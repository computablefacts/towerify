<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\YnhTrial;
use App\Modules\AdversaryMeter\Enums\HoneypotCloudProvidersEnum;
use App\Modules\AdversaryMeter\Enums\HoneypotCloudSensorsEnum;
use App\Modules\AdversaryMeter\Enums\HoneypotStatusesEnum;
use App\Modules\AdversaryMeter\Http\Controllers\AssetController;
use App\Modules\AdversaryMeter\Mail\HoneypotRequested;
use App\Modules\AdversaryMeter\Models\Honeypot;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Konekt\User\Models\InvitationProxy;

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
            return redirect()->route('public.cywise.onboarding', ['hash' => $hash, 'step' => 1]);
        }
        if ($step === 4 && $request->get('action') == 'back') {
            return redirect()->route('public.cywise.onboarding', ['hash' => $hash, 'step' => 2]);
        }
        if ($step === 5 && $request->get('action') == 'back') {
            return redirect()->route('public.cywise.onboarding', ['hash' => $hash, 'step' => 3]);
        }

        // Load trial (if any)
        /** @var YnhTrial $trial */
        $trial = YnhTrial::updateOrCreate(['hash' => $hash], ['hash' => $hash]);

        // Deal with parameters validation and state management
        if ($step === 2 && $request->get('action') == 'next') {
            $request->validate(['domain' => 'required|string|min:1|max:100']);
            $trial->domain = $request->string('domain');
        }
        if ($step === 3 && $request->get('action') == 'next') {
            $request->validate(['certificate' => 'required|string']);
            $states = array_filter($request->all(), fn($key) => preg_match('/^d1-.*$/', $key), ARRAY_FILTER_USE_KEY);
            $domains = array_filter($request->all(), fn($key) => preg_match('/^d2-.*$/', $key), ARRAY_FILTER_USE_KEY);
            $trial->subdomains = array_map(fn($id) => $domains[Str::replace('d1-', 'd2-', $id)], array_keys($states));
        }
        if ($step === 4 && $request->get('action') == 'next') {
            // FALL THROUGH
        }
        if ($step === 5 /* && $request->get('action') == 'next' */) {

            if ($request->get('action') == 'next') {
                $request->validate(['email' => 'required|string|email']);
                $trial->email = $request->string('email');
            }

            // Create shadow profile
            /** @var User $user */
            $user = User::where('email', $trial->email)->first();
            if (!$user) {
                /** @var Invitation $invitation */
                $invitation = Invitation::where('email', $trial->email)->first();
                if (!$invitation) {
                    $invitation = InvitationProxy::createInvitation($trial->email, "J. Doe");
                }
                $user = $invitation->createUser(['password' => Str::random(64)]);
            }

            Auth::login($user);

            if (!$trial->honeypots) {

                // Generate honeypots names
                $http = Str::random(10);
                $https = Str::random(10);
                $ssh = Str::random(10);

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
                Mail::to(config('towerify.freshdesk.to_email'))->send(new HoneypotRequested($user, $subject, $body));

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
                Mail::to(config('towerify.freshdesk.to_email'))->send(new HoneypotRequested($user, $subject, $body));

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
                Mail::to(config('towerify.freshdesk.to_email'))->send(new HoneypotRequested($user, $subject, $body));

                $trial->honeypots = true;
            }

            // Register assets and start scans
            $assets = [];

            foreach ($trial->subdomains as $subdomain) {

                $request = new Request();
                $request->replace(['asset' => $subdomain, 'watch' => true]);

                $controller = new AssetController();
                $controller->saveAsset($request);

                $assets[] = $controller->infosFromAsset(base64_encode($subdomain));
            }

            usort($assets, fn($a, $b) => strcmp($a['asset'], $b['asset']));

            // Logout!
            Auth::logout();
        }

        $trial->save();

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
}
