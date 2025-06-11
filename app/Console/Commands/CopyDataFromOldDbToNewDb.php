<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CopyDataFromOldDbToNewDb extends Command
{
    protected $signature = 'db:from-old-to-new';
    protected $description = 'Copy data from the old database to the new one';

    public function handle()
    {
        DB::transaction(function () {

            Schema::disableForeignKeyConstraints();

            try {

                \DB::connection('mysql_legacy')
                    ->table('tenants')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('tenants', $items));

                \DB::connection('mysql_legacy')
                    ->table('users')
                    ->chunkById(100, function ($items) {
                        /** @var object $item */
                        foreach ($items as $item) {
                            $item->username = $item->name;
                            $item->password = Hash::make(cywise_unhash($item->password));
                            $this->upsert('users', $item);
                            // TODO : deal with stripe_id
                        }
                    });

                \DB::connection('mysql_legacy')
                    ->table('roles')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('roles', $items));

                \DB::connection('mysql_legacy')
                    ->table('permissions')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('permissions', $items));

                \DB::connection('mysql_legacy')
                    ->table('model_roles')
                    ->get()
                    ->each(function (object $item) {
                        $item->model_type = 'App\Models\User';
                        $objKeys = array_keys((array)$item);
                        $tblKeys = Schema::getColumnListing('model_has_roles');
                        $keys = array_intersect($objKeys, $tblKeys);
                        $newItem = array_intersect_key((array)$item, array_flip($tblKeys));
                        \DB::table('model_has_roles')->upsert($newItem, $tblKeys, $keys);
                    });

                \DB::connection('mysql_legacy')
                    ->table('model_permissions')
                    ->get()
                    ->each(function (object $item) {
                        $item->model_type = 'App\Models\User';
                        $objKeys = array_keys((array)$item);
                        $tblKeys = Schema::getColumnListing('model_has_roles');
                        $keys = array_intersect($objKeys, $tblKeys);
                        $newItem = array_intersect_key((array)$item, array_flip($tblKeys));
                        \DB::table('model_has_permissions')->upsert($newItem, $tblKeys, $keys);
                    });

                \DB::connection('mysql_legacy')
                    ->table('role_permissions')
                    ->get()
                    ->each(function (object $item) {
                        $item->model_type = 'App\Models\User';
                        $objKeys = array_keys((array)$item);
                        $tblKeys = Schema::getColumnListing('role_has_permissions');
                        $keys = array_intersect($objKeys, $tblKeys);
                        $newItem = array_intersect_key((array)$item, array_flip($tblKeys));
                        \DB::table('role_has_permissions')->upsert($newItem, $tblKeys, $keys);
                    });

                \DB::connection('mysql_legacy')
                    ->table('am_alerts')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_alerts', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_assets')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_assets', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_assets_tags')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_assets_tags', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_assets_tags_hashes')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_assets_tags_hashes', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_attackers')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_attackers', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_hidden_alerts')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_hidden_alerts', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_honeypots')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_honeypots', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_honeypots_events')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_honeypots_events', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_ports')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_ports', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_ports_tags')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_ports_tags', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_scans')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_scans', $items));

                \DB::connection('mysql_legacy')
                    ->table('am_screenshots')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('am_screenshots', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_chunks')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_chunks', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_chunks_tags')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_chunks_tags', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_collections')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_collections', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_conversations')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_conversations', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_files')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_files', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_prompts')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_prompts', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_scheduled_tasks')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_scheduled_tasks', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_tables')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_tables', $items));

                \DB::connection('mysql_legacy')
                    ->table('cb_templates')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('cb_templates', $items));

                \DB::connection('mysql_legacy')
                    ->table('t_items_items')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('t_items_items', $items));

                \DB::connection('mysql_legacy')
                    ->table('health_check_result_history_items')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('health_check_result_history_items', $items));

                \DB::connection('mysql_legacy')
                    ->table('personal_access_tokens')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('personal_access_tokens', $items));

                \DB::connection('mysql_legacy')
                    ->table('saml2_tenants')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('saml2_tenants', $items));

                \DB::connection('mysql_legacy')
                    ->table('t_facts')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('t_facts', $items));

                \DB::connection('mysql_legacy')
                    ->table('t_facts_items')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('t_facts_items', $items));

                \DB::connection('mysql_legacy')
                    ->table('t_items')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('t_items', $items));

                \DB::connection('mysql_legacy')
                    ->table('tcb_stories')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('tcb_stories', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_applications')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_applications', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_backups')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_backups', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_cves')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_cves', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_domains')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_domains', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_frameworks')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_frameworks', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_mitre_attck')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_mitre_attck', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_nginx_logs')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_nginx_logs', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_osquery')
                    ->whereIn('id', fn($query) => $query->select('ynh_osquery_id')->from('ynh_osquery_latest_events'))
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_osquery', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_osquery_events_counts')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_osquery_events_counts', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_osquery_latest_events')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_osquery_latest_events', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_osquery_packages')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_osquery_packages', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_osquery_rules')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_osquery_rules', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_ossec_checks')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_ossec_checks', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_ossec_policies')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_ossec_policies', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_overview')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_overview', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_permissions')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_permissions', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_servers')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_servers', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_ssh_traces')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_ssh_traces', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_trials')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_trials', $items));

                \DB::connection('mysql_legacy')
                    ->table('ynh_users')
                    ->chunkById(100, fn(Collection $items) => $this->upsertAll('ynh_users', $items));

            } catch (\Exception $e) {
                Log::error($e->getMessage());
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });
        return 0;
    }

    private function upsertAll(string $table, Collection $items)
    {
        /** @var object $item */
        foreach ($items as $item) {
            $this->upsert($table, $item);
        }
    }

    private function upsert(string $table, object $item)
    {
        $objKeys = array_keys((array)$item);
        $tblKeys = Schema::getColumnListing($table);
        $keys = array_intersect($objKeys, $tblKeys);
        $newItem = array_intersect_key((array)$item, array_flip($keys));
        \DB::table($table)->upsert($newItem, ['id'], $keys);
    }
}