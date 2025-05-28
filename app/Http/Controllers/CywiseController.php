<?php

namespace App\Http\Controllers;

use App\Enums\HoneypotCloudProvidersEnum;
use App\Enums\HoneypotCloudSensorsEnum;
use App\Enums\HoneypotStatusesEnum;
use App\Mail\HoneypotRequested;
use App\Models\Honeypot;
use App\Models\YnhTrial;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CywiseController extends Controller
{
    public function __construct()
    {
        //
    }

    public function onboarding(string $hash, int $step, Request $request)
    {
        $validator = Validator::make([
            'hash' => $hash,
            'step' => $step,
        ], [
            'hash' => 'required|string|min:128|max:128',
            'step' => 'required|integer|min:1|max:5',
        ]);
        $validator->validate();
        $request->validate(['action' => 'string|min:4|max:4']);

        // Deal with the "back" buttons
        if ($step === 3 && $request->get('action') == 'back') {
            return redirect()->route('cyber-check.cywise.onboarding', ['hash' => $hash, 'step' => 1]);
        }
        if ($step === 4 && $request->get('action') == 'back') {
            return redirect()->route('cyber-check.cywise.onboarding', ['hash' => $hash, 'step' => 2]);
        }
        if ($step === 5 && $request->get('action') == 'back') {
            return redirect()->route('cyber-check.cywise.onboarding', ['hash' => $hash, 'step' => 3]);
        }

        // Load trial (if any)
        /** @var YnhTrial $trial */
        $trial = YnhTrial::updateOrCreate(['hash' => $hash], ['hash' => $hash]);

        // Deal with parameters validation and state management
        if ($step === 2 && $request->get('action') == 'next') {
            $request->validate(['domain' => 'required|string|min:1|max:100']);
            $trial->domain = Str::betweenFirst($request->string('domain'), '://', '/');
            $trial->save();
        }
        if ($step === 3 && $request->get('action') == 'next') {
            $request->validate(['terms' => 'required|string']);
            $trial->subdomains = array_values(array_filter($request->all(), fn(string $key) => preg_match('/^d-\d+$/', $key), ARRAY_FILTER_USE_KEY));
            $trial->save();
        }
        if ($step === 4 && $request->get('action') == 'next') {
            // FALL THROUGH
        }
        if ($step === 5 /* && $request->get('action') == 'next' */) {

            if ($request->get('action') == 'next') {
                $request->validate(['email' => 'required|string|email']);
                $trial->email = $request->string('email');
                $trial->save();
            }

            // Create shadow profile
            $user = User::getOrCreate($trial->email);

            if (!$trial->created_by) {
                $trial->created_by = $user->id;
                $trial->save();
            }

            Auth::login($user);

            if (!$trial->honeypots) {

                // Generate honeypots names
                $http = Str::lower(Str::random(10));
                $https = Str::lower(Str::random(10));
                $ssh = Str::lower(Str::random(10));

                // HTTP
                /** @var Honeypot $honeypot */
                $honeypot = Honeypot::create([
                    'dns' => "{$http}.cywise.io",
                    'status' => HoneypotStatusesEnum::DNS_SETUP,
                    'cloud_provider' => HoneypotCloudProvidersEnum::AWS,
                    'cloud_sensor' => HoneypotCloudSensorsEnum::HTTP,
                ]);
                $subject = "Setup of honeypot {$honeypot->dns} requested";
                $body = [
                    'id' => $honeypot->id,
                    'sensor' => $honeypot->cloud_sensor,
                    'provider' => $honeypot->cloud_provider,
                    'query' => "UPDATE am_honeypots SET status = 'setup_complete' WHERE id = {$honeypot->id};",
                ];
                Mail::to(config('towerify.freshdesk.to_email'))->send(new HoneypotRequested(config('towerify.freshdesk.from_email'), 'Support', $subject, $body));

                // HTTPS
                /** @var Honeypot $honeypot */
                $honeypot = Honeypot::create([
                    'dns' => "{$https}.cywise.io",
                    'status' => HoneypotStatusesEnum::DNS_SETUP,
                    'cloud_provider' => HoneypotCloudProvidersEnum::AWS,
                    'cloud_sensor' => HoneypotCloudSensorsEnum::HTTPS,
                ]);
                $subject = "Setup of honeypot {$honeypot->dns} requested";
                $body = [
                    'id' => $honeypot->id,
                    'sensor' => $honeypot->cloud_sensor,
                    'provider' => $honeypot->cloud_provider,
                    'query' => "UPDATE am_honeypots SET status = 'setup_complete' WHERE id = {$honeypot->id};",
                ];
                Mail::to(config('towerify.freshdesk.to_email'))->send(new HoneypotRequested(config('towerify.freshdesk.from_email'), 'Support', $subject, $body));

                // SSH
                /** @var Honeypot $honeypot */
                $honeypot = Honeypot::create([
                    'dns' => "{$ssh}.cywise.io",
                    'status' => HoneypotStatusesEnum::DNS_SETUP,
                    'cloud_provider' => HoneypotCloudProvidersEnum::AWS,
                    'cloud_sensor' => HoneypotCloudSensorsEnum::SSH,
                ]);
                $subject = "Setup of honeypot {$honeypot->dns} requested";
                $body = [
                    'id' => $honeypot->id,
                    'sensor' => $honeypot->cloud_sensor,
                    'provider' => $honeypot->cloud_provider,
                    'query' => "UPDATE am_honeypots SET status = 'setup_complete' WHERE id = {$honeypot->id};",
                ];
                Mail::to(config('towerify.freshdesk.to_email'))->send(new HoneypotRequested(config('towerify.freshdesk.from_email'), 'Support', $subject, $body));

                $trial->honeypots = true;
                $trial->save();
            }

            // Register assets and start scans
            $assets = [];

            foreach ($trial->subdomains as $subdomain) {

                $request = new Request([
                    'asset' => $subdomain,
                    'watch' => true,
                    'trial_id' => $trial->id,
                ]);
                $request->setUserResolver(fn() => auth()->user());
                $controller = new AssetController();
                $controller->saveAsset($request);

                $assets[] = $controller->infosFromAsset(base64_encode($subdomain), $trial->id);
            }

            usort($assets, fn($a, $b) => strcmp($a['asset'], $b['asset']));

            // Logout!
            Auth::logout();
        }
        return view('cywise.cywise', [
            'hash' => $hash,
            'step' => $step,
            'trial' => $trial,
            'assets' => $assets ?? [],
        ]);
    }

    public function discovery(string $hash, Request $request)
    {
        $validator = Validator::make([
            'hash' => $hash,
        ], [
            'hash' => 'required|string|min:128|max:128',
        ]);
        $validator->validate();

        // Load trial (if any)
        /** @var YnhTrial $trial */
        $trial = YnhTrial::where('hash', $hash)->firstOrFail();
        $request->replace(['domain' => $trial->domain]);

        $controller = new AssetController();
        return $controller->discover($request)['subdomains'];
    }

    public function onboarding2(Request $request)
    {
        return view('cywise.cywise2', []);
    }
}
