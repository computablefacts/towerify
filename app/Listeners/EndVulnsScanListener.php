<?php

namespace App\Listeners;

use App\Events\EndVulnsScan;
use App\Helpers\DeepSeek;
use App\Helpers\VulnerabilityScannerApiUtilsFacade as ApiUtils;
use App\Http\Controllers\AssetController;
use App\Models\Alert;
use App\Models\Asset;
use App\Models\Port;
use App\Models\Scan;
use App\Models\YnhTrial;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EndVulnsScanListener extends AbstractListener
{
    public function viaQueue(): string
    {
        return self::MEDIUM;
    }

    protected function handle2($event)
    {
        if (!($event instanceof EndVulnsScan)) {
            throw new \Exception('Invalid event type!');
        }
        $this->handle3($event);
        $this->sendEmailReport($event->scan());
    }

    private function handle3($event): void
    {
        $scan = $event->scan();
        $dropEvent = $event->drop();
        $taskResult = $event->taskResult;

        if (!$scan) {
            Log::warning("Vulns scan has been removed : {$event->scanId}");
            return;
        }
        if ($scan->vulnsScanHasEnded()) {
            Log::warning("Vulns scan has ended : {$event->scanId}");
            return;
        }
        if (count($taskResult) > 0) {
            $task = $taskResult;
        } else {
            if ($dropEvent) {
                Log::error("Vulns scan event is too old : {$event->scanId}");
                $scan->markAsFailed();
                return;
            }
            if (!$scan->vulnsScanIsRunning()) {
                Log::warning("Vulns scan is not running anymore : {$event->scanId}");
                $scan->markAsFailed();
                return;
            }

            $taskId = $scan->vulns_scan_id;
            $task = $this->taskOutput($taskId);
        }

        $currentTaskName = $task['current_task'] ?? null;
        $currentTaskStatus = $task['current_task_status'] ?? null;

        if ($currentTaskName !== 'alerter' || $currentTaskStatus !== 'DONE') {

            $isCsc = isset($task['tags']) && collect($task['tags'])->filter(fn(string $tag) => Str::startsWith(Str::lower($tag), 'csc_'))->isNotEmpty();

            if (!$isCsc) {

                if ($currentTaskStatus && Str::startsWith($currentTaskStatus, 'DONE_')) {
                    $event->sink();
                    return;
                }

                $service = $task['service'] ?? null;

                if ($service !== 'closed') {
                    $event->sink();
                } else { // End the scan!

                    $port = $scan->port()->first();
                    $port->closed = 1;
                    $port->save();

                    $this->markScanAsCompleted($scan);
                }
                return;
            }
        }

        $service = $task['service'] ?? null;
        $product = $task['product'] ?? null;
        $ssl = $task['ssl'] ?? null;

        $port = $scan->port()->first();
        $port->service = $service;
        $port->product = $product;
        $port->ssl = $ssl ? 1 : 0;
        $port->save();

        $tags = collect($task['tags'] ?? []);
        $tags->each(function (string $label) use ($port) {
            $port->tags()->create(['tag' => Str::lower($label)]);
        });

        $this->setAlertsV1($port, $task);
        $this->setAlertsV2($port, $task);
        $this->setScreenshot($port, $task);
        $this->markScanAsCompleted($scan);
    }

    private function setAlertsV1(Port $port, array $task): void
    {
        collect($task['data'] ?? [])
            ->filter(fn(array $data) => isset($data['tool']) && $data['tool'] === 'alerter' && isset($data['rawOutput']) && $data['rawOutput'])
            ->flatMap(fn(array $data) => collect(preg_split('/\r\n|\r|\n/', $data['rawOutput'])))
            ->filter(fn(string $alert) => $alert !== '')
            ->map(fn(string $alert) => json_decode($alert, true))
            ->filter(fn(?array $alert) => $alert !== null)
            ->each(function (array $alert) use ($port) {
                try {
                    Alert::updateOrCreate([
                        'port_id' => $port->id,
                        'uid' => trim($alert['values'][7])
                    ], [
                        'port_id' => $port->id,
                        'type' => trim($alert['type']),
                        'vulnerability' => trim($alert['values'][4]),
                        'remediation' => trim($alert['values'][5]),
                        'level' => trim($alert['values'][6]),
                        'uid' => trim($alert['values'][7]),
                        'cve_id' => empty($alert['values'][8]) ? null : $alert['values'][8],
                        'cve_cvss' => empty($alert['values'][9]) ? null : $alert['values'][9],
                        'cve_vendor' => empty($alert['values'][10]) ? null : $alert['values'][10],
                        'cve_product' => empty($alert['values'][11]) ? null : $alert['values'][11],
                        'title' => trim($alert['values'][12]),
                        'flarum_slug' => null, // TODO : remove?
                    ]);
                } catch (\Exception $exception) {
                    Log::error($exception);
                    Log::error($alert);
                }
            });
    }

    private function setAlertsV2(Port $port, array $task): void
    {
        collect($task['data'] ?? [])
            ->filter(fn(array $data) => isset($data['alerts']) && count($data['alerts']))
            ->flatMap(fn(array $data) => $data['alerts'])
            ->filter(fn(array|string $alert) => is_array($alert))
            ->each(function (array $alert) use ($port) {
                try {
                    $type = trim($alert['type']);

                    if (!str_ends_with($type, '_alert')) {
                        $type .= '_v3_alert';
                    }

                    Alert::updateOrCreate([
                        'port_id' => $port->id,
                        'uid' => trim($alert['uid'])
                    ], [
                        'port_id' => $port->id,
                        'type' => $type,
                        'vulnerability' => Str::limit(trim($alert['vulnerability']), 5000),
                        'remediation' => Str::limit(trim($alert['remediation']), 5000),
                        'level' => trim($alert['level']),
                        'uid' => trim($alert['uid']),
                        'cve_id' => empty($alert['cve_id']) ? null : $alert['cve_id'],
                        'cve_cvss' => empty($alert['cve_cvss']) ? null : $alert['cve_cvss'],
                        'cve_vendor' => empty($alert['cve_vendor']) ? null : $alert['cve_vendor'],
                        'cve_product' => empty($alert['cve_product']) ? null : $alert['cve_product'],
                        'title' => trim($alert['title']),
                        'flarum_slug' => null, // TODO : remove?
                    ]);
                } catch (\Exception $exception) {
                    Log::error($exception);
                    Log::error($alert);
                }
            });
    }

    private function setScreenshot(Port $port, array $task)
    {
        collect($task['data'] ?? [])
            ->filter(fn(array $data) => isset($data['tool']) && $data['tool'] === 'splash' && isset($data['rawOutput']) && $data['rawOutput'])
            ->map(fn(array $data) => json_decode($data['rawOutput'], true))
            ->filter(fn(array $screenshot) => !empty($screenshot['png']))
            ->each(function (array $screenshot) use ($port) {
                try {
                    $port->screenshot()->create([
                        'port_id' => $port->id,
                        'png' => "data:image/png;base64,{$screenshot['png']}",
                    ]);
                } catch (\Exception $exception) {
                    Log::error($exception);
                    Log::error($port);
                }
            });
    }

    private function markScanAsCompleted(Scan $scan): void
    {
        DB::transaction(function () use ($scan) {

            $scan->vulns_scan_ends_at = Carbon::now();
            $scan->save();

            $remaining = Scan::where('asset_id', $scan->asset_id)
                ->where('ports_scan_id', $scan->ports_scan_id)
                ->whereNull('vulns_scan_ends_at')
                ->count();

            if ($remaining === 0) {

                /** @var Asset $asset */
                $asset = $scan->asset()->first();

                if ($asset) {
                    if ($asset->cur_scan_id === $scan->ports_scan_id) {
                        return; // late arrival, ex. when events are processed synchronously
                    }
                    if ($asset->prev_scan_id) {
                        Scan::where('asset_id', $scan->asset_id)
                            ->where('id', $asset->prev_scan_id)
                            ->delete();
                    }

                    $asset->prev_scan_id = $asset->cur_scan_id;
                    $asset->cur_scan_id = $asset->next_scan_id;
                    $asset->next_scan_id = null;
                    $asset->save();
                }
            }
        });
    }

    private function taskOutput(string $taskId): array
    {
        return ApiUtils::task_get_scan_public($taskId);
    }

    private function sendEmailReport(?Scan $scan): void
    {
        if (!$scan) {
            return;
        }

        /** @var Asset $asset */
        $asset = $scan->asset()->firstOrFail();
        /** @var YnhTrial $trial */
        $trial = $asset->trial()->first();

        if (!$trial) {
            return;
        }
        if ($trial->completed) {
            Log::warning("Trial {$trial->id} is already completed");
            return;
        }

        $user = $trial->createdBy();

        if (!$user->is_active) {
            Log::warning("User {$user->email} is inactive");
            return;
        }

        $assets = $trial->assets()->get();
        $scansInProgress = $assets->contains(fn(Asset $asset) => $asset->scanInProgress()->isNotEmpty());

        if ($scansInProgress) {
            Log::warning("Assets are still being scanned for trial {$trial->id}");
            return;
        }

        $onboarding = route('public.cywise.onboarding', ['hash' => $trial->hash, 'step' => 5]);
        $alerts = $assets->flatMap(fn(Asset $asset) => $asset->alerts()->get())->filter(fn(Alert $alert) => $alert->is_hidden === 0);
        $alertsHigh = $alerts->filter(fn(Alert $alert) => $alert->level === 'High');
        $alertsMedium = $alerts->filter(fn(Alert $alert) => $alert->level === 'Medium');
        $alertsLow = $alerts->filter(fn(Alert $alert) => $alert->level === 'Low');
        $nbServers = $alerts->map(fn(Alert $alert) => $alert->port()->ip)->unique()->count();
        $to = $user->email;
        $msgHigh = $alertsHigh->count() > 0 ? "<li><b>{$alertsHigh->count()}</b> sont des vulnérabilités critiques et <b>doivent</b> être corrigées.</li>" : "";
        $msgMedium = $alertsMedium->count() > 0 ? "<li><b>{$alertsMedium->count()}</b> sont des vulnérabilités de criticité moyenne et <b>devraient</b> être corrigées.</li>" : "";
        $msgLow = $alertsLow->count() > 0 ? "<li><b>{$alertsLow->count()}</b> sont des vulnérabilités de criticité basse et ne posent pas un risque de sécurité immédiat.</li>" : "";
        /* $promptHigh = $alertsHigh->map(function (Alert $alert) {
            $cve = $alert->cve_id ? "\n- Identifiant de la CVE. <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a>" : '';
            return "
                ## {$alert->title} (criticité haute)
                - Actif concerné. {$alert->asset()?->asset}
                - Serveur concerné. {$alert->port()?->ip} ({$alert->port()?->port}) {$cve}
                - Service concerné. {$alert->port()?->service}
                - Produit concerné. {$alert->port()?->product}
                - Description détaillée de l'alerte. {$alert->vulnerability}
                - Recette de remédiation. {$alert->remediation}
            ";
        })->join("\n");
        $promptMedium = $alertsMedium->map(function (Alert $alert) {
            $cve = $alert->cve_id ? "\n- Identifiant de la CVE. <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a>" : '';
            return "
                ## {$alert->title} (criticité moyenne)
                - Actif concerné. {$alert->asset()?->asset}
                - Serveur concerné. {$alert->port()?->ip} ({$alert->port()?->port}) {$cve}
                - Service concerné. {$alert->port()?->service}
                - Produit concerné. {$alert->port()?->product}
                - Description détaillée de l'alerte. {$alert->vulnerability}
                - Recette de remédiation. {$alert->remediation}
            ";
        })->join("\n");
        $promptLow = $alertsLow->map(function (Alert $alert) {
            $cve = $alert->cve_id ? "\n- Identifiant de la CVE. <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a>" : '';
            return "
                ## {$alert->title} (criticité basse)
                - Actif concerné. {$alert->asset()?->asset}
                - Serveur concerné. {$alert->port()?->ip} ({$alert->port()?->port}) {$cve}
                - Service concerné. {$alert->port()?->service}
                - Produit concerné. {$alert->port()?->product}
                - Description détaillée de l'alerte. {$alert->vulnerability}
                - Recette de remédiation. {$alert->remediation}
            ";
        })->join("\n");

        $response = DeepSeek::execute("
            # Alertes
            
            {$promptHigh}
            {$promptMedium}
            {$promptLow}
            
            # Contexte
            
            Tu es CyberBuddy, un assistant virtuel expert des questions de Cybersécurité. Tu es capable de répondre 
            de manière accessible et concise à des problématiques posées par des utilisateurs.
            
            # Instructions
            
            En te basant sur les alertes ci-dessus ordonne les vulnérabilités de la plus critique à la moins critique et 
            propose-moi pour chaque vulnérabilité un plan de remédiation me permettant de corriger celle-ci. Dans la 
            rédaction de ta réponse essaie d'être le plus concis et clair possible. Identifie clairement les actifs,
            serveurs et ports concernés.

            Ton plan de remédiation doit être rédigé au format HTML. Tu n'utiliseras pas de feuilles de style externes et 
            tu ne mettras pas d'attributs 'style' et 'class' aux balises HTML utilisées. Tu utiliseras uniquement les balises 
            HTML <h2>, <h3>, <ul>, <li> et <p>. Tu écriras uniquement du HTML dans ta réponse. Tu n'utiliseras pas de markdown 
            ni ne fera commencer ta réponse par '```html' ou terminer celle-ci par '```'.
        ");
        $answer = $response['choices'][0]['message']['content'] ?? '';
        $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer); */

        $resultsHigh = $alertsHigh->map(function (Alert $alert) {
            $cve = $alert->cve_id ? "<li><b>Identifiant de la CVE.</b> <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a></li>" : '';
            return "
                <h3>{$alert->title} (criticité haute)</h3>
                <ul>
                    <li><b>Actif concerné.</b> {$alert->asset()?->asset}</li>
                    <li><b>Serveur concerné.</b> {$alert->port()?->ip} ({$alert->port()?->port})</li> {$cve}
                    <li><b>Service concerné.</b> {$alert->port()?->service}</li>
                    <li><b>Produit concerné.</b> {$alert->port()?->product}</li>
                    <li><b>Description détaillée de l'alerte.</b> {$alert->vulnerability}</li>
                    <li><b>Proposition de remédiation.</b> {$alert->remediation}</li>
                </ul>
            ";
        })->join("</li><li>");

        $resultsMedium = $alertsMedium->map(function (Alert $alert) {
            $cve = $alert->cve_id ? "<li><b>Identifiant de la CVE.</b> <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a></li>" : '';
            return "
                <h3>{$alert->title} (criticité moyenne)</h3>
                <ul>
                    <li><b>Actif concerné.</b> {$alert->asset()?->asset}</li>
                    <li><b>Serveur concerné.</b> {$alert->port()?->ip} ({$alert->port()?->port})</li> {$cve}
                    <li><b>Service concerné.</b> {$alert->port()?->service}</li>
                    <li><b>Produit concerné.</b> {$alert->port()?->product}</li>
                    <li><b>Description détaillée de l'alerte.</b> {$alert->vulnerability}</li>
                    <li><b>Proposition de remédiation.</b> {$alert->remediation}</li>
                </ul>
            ";
        })->join("</li><li>");

        $resultsLow = $alertsLow->map(function (Alert $alert) {
            $cve = $alert->cve_id ? "<li><b>Identifiant de la CVE.</b> <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a></li>" : '';
            return "
                <h3>{$alert->title} (criticité basse)</h3>
                <ul>
                    <li><b>Actif concerné.</b> {$alert->asset()?->asset}</li>
                    <li><b>Serveur concerné.</b> {$alert->port()?->ip} ({$alert->port()?->port})</li> {$cve}
                    <li><b>Service concerné.</b> {$alert->port()?->service}</li>
                    <li><b>Produit concerné.</b> {$alert->port()?->product}</li>
                    <li><b>Description détaillée de l'alerte.</b> {$alert->vulnerability}</li>
                    <li><b>Proposition de remédiation.</b> {$alert->remediation}</li>
                </ul>
            ";
        })->join("</li><li>");

        $response = DeepSeek::execute("
            Traduis les textes de la page HTML ci-dessous en français.
            Conserve tous les tags HTML. 
            Ta réponse sera composée uniquement du HTML ci-dessous traduis en français.
            Tu n'utiliseras pas de markdown ni ne fera commencer ta réponse par '```html' ou terminer celle-ci par '```'.

            {$resultsHigh}
            {$resultsMedium}
            {$resultsLow}
        ");
        $answer = $response['choices'][0]['message']['content'] ?? '';
        $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);

        $subject = "Cywise - Résultats de ton audit de sécurité";

        $beforeCta = "
            <p>Je tiens tout d'abord à te remercier d'avoir testé Cywise. Ta participation est essentielle pour m'aider à améliorer la sécurité de tes systèmes et à protéger tes données sensibles.</p>
            <p>L'idée forte derrière Cywise est de t'aider à améliorer ta sécurité en quelques minutes par semaine.</p>
            <p>Voici un résumé des résultats du test :</p>
            <ul>
              <li>J'ai analysé <b>{$assets->count()}</b> domaines.</li>
              <li>J'ai évalué <b>{$nbServers}</b> serveurs et découvert <b>{$alerts->count()}</b> vulnérabilités.</li>
              {$msgHigh}
              {$msgMedium}
              {$msgLow}
            </ul>
            <p>Je te propose d'effectuer les correctifs suivants :</p>
            {$answer}
            <p>Si tu souhaites retourner à la liste de tes domaines, cliques <a href='{$onboarding}' target='_blank'>ici</a>.</p>
            <p>Enfin, je reste à ta disposition pour toute question ou assistance supplémentaire. Merci encore pour ta confiance en Cywise !</p>
            <p>Bien à toi,</p>
            <p>CyberBuddy</p>
        ";

        $this->sendEmail($to, $subject, "Bienvenu !", $beforeCta);

        $controller = new AssetController();
        $assets->each(fn(Asset $asset) => $controller->assetMonitoringEnds($asset));

        $trial->completed = true;
        $trial->save();
    }

    private function sendEmail(string $to, string $subject, string $title, string $beforeCta, string $ctaLink = "", string $ctaName = "", string $afterCta = ""): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('towerify.sendgrid.api_key'),
            'Accept' => 'application/json',
        ])->post(config('towerify.sendgrid.api'), [
            "personalizations" => [[
                "to" => [[
                    "email" => $to,
                ]],
                "dynamic_template_data" => [
                    "sender" => [
                        "name" => "ComputableFacts",
                        "address" => "178 boulevard Haussmann",
                        "city" => "Paris",
                        "country" => "France",
                        "postcode" => "75008",
                    ],
                    "email" => [
                        "subject" => $subject,
                        "title" => $title,
                        "text_before_cta" => $beforeCta,
                        "text_after_cta" => $afterCta,
                        "cta_link" => $ctaLink,
                        "cta_name" => $ctaName,
                    ]
                ]
            ]],
            "from" => [
                "email" => config('towerify.admin.email'),
            ],
            "template_id" => "d-a7f35a5a052e4ac4b127d6f12034331d"
        ]);
        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json;
        }
        Log::error($response->body());
        return [];
    }
}
