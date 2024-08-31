<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Jobs\TriggerScan;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTag;
use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\PortTag;
use App\Modules\AdversaryMeter\Models\Scan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScansTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan("migrate --path=database/migrations/am --database=mysql_am");
    }

    public function testItUpdatesTld()
    {
        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);

        $asset = Asset::find($asset->id); // reload all fields from db

        $this->assertNull($asset->tld);
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);
        $this->assertNull($asset->discovery_id);

        $tld = $asset->tld();

        $this->assertEquals($tld, 'example.com');
        $this->assertEquals($asset->tld, 'example.com');

        $asset = Asset::find($asset->id); // reload from db

        $this->assertEquals($asset->tld, 'example.com'); // ensure TLD has been persisted

        $asset->delete(); // cleanup
    }

    public function testItDoesNotUpdateTld()
    {
        $asset = Asset::firstOrCreate([
            'asset' => '93.184.215.14',
            'asset_type' => AssetTypesEnum::IP,
        ]);

        $asset = Asset::find($asset->id); // reload all fields from db

        $this->assertNull($asset->tld);
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);
        $this->assertNull($asset->discovery_id);

        $tld = $asset->tld();

        $this->assertNull($tld);
        $this->assertNull($asset->tld);

        $asset = Asset::find($asset->id); // reload from db

        $this->assertNull($asset->tld); // ensure TLD has been persisted

        $asset->delete(); // cleanup
    }

    public function testItTriggersAnAssetScan()
    {
        ApiUtils::shouldReceive('task_nmap_public')
            ->once()
            ->with('www.example.com')
            ->andReturn([
                'task_id' => '6409ae68ed42e11e31e5f19d',
            ]);
        ApiUtils::shouldReceive('task_status_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_status' => 'SUCCESS',
            ]);
        ApiUtils::shouldReceive('task_result_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_result' => [
                    [
                        'hostname' => 'www.example.com',
                        'ip' => '93.184.215.14',
                        'port' => 443,
                        'protocol' => 'tcp',
                    ], [
                        'hostname' => 'www.example.com',
                        'ip' => '93.184.215.14',
                        'port' => 80,
                        'protocol' => 'tcp',
                    ]
                ],
            ]);
        ApiUtils::shouldReceive('ip_geoloc_public')
            ->twice()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'country' => [
                        'iso_code' => 'US',
                    ],
                ],
            ]);
        ApiUtils::shouldReceive('ip_whois_public')
            ->twice()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'asn_description' => null,
                    'asn_registry' => null,
                    'asn' => null,
                    'asn_cidr' => null,
                    'asn_country_code' => null,
                    'asn_date' => null,
                ],
            ]);
        ApiUtils::shouldReceive('task_start_scan_public')
            ->once()
            ->with('www.example.com', '93.184.215.14', 443, 'tcp', ['demo'])
            ->andReturn([
                'scan_id' => 'a9a5d877-abed-4a39-8b4a-8316d451730d',
            ]);
        ApiUtils::shouldReceive('task_start_scan_public')
            ->once()
            ->with('www.example.com', '93.184.215.14', 80, 'tcp', ['demo'])
            ->andReturn([
                'scan_id' => 'b9b5e877-bdfe-4b39-8c4b-8316e451730e',
            ]);
        ApiUtils::shouldReceive('task_get_scan_public')
            ->once()
            ->with('a9a5d877-abed-4a39-8b4a-8316d451730d')
            ->andReturn([
                'hostname' => 'www.example.com',
                'ip' => '93.184.215.14',
                'port' => 443,
                'protocol' => 'tcp',
                'service' => 'http',
                'product' => 'Cloudflare http proxy',
                'ssl' => true,
                'current_task' => 'alerter',
                'current_task_status' => 'DONE',
                'tags' => ['Http', 'Cloudflare'],
                'data' => [
                    [ // Alert V1
                        "tool" => "alerter",
                        "rawOutput" => json_encode([
                            "type" => "wordpress_vuln_v2_alert",
                            "values" => [
                                "www.example.com",
                                '93.184.215.14',
                                443,
                                "tcp",
                                "WP <= 6.1.1 - Unauthenticated Blind SSRF via DNS Rebinding",
                                "Update Wordpress if possible.\nGet more information about the vulnerability at https://wpscan.com/vulnerability/c8814e6e-78b3-4f63-a1d3-6906a84c1f11\nMore references:\nhttps://blog.sonarsource.com/wordpress-core-unauthenticated-blind-ssrf/",
                                "Low",
                                "3ee22091822f95e8b2095c228f397a63",
                                "",
                                "",
                                "",
                                "",
                                "Wordpress core issue"
                            ],
                        ]),
                    ]
                ],
            ]);
        ApiUtils::shouldReceive('task_get_scan_public')
            ->once()
            ->with('b9b5e877-bdfe-4b39-8c4b-8316e451730e')
            ->andReturn([
                'hostname' => 'www.example.com',
                'ip' => '93.184.215.14',
                'port' => 80,
                'protocol' => 'tcp',
                'service' => 'http',
                'product' => 'Cloudflare http proxy',
                'ssl' => false,
                'current_task' => 'alerter',
                'current_task_status' => 'DONE',
                'tags' => ['Http', 'Cloudflare'],
                'data' => [
                    [
                        "fromCache" => false,
                        "cacheTimestamp" => "",
                        "commandExecuted" => "/usr/local/bin/wpscan --api-token xxx --random-user-agent --detection-mode aggressive --disable-tls-checks -f json --url https://www.example.com",
                        "timestamp" => "2023-03-09T10:44:39.428704",
                        "execDuration" => 29.054311990737915,
                        "summary" => "Ran tool wpscan",
                        "tool" => "wpscan",
                        "rawOutput" => "{\n  \"banner\": {\n    \"description\": \"WordPress Security Scanner by the WPScan Team\",\n    \"version\": \"3.8.22\",\n    \"authors\": [\n      \"@_WPScan_\",\n      \"@ethicalhack3r\",\n      \"@erwan_lr\",\n      \"@firefart\"\n    ],\n    \"sponsor\": \"Sponsored by Automattic - https://automattic.com/\"\n  },\n  \"start_time\": 1678358684,\n  \"start_memory\": 45481984,\n  \"target_url\": \"https://www.example.com/\",\n  \"target_ip\": \"93.184.215.14\",\n  \"effective_url\": \"https://www.example.com/fr/\",\n  \"interesting_findings\": [\n    {\n      \"url\": \"https://www.example.com/xmlrpc.php\",\n      \"to_s\": \"XML-RPC seems to be enabled: https://www.example.com/xmlrpc.php\",\n      \"type\": \"xmlrpc\",\n      \"found_by\": \"Direct Access (Aggressive Detection)\",\n      \"confidence\": 100,\n      \"confirmed_by\": {\n\n      },\n      \"references\": {\n        \"url\": [\n          \"http://codex.wordpress.org/XML-RPC_Pingback_API\"\n        ],\n        \"metasploit\": [\n          \"auxiliary/scanner/http/wordpress_ghost_scanner\",\n          \"auxiliary/dos/http/wordpress_xmlrpc_dos\",\n          \"auxiliary/scanner/http/wordpress_xmlrpc_login\",\n          \"auxiliary/scanner/http/wordpress_pingback_access\"\n        ]\n      },\n      \"interesting_entries\": [\n\n      ]\n    },\n    {\n      \"url\": \"https://www.example.com/readme.html\",\n      \"to_s\": \"WordPress readme found: https://www.example.com/readme.html\",\n      \"type\": \"readme\",\n      \"found_by\": \"Direct Access (Aggressive Detection)\",\n      \"confidence\": 100,\n      \"confirmed_by\": {\n\n      },\n      \"references\": {\n\n      },\n      \"interesting_entries\": [\n\n      ]\n    },\n    {\n      \"url\": \"https://www.example.com/wp-content/backup-db/\",\n      \"to_s\": \"A backup directory has been found: https://www.example.com/wp-content/backup-db/\",\n      \"type\": \"backup_db\",\n      \"found_by\": \"Direct Access (Aggressive Detection)\",\n      \"confidence\": 70,\n      \"confirmed_by\": {\n\n      },\n      \"references\": {\n        \"url\": [\n          \"https://github.com/wpscanteam/wpscan/issues/422\"\n        ]\n      },\n      \"interesting_entries\": [\n\n      ]\n    },\n    {\n      \"url\": \"https://www.example.com/wp-cron.php\",\n      \"to_s\": \"The external WP-Cron seems to be enabled: https://www.example.com/wp-cron.php\",\n      \"type\": \"wp_cron\",\n      \"found_by\": \"Direct Access (Aggressive Detection)\",\n      \"confidence\": 60,\n      \"confirmed_by\": {\n\n      },\n      \"references\": {\n        \"url\": [\n          \"https://www.iplocation.net/defend-wordpress-from-ddos\",\n          \"https://github.com/wpscanteam/wpscan/issues/1299\"\n        ]\n      },\n      \"interesting_entries\": [\n\n      ]\n    }\n  ],\n  \"version\": {\n    \"number\": \"4.7.25\",\n    \"release_date\": \"2022-10-17\",\n    \"status\": \"outdated\",\n    \"found_by\": \"Rss Generator (Aggressive Detection)\",\n    \"confidence\": 100,\n    \"interesting_entries\": [\n      \"https://www.example.com/fr/feed/, <generator>https://wordpress.org/?v=4.7.25</generator>\",\n      \"https://www.example.com/fr/comments/feed/, <generator>https://wordpress.org/?v=4.7.25</generator>\"\n    ],\n    \"confirmed_by\": {\n\n    },\n    \"vulnerabilities\": [\n      {\n        \"title\": \"WP <= 6.1.1 - Unauthenticated Blind SSRF via DNS Rebinding\",\n        \"cvss\": {\n          \"score\": \"5.4\",\n          \"vector\": \"CVSS:3.1/AV:N/AC:H/PR:N/UI:N/S:C/C:L/I:L/A:N\"\n        },\n        \"fixed_in\": null,\n        \"references\": {\n          \"cve\": [\n            \"2022-3590\"\n          ],\n          \"url\": [\n            \"https://blog.sonarsource.com/wordpress-core-unauthenticated-blind-ssrf/\"\n          ],\n          \"wpvulndb\": [\n            \"c8814e6e-78b3-4f63-a1d3-6906a84c1f11\"\n          ]\n        }\n      }\n    ]\n  },\n  \"main_theme\": null,\n  \"plugins\": {\n    \"all-in-one-seo-pack\": {\n      \"slug\": \"all-in-one-seo-pack\",\n      \"location\": \"https://www.example.com/wp-content/plugins/all-in-one-seo-pack/\",\n      \"latest_version\": \"4.3.2\",\n      \"last_updated\": \"2023-03-02T18:34:00.000Z\",\n      \"outdated\": true,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Comment (Passive Detection)\",\n      \"confidence\": 30,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n\n      },\n      \"vulnerabilities\": [\n        {\n          \"title\": \"All In One SEO Pack < 3.2.7 - Stored Cross-Site Scripting (XSS)\",\n          \"cvss\": {\n            \"score\": \"5.4\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:L/UI:R/S:C/C:L/I:L/A:N\"\n          },\n          \"fixed_in\": \"3.2.7\",\n          \"references\": {\n            \"cve\": [\n              \"2019-16520\"\n            ],\n            \"url\": [\n              \"https://github.com/sbaresearch/advisories/tree/public/2019/SBA-ADV-20190913-04_WordPress_Plugin_All_in_One_SEO_Pack\"\n            ],\n            \"wpvulndb\": [\n              \"868dccee-089b-43d2-a80a-6cadba91f770\"\n            ]\n          }\n        },\n        {\n          \"title\": \"All in One SEO Pack < 3.6.2 - Authenticated Stored Cross-Site Scripting\",\n          \"cvss\": {\n            \"score\": \"5.4\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:L/UI:R/S:C/C:L/I:L/A:N\"\n          },\n          \"fixed_in\": \"3.6.2\",\n          \"references\": {\n            \"cve\": [\n              \"2020-35946\"\n            ],\n            \"url\": [\n              \"https://www.wordfence.com/blog/2020/07/2-million-users-affected-by-vulnerability-in-all-in-one-seo-pack/\"\n            ],\n            \"youtube\": [\n              \"https://www.youtube.com/watch?v=2fqMM6HRV5s\"\n            ],\n            \"wpvulndb\": [\n              \"528fff6c-54fe-4812-9b08-8c4e47350c83\"\n            ]\n          }\n        },\n        {\n          \"title\": \"All in One SEO Pack <  4.1.0.2 - Admin RCE via unserialize\",\n          \"cvss\": {\n            \"score\": \"6.6\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:H/UI:N/S:C/C:L/I:L/A:L\"\n          },\n          \"fixed_in\": \"4.1.0.2\",\n          \"references\": {\n            \"cve\": [\n              \"2021-24307\"\n            ],\n            \"url\": [\n              \"https://aioseo.com/changelog/\"\n            ],\n            \"wpvulndb\": [\n              \"ab2c94d2-f6c4-418b-bd14-711ed164bcf1\"\n            ]\n          }\n        },\n        {\n          \"title\": \"All in One SEO < 4.2.4 - Multiple CSRF\",\n          \"cvss\": {\n            \"score\": \"4.3\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:U/C:N/I:L/A:N\"\n          },\n          \"fixed_in\": \"4.2.4\",\n          \"references\": {\n            \"cve\": [\n              \"2022-38093\"\n            ],\n            \"wpvulndb\": [\n              \"5f31b537-186d-424c-a0c3-56f29146bb6e\"\n            ]\n          }\n        },\n        {\n          \"title\": \"All in One SEO Pack < 4.3.0 - Contributor+ Stored XSS\",\n          \"cvss\": {\n            \"score\": \"6.8\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:H/UI:R/S:U/C:H/I:H/A:H\"\n          },\n          \"fixed_in\": \"4.3.0\",\n          \"references\": {\n            \"cve\": [\n              \"2023-0586\"\n            ],\n            \"wpvulndb\": [\n              \"e2e78948-81cc-49e4-9a68-1a989a4a0585\"\n            ]\n          }\n        },\n        {\n          \"title\": \"All in One SEO Pack < 4.3.0 - Admin+ Stored XSS\",\n          \"cvss\": {\n            \"score\": \"3.1\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:H/PR:H/UI:R/S:U/C:L/I:L/A:N\"\n          },\n          \"fixed_in\": \"4.3.0\",\n          \"references\": {\n            \"cve\": [\n              \"2023-0585\"\n            ],\n            \"wpvulndb\": [\n              \"3ace429e-42a8-4b1b-8a39-5262f289cbd9\"\n            ]\n          }\n        }\n      ],\n      \"version\": {\n        \"number\": \"3.2.5\",\n        \"confidence\": 100,\n        \"found_by\": \"Comment (Passive Detection)\",\n        \"interesting_entries\": [\n          \"https://www.example.com/fr/, Match: 'All in One SEO Pack 3.2.5 by'\"\n        ],\n        \"confirmed_by\": {\n          \"Readme - Stable Tag (Aggressive Detection)\": {\n            \"confidence\": 80,\n            \"interesting_entries\": [\n              \"https://www.example.com/wp-content/plugins/all-in-one-seo-pack/readme.txt\"\n            ]\n          }\n        }\n      }\n    },\n    \"comments-like-dislike\": {\n      \"slug\": \"comments-like-dislike\",\n      \"location\": \"https://www.example.com/wp-content/plugins/comments-like-dislike/\",\n      \"latest_version\": \"1.1.8\",\n      \"last_updated\": \"2023-02-27T16:53:00.000Z\",\n      \"outdated\": true,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Urls In Homepage (Passive Detection)\",\n      \"confidence\": 100,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n        \"Urls In 404 Page (Passive Detection)\": {\n          \"confidence\": 80,\n          \"interesting_entries\": [\n\n          ]\n        }\n      },\n      \"vulnerabilities\": [\n        {\n          \"title\": \"Comments Like Dislike < 1.1.4 - Add Like/Dislike Bypass\",\n          \"cvss\": {\n            \"score\": \"5.3\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:N/I:L/A:N\"\n          },\n          \"fixed_in\": \"1.1.4\",\n          \"references\": {\n            \"cve\": [\n              \"2021-24379\"\n            ],\n            \"wpvulndb\": [\n              \"aae7a889-195c-45a3-bbe4-e6d4cd2d7fd9\"\n            ]\n          }\n        }\n      ],\n      \"version\": {\n        \"number\": \"1.0.2\",\n        \"confidence\": 100,\n        \"found_by\": \"Query Parameter (Passive Detection)\",\n        \"interesting_entries\": [\n          \"https://www.example.com/wp-content/plugins/comments-like-dislike/css/font-awesome.min.css?ver=1.0.2\",\n          \"https://www.example.com/wp-content/plugins/comments-like-dislike/css/cld-frontend.css?ver=1.0.2\",\n          \"https://www.example.com/wp-content/plugins/comments-like-dislike/js/cld-frontend.js?ver=1.0.2\"\n        ],\n        \"confirmed_by\": {\n          \"Readme - Stable Tag (Aggressive Detection)\": {\n            \"confidence\": 80,\n            \"interesting_entries\": [\n              \"https://www.example.com/wp-content/plugins/comments-like-dislike/readme.txt\"\n            ]\n          }\n        }\n      }\n    },\n    \"contact-form-7\": {\n      \"slug\": \"contact-form-7\",\n      \"location\": \"https://www.example.com/wp-content/plugins/contact-form-7/\",\n      \"latest_version\": \"5.7.4\",\n      \"last_updated\": \"2023-02-19T04:38:00.000Z\",\n      \"outdated\": true,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Urls In Homepage (Passive Detection)\",\n      \"confidence\": 100,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n        \"Urls In 404 Page (Passive Detection)\": {\n          \"confidence\": 80,\n          \"interesting_entries\": [\n\n          ]\n        }\n      },\n      \"vulnerabilities\": [\n        {\n          \"title\": \"Contact Form 7 <= 5.0.3 - register_post_type() Privilege Escalation\",\n          \"cvss\": {\n            \"score\": \"9.8\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H\"\n          },\n          \"fixed_in\": \"5.0.4\",\n          \"references\": {\n            \"cve\": [\n              \"2018-20979\"\n            ],\n            \"url\": [\n              \"https://contactform7.com/2018/09/04/contact-form-7-504/\",\n              \"https://plugins.trac.wordpress.org/changeset/1935726/contact-form-7\",\n              \"https://plugins.trac.wordpress.org/changeset/1934594/contact-form-7\",\n              \"https://plugins.trac.wordpress.org/changeset/1934343/contact-form-7\",\n              \"https://plugins.trac.wordpress.org/changeset/1934327/contact-form-7\",\n              \"https://www.ripstech.com/php-security-calendar-2018/#day-18\"\n            ],\n            \"wpvulndb\": [\n              \"af945f64-9ce2-485c-bf36-c2ff59dc10d5\"\n            ]\n          }\n        },\n        {\n          \"title\": \"Contact Form 7 < 5.3.2 - Unrestricted File Upload\",\n          \"cvss\": {\n            \"score\": \"8.1\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:H/PR:N/UI:N/S:U/C:H/I:H/A:H\"\n          },\n          \"fixed_in\": \"5.3.2\",\n          \"references\": {\n            \"cve\": [\n              \"2020-35489\"\n            ],\n            \"url\": [\n              \"https://www.getastra.com/blog/911/plugin-exploit/contact-form-7-unrestricted-file-upload-vulnerability/\",\n              \"https://www.jinsonvarghese.com/unrestricted-file-upload-in-contact-form-7/\",\n              \"https://contactform7.com/2020/12/17/contact-form-7-532/#more-38314\"\n            ],\n            \"wpvulndb\": [\n              \"7391118e-eef5-4ff8-a8ea-f6b65f442c63\"\n            ]\n          }\n        }\n      ],\n      \"version\": {\n        \"number\": \"4.7\",\n        \"confidence\": 100,\n        \"found_by\": \"Query Parameter (Passive Detection)\",\n        \"interesting_entries\": [\n          \"https://www.example.com/wp-content/plugins/contact-form-7/includes/css/styles.css?ver=4.7\",\n          \"https://www.example.com/wp-content/plugins/contact-form-7/includes/js/scripts.js?ver=4.7\"\n        ],\n        \"confirmed_by\": {\n          \"Readme - Stable Tag (Aggressive Detection)\": {\n            \"confidence\": 80,\n            \"interesting_entries\": [\n              \"https://www.example.com/wp-content/plugins/contact-form-7/readme.txt\"\n            ]\n          },\n          \"Readme - ChangeLog Section (Aggressive Detection)\": {\n            \"confidence\": 50,\n            \"interesting_entries\": [\n              \"https://www.example.com/wp-content/plugins/contact-form-7/readme.txt\"\n            ]\n          }\n        }\n      }\n    },\n    \"gasInternal\": {\n      \"slug\": \"gasInternal\",\n      \"location\": \"https://www.example.com/wp-content/plugins/gasInternal/\",\n      \"latest_version\": null,\n      \"last_updated\": null,\n      \"outdated\": false,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Urls In Homepage (Passive Detection)\",\n      \"confidence\": 100,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n        \"Urls In 404 Page (Passive Detection)\": {\n          \"confidence\": 80,\n          \"interesting_entries\": [\n\n          ]\n        }\n      },\n      \"vulnerabilities\": [\n\n      ],\n      \"version\": null\n    },\n    \"hustle\": {\n      \"slug\": \"hustle\",\n      \"location\": \"https://www.example.com/wp-content/plugins/hustle/\",\n      \"latest_version\": null,\n      \"last_updated\": null,\n      \"outdated\": false,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Urls In Homepage (Passive Detection)\",\n      \"confidence\": 100,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n        \"Urls In 404 Page (Passive Detection)\": {\n          \"confidence\": 80,\n          \"interesting_entries\": [\n\n          ]\n        }\n      },\n      \"vulnerabilities\": [\n\n      ],\n      \"version\": null\n    },\n    \"popover\": {\n      \"slug\": \"popover\",\n      \"location\": \"https://www.example.com/wp-content/plugins/popover/\",\n      \"latest_version\": null,\n      \"last_updated\": null,\n      \"outdated\": false,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Urls In Homepage (Passive Detection)\",\n      \"confidence\": 100,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n        \"Urls In 404 Page (Passive Detection)\": {\n          \"confidence\": 80,\n          \"interesting_entries\": [\n\n          ]\n        }\n      },\n      \"vulnerabilities\": [\n\n      ],\n      \"version\": null\n    },\n    \"qtranslate-slug\": {\n      \"slug\": \"qtranslate-slug\",\n      \"location\": \"https://www.example.com/wp-content/plugins/qtranslate-slug/\",\n      \"latest_version\": \"1.1.18\",\n      \"last_updated\": \"2016-01-21T04:48:00.000Z\",\n      \"outdated\": false,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Urls In Homepage (Passive Detection)\",\n      \"confidence\": 100,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n        \"Urls In 404 Page (Passive Detection)\": {\n          \"confidence\": 80,\n          \"interesting_entries\": [\n\n          ]\n        }\n      },\n      \"vulnerabilities\": [\n        {\n          \"title\": \"CSRF Bypass in Multiple Plugins\",\n          \"cvss\": {\n            \"score\": \"5.4\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:U/C:L/I:L/A:N\"\n          },\n          \"fixed_in\": null,\n          \"references\": {\n            \"url\": [\n              \"https://blog.nintechnet.com/multiple-wordpress-plugins-fixed-csrf-vulnerabilities-part-2/\",\n              \"https://blog.nintechnet.com/multiple-wordpress-plugins-fixed-csrf-vulnerabilities-part-3/\"\n            ],\n            \"wpvulndb\": [\n              \"3725296b-c316-440a-875a-3068fb876b3b\"\n            ]\n          }\n        }\n      ],\n      \"version\": {\n        \"number\": \"1.1.18\",\n        \"confidence\": 80,\n        \"found_by\": \"Readme - Stable Tag (Aggressive Detection)\",\n        \"interesting_entries\": [\n          \"https://www.example.com/wp-content/plugins/qtranslate-slug/readme.txt\"\n        ],\n        \"confirmed_by\": {\n\n        }\n      }\n    },\n    \"qtranslate-x\": {\n      \"slug\": \"qtranslate-x\",\n      \"location\": \"https://www.example.com/wp-content/plugins/qtranslate-x/\",\n      \"latest_version\": \"3.4.6.8\",\n      \"last_updated\": \"2016-07-13T17:36:00.000Z\",\n      \"outdated\": false,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Urls In Homepage (Passive Detection)\",\n      \"confidence\": 100,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n        \"Urls In 404 Page (Passive Detection)\": {\n          \"confidence\": 80,\n          \"interesting_entries\": [\n\n          ]\n        }\n      },\n      \"vulnerabilities\": [\n        {\n          \"title\": \"qTranslate X <= 3.4.6.8 - Multiple Admin+ Stored Cross-Site Scripting\",\n          \"cvss\": {\n            \"score\": \"3.5\",\n            \"vector\": \"CVSS:3.1/AV:N/AC:L/PR:H/UI:R/S:U/C:L/I:L/A:N\"\n          },\n          \"fixed_in\": null,\n          \"references\": {\n            \"wpvulndb\": [\n              \"2a0917ac-0e35-4a3c-9a3c-d0a4b178061b\"\n            ]\n          }\n        }\n      ],\n      \"version\": {\n        \"number\": \"3.4.6.8\",\n        \"confidence\": 80,\n        \"found_by\": \"Readme - Stable Tag (Aggressive Detection)\",\n        \"interesting_entries\": [\n          \"https://www.example.com/wp-content/plugins/qtranslate-x/readme.txt\"\n        ],\n        \"confirmed_by\": {\n\n        }\n      }\n    },\n    \"zrInternal\": {\n      \"slug\": \"zrInternal\",\n      \"location\": \"https://www.example.com/wp-content/plugins/zrInternal/\",\n      \"latest_version\": null,\n      \"last_updated\": null,\n      \"outdated\": false,\n      \"readme_url\": null,\n      \"directory_listing\": null,\n      \"error_log_url\": null,\n      \"found_by\": \"Urls In Homepage (Passive Detection)\",\n      \"confidence\": 100,\n      \"interesting_entries\": [\n\n      ],\n      \"confirmed_by\": {\n        \"Urls In 404 Page (Passive Detection)\": {\n          \"confidence\": 80,\n          \"interesting_entries\": [\n\n          ]\n        }\n      },\n      \"vulnerabilities\": [\n\n      ],\n      \"version\": null\n    }\n  },\n  \"config_backups\": {\n\n  },\n  \"vuln_api\": {\n    \"plan\": \"enterprise\",\n    \"requests_done_during_scan\": 10,\n    \"requests_remaining\": \"Unlimited\"\n  },\n  \"stop_time\": 1678358708,\n  \"elapsed\": 24,\n  \"requests_done\": 213,\n  \"cached_requests\": 2,\n  \"data_sent\": 63741,\n  \"data_sent_humanised\": \"62.247 KB\",\n  \"data_received\": 1230027,\n  \"data_received_humanised\": \"1.173 MB\",\n  \"used_memory\": 242937856,\n  \"used_memory_humanised\": \"231.684 MB\"\n}\n",
                        "toolVersion" => "0.0.1a",
                        "error" => "",
                        "alerts" => [ // Alerts V2
                            '', // edge case...
                            [
                                "asset" => "www.example.com",
                                "port" => 443,
                                "protocol" => "tcp",
                                "tool" => "wpscan",
                                "type" => "wordpress_vuln",
                                "title" => "Wordpress core issue",
                                "level" => "Low",
                                "vulnerability" => "WP <= 6.1.1 - Unauthenticated Blind SSRF via DNS Rebinding",
                                "remediation" => "Update Wordpress if possible.\nGet more information about the vulnerability at https://wpscan.com/vulnerability/c8814e6e-78b3-4f63-a1d3-6906a84c1f11\nMore references:\nhttps://blog.sonarsource.com/wordpress-core-unauthenticated-blind-ssrf/",
                                "cve_id" => "",
                                "cve_cvss" => "",
                                "cve_vendor" => "",
                                "cve_product" => "",
                                "uid" => "3ee22091822f95e8b2095c228f397a63"
                            ]
                        ]
                    ]
                ],
            ]);

        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);
        $asset->tags()->create(['tag' => 'demo']);

        TriggerScan::dispatch();

        $asset = Asset::find($asset->id); // reload from db

        // Check the assets table
        $this->assertNull($asset->prev_scan_id);
        $this->assertEquals('6409ae68ed42e11e31e5f19d', $asset->cur_scan_id); // Events are sync during tests...
        $this->assertNull($asset->next_scan_id);

        // Check the assets_tags table
        $assetTags = AssetTag::where('asset_id', $asset->id)->get();
        $this->assertEquals(['demo'], $assetTags->pluck('tag')->toArray());

        // Check the scans table
        $scans = Scan::where('asset_id', $asset->id)->get();
        $this->assertEquals(2, $scans->count());
        $this->assertEquals(2, $scans->filter(fn(Scan $scan) => $scan->portsScanHasEnded() && $scan->vulnsScanHasEnded())->count());

        // Check the ports table
        $ports = Port::whereIn('scan_id', $scans->pluck('id'))->get();
        $this->assertEquals(2, $ports->count());
        $this->assertEquals(1, $ports->filter(function (Port $port) {
            return $port->hostname === 'www.example.com'
                && $port->ip === '93.184.215.14'
                && $port->port === 443
                && $port->protocol === 'tcp'
                && $port->country === 'US'
                && $port->hosting_service_description === null
                && $port->hosting_service_registry === null
                && $port->hosting_service_asn === null
                && $port->hosting_service_cidr === null
                && $port->hosting_service_country_code === null
                && $port->hosting_service_date === null
                && $port->service === 'http'
                && $port->product === 'Cloudflare http proxy'
                && $port->ssl === true;
        })->count());
        $this->assertEquals(1, $ports->filter(function (Port $port) {
            return $port->hostname === 'www.example.com'
                && $port->ip === '93.184.215.14'
                && $port->port === 80
                && $port->protocol === 'tcp'
                && $port->country === 'US'
                && $port->hosting_service_description === null
                && $port->hosting_service_registry === null
                && $port->hosting_service_asn === null
                && $port->hosting_service_cidr === null
                && $port->hosting_service_country_code === null
                && $port->hosting_service_date === null
                && $port->service === 'http'
                && $port->product === 'Cloudflare http proxy'
                && $port->ssl === false;
        })->count());

        // Check the ports_tags table
        $portsTags = PortTag::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(4, $portsTags->count());

        $portsTagsByPort = $portsTags->groupBy('port_id');
        $this->assertEquals(['cloudflare', 'http'], $portsTagsByPort[$ports->first()->id]->sortBy('tag')->pluck('tag')->toArray());
        $this->assertEquals(['cloudflare', 'http'], $portsTagsByPort[$ports->last()->id]->sortBy('tag')->pluck('tag')->toArray());

        // Check the alerts table
        $alerts = Alert::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(2, $alerts->count());
        $this->assertEquals(1, $alerts->filter(function (Alert $alert) {
            return $alert->type === 'wordpress_vuln_v3_alert'
                && $alert->vulnerability === 'WP <= 6.1.1 - Unauthenticated Blind SSRF via DNS Rebinding'
                && $alert->remediation === "Update Wordpress if possible.\nGet more information about the vulnerability at https://wpscan.com/vulnerability/c8814e6e-78b3-4f63-a1d3-6906a84c1f11\nMore references:\nhttps://blog.sonarsource.com/wordpress-core-unauthenticated-blind-ssrf/"
                && $alert->level === 'Low'
                && $alert->uid === '3ee22091822f95e8b2095c228f397a63'
                && $alert->cve_id === null
                && $alert->cve_cvss === null
                && $alert->cve_vendor === null
                && $alert->cve_product === null
                && $alert->title === 'Wordpress core issue'
                && $alert->flarum_slug === null;
        })->count());
        $this->assertEquals(1, $alerts->filter(function (Alert $alert) {
            return $alert->type === 'wordpress_vuln_v2_alert'
                && $alert->vulnerability === 'WP <= 6.1.1 - Unauthenticated Blind SSRF via DNS Rebinding'
                && $alert->remediation === "Update Wordpress if possible.\nGet more information about the vulnerability at https://wpscan.com/vulnerability/c8814e6e-78b3-4f63-a1d3-6906a84c1f11\nMore references:\nhttps://blog.sonarsource.com/wordpress-core-unauthenticated-blind-ssrf/"
                && $alert->level === 'Low'
                && $alert->uid === '3ee22091822f95e8b2095c228f397a63'
                && $alert->cve_id === null
                && $alert->cve_cvss === null
                && $alert->cve_vendor === null
                && $alert->cve_product === null
                && $alert->title === 'Wordpress core issue'
                && $alert->flarum_slug === null;
        })->count());

        // Cleanup
        $asset->delete();

        // Ensure that removing an asset remove all associated data
        $assetId = $asset->id;
        $asset = Asset::find($assetId);
        $this->assertNull($asset);

        // Check the assets_tags table
        $assetTags = AssetTag::where('asset_id', $assetId)->get();
        $this->assertEquals(0, $assetTags->count());

        // Check the scans table
        $scans = Scan::where('asset_id', $assetId)->get();
        $this->assertEquals(0, $scans->count());

        // Check the ports table
        $ports = Port::whereIn('scan_id', $scans->pluck('id'))->get();
        $this->assertEquals(0, $ports->count());

        // Check the ports_tags table
        $portsTags = PortTag::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(0, $portsTags->count());

        // Check the alerts table
        $alerts = Alert::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(0, $alerts->count());
    }

    public function testItDoesNotModifyTheDbWhenPortsScanFailsToStart()
    {
        ApiUtils::shouldReceive('task_nmap_public')
            ->once()
            ->with('www.example.com')
            ->andReturn([
                'task_id' => null,
            ]);

        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);
        $asset->tags()->create(['tag' => 'demo']);

        TriggerScan::dispatch();

        $asset = Asset::find($asset->id); // reload from db

        // Check the assets table
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);

        // Check the scans table
        $scans = Scan::where('asset_id', $asset->id)->get();
        $this->assertEquals(0, $scans->count());

        // Cleanup
        $asset->delete();
    }

    public function testItDoesNotModifyTheDbWhenPortsScanFailsToComplete()
    {
        ApiUtils::shouldReceive('task_nmap_public')
            ->once()
            ->with('www.example.com')
            ->andReturn([
                'task_id' => '6409ae68ed42e11e31e5f19d',
            ]);
        ApiUtils::shouldReceive('task_status_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_status' => 'ERROR',
            ]);

        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);
        $asset->tags()->create(['tag' => 'demo']);

        TriggerScan::dispatch();

        $asset = Asset::find($asset->id); // reload from db

        // Check the assets table
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);

        // Check the scans table
        $scans = Scan::where('asset_id', $asset->id)->get();
        $this->assertEquals(0, $scans->count());

        // Cleanup
        $asset->delete();
    }

    public function testItDoesNotModifyTheDbWhenVulnsScanFailsToStart()
    {
        ApiUtils::shouldReceive('task_nmap_public')
            ->once()
            ->with('www.example.com')
            ->andReturn([
                'task_id' => '6409ae68ed42e11e31e5f19d',
            ]);
        ApiUtils::shouldReceive('task_status_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_status' => 'SUCCESS',
            ]);
        ApiUtils::shouldReceive('task_result_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_result' => [
                    [
                        'hostname' => 'www.example.com',
                        'ip' => '93.184.215.14',
                        'port' => 443,
                        'protocol' => 'tcp',
                    ]
                ],
            ]);
        ApiUtils::shouldReceive('ip_geoloc_public')
            ->once()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'country' => [
                        'iso_code' => 'US',
                    ],
                ],
            ]);
        ApiUtils::shouldReceive('ip_whois_public')
            ->once()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'asn_description' => null,
                    'asn_registry' => null,
                    'asn' => null,
                    'asn_cidr' => null,
                    'asn_country_code' => null,
                    'asn_date' => null,
                ],
            ]);
        ApiUtils::shouldReceive('task_start_scan_public')
            ->once()
            ->with('www.example.com', '93.184.215.14', 443, 'tcp', ['demo'])
            ->andReturn([
                'scan_id' => null,
            ]);

        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);
        $asset->tags()->create(['tag' => 'demo']);

        TriggerScan::dispatch();

        $asset = Asset::find($asset->id); // reload from db

        // Check the assets table
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);

        // Check the scans table
        $scans = Scan::where('asset_id', $asset->id)->get();
        $this->assertEquals(0, $scans->count());

        // Cleanup
        $asset->delete();
    }

    public function testItDoesNotModifyTheDbWhenVulnsScanFailsToComplete()
    {
        ApiUtils::shouldReceive('task_nmap_public')
            ->once()
            ->with('www.example.com')
            ->andReturn([
                'task_id' => '6409ae68ed42e11e31e5f19d',
            ]);
        ApiUtils::shouldReceive('task_status_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_status' => 'SUCCESS',
            ]);
        ApiUtils::shouldReceive('task_result_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_result' => [
                    [
                        'hostname' => 'www.example.com',
                        'ip' => '93.184.215.14',
                        'port' => 443,
                        'protocol' => 'tcp',
                    ]
                ],
            ]);
        ApiUtils::shouldReceive('ip_geoloc_public')
            ->once()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'country' => [
                        'iso_code' => 'US',
                    ],
                ],
            ]);
        ApiUtils::shouldReceive('ip_whois_public')
            ->once()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'asn_description' => null,
                    'asn_registry' => null,
                    'asn' => null,
                    'asn_cidr' => null,
                    'asn_country_code' => null,
                    'asn_date' => null,
                ],
            ]);
        ApiUtils::shouldReceive('task_start_scan_public')
            ->once()
            ->with('www.example.com', '93.184.215.14', 443, 'tcp', ['demo'])
            ->andReturn([
                'scan_id' => 'a9a5d877-abed-4a39-8b4a-8316d451730d',
            ]);
        ApiUtils::shouldReceive('task_get_scan_public')
            ->times(100)
            ->with('a9a5d877-abed-4a39-8b4a-8316d451730d')
            ->andReturn([
                'hostname' => 'www.example.com',
                'ip' => '93.184.215.14',
                'port' => 443,
                'protocol' => 'tcp',
                'service' => 'http',
                'product' => 'Cloudflare http proxy',
                'ssl' => true,
                'current_task' => 'alerter',
                'current_task_status' => 'ERROR',
                'tags' => ['Http', 'Cloudflare'],
                'data' => [
                    //
                ],
            ]);

        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);
        $asset->tags()->create(['tag' => 'demo']);

        TriggerScan::dispatch();

        $asset = Asset::find($asset->id); // reload from db

        // Check the assets table
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);

        // Check the scans table
        $scans = Scan::where('asset_id', $asset->id)->get();
        $this->assertEquals(0, $scans->count());

        // Cleanup
        $asset->delete();
    }

    public function testItProperlyEndsWhenVulnsScanMarkThePortAsClosed()
    {
        ApiUtils::shouldReceive('task_nmap_public')
            ->once()
            ->with('www.example.com')
            ->andReturn([
                'task_id' => '6409ae68ed42e11e31e5f19d',
            ]);
        ApiUtils::shouldReceive('task_status_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_status' => 'SUCCESS',
            ]);
        ApiUtils::shouldReceive('task_result_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_result' => [
                    [
                        'hostname' => 'www.example.com',
                        'ip' => '93.184.215.14',
                        'port' => 443,
                        'protocol' => 'tcp',
                    ]
                ],
            ]);
        ApiUtils::shouldReceive('ip_geoloc_public')
            ->once()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'country' => [
                        'iso_code' => 'US',
                    ],
                ],
            ]);
        ApiUtils::shouldReceive('ip_whois_public')
            ->once()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'asn_description' => null,
                    'asn_registry' => null,
                    'asn' => null,
                    'asn_cidr' => null,
                    'asn_country_code' => null,
                    'asn_date' => null,
                ],
            ]);
        ApiUtils::shouldReceive('task_start_scan_public')
            ->once()
            ->with('www.example.com', '93.184.215.14', 443, 'tcp', ['demo'])
            ->andReturn([
                'scan_id' => 'a9a5d877-abed-4a39-8b4a-8316d451730d',
            ]);
        ApiUtils::shouldReceive('task_get_scan_public')
            ->once()
            ->with('a9a5d877-abed-4a39-8b4a-8316d451730d')
            ->andReturn([
                'hostname' => 'www.example.com',
                'ip' => '93.184.215.14',
                'port' => 443,
                'protocol' => 'tcp',
                'service' => 'closed',
                'product' => 'Cloudflare http proxy',
                'ssl' => true,
                'current_task' => 'alerter',
                'current_task_status' => 'ERROR',
                'tags' => ['Http', 'Cloudflare'],
                'data' => [
                    //
                ],
            ]);

        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);
        $asset->tags()->create(['tag' => 'demo']);

        TriggerScan::dispatch();

        $asset = Asset::find($asset->id); // reload from db

        // Check the assets table
        $this->assertNull($asset->prev_scan_id);
        $this->assertEquals('6409ae68ed42e11e31e5f19d', $asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);

        // Check the assets_tags table
        $assetTags = AssetTag::where('asset_id', $asset->id)->get();
        $this->assertEquals(['demo'], $assetTags->pluck('tag')->toArray());

        // Check the scans table
        $scans = Scan::where('asset_id', $asset->id)->get();
        $this->assertEquals(1, $scans->count());
        $this->assertEquals(1, $scans->filter(fn(Scan $scan) => $scan->portsScanHasEnded() && $scan->vulnsScanHasEnded())->count());

        // Check the ports table
        $ports = Port::whereIn('scan_id', $scans->pluck('id'))->get();
        $this->assertEquals(1, $ports->count());
        $this->assertEquals(1, $ports->filter(function (Port $port) {
            return $port->hostname === 'www.example.com'
                && $port->ip === '93.184.215.14'
                && $port->port === 443
                && $port->protocol === 'tcp'
                && $port->country === 'US'
                && $port->hosting_service_description === null
                && $port->hosting_service_registry === null
                && $port->hosting_service_asn === null
                && $port->hosting_service_cidr === null
                && $port->hosting_service_country_code === null
                && $port->hosting_service_date === null
                && $port->service === null
                && $port->product === null
                && $port->ssl === null;
        })->count());

        // Check the ports_tags table
        $portsTags = PortTag::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(0, $portsTags->count());

        // Check the alerts table
        $alerts = Alert::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(0, $alerts->count());

        // Cleanup
        $asset->delete();

        // Cleanup
        $asset->delete();
    }
}
