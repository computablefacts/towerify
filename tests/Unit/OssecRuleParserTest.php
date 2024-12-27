<?php

namespace Tests\Unit;

use App\Helpers\OssecRulesParser;
use Tests\TestCase;

class OssecRuleParserTest extends TestCase
{
    public function testMatchFilesAndDirectories()
    {
        $rule = OssecRulesParser::parse("
            \$conf-dirs=/etc/apache2/conf-enabled,/etc/apache2/mods-enabled,/etc/apache2/sites-enabled,/etc/httpd/conf.d,/etc/httpd/modsecurity.d;
            \$mods-en=/etc/apache2/mods-enabled;
            
            [CIS - Apache Configuration - 2.3: WebDAV Modules are enabled] [any] [https://workbench.cisecurity.org/benchmarks/307, https://workbench.cisecurity.org/benchmarks/308]
            d:\$conf-dirs -> conf -> !r:^# && r:loadmodule\sdav;
            d:\$conf-dirs -> load -> !r:^# && r:loadmodule\sdav;
            f:/etc/httpd/conf.d -> !r:^# && r:loadmodule\sdav;
            d:\$mods-en -> dav.load;
        ");

        $this->assertEquals([
            'rule_name' => 'CIS - Apache Configuration - 2.3: WebDAV Modules are enabled',
            'match_type' => 'any',
            'references' => [
                'https://workbench.cisecurity.org/benchmarks/307',
                'https://workbench.cisecurity.org/benchmarks/308'
            ],
            'rules' => [
                [
                    'type' => 'directory',
                    'directories' => [
                        '/etc/apache2/conf-enabled',
                        '/etc/apache2/mods-enabled',
                        '/etc/apache2/sites-enabled',
                        '/etc/httpd/conf.d',
                        '/etc/httpd/modsecurity.d'
                    ],
                    'files' => 'conf',
                    'expr' => [
                        '!r:^#',
                        'r:loadmodule\sdav'
                    ],
                    'negate' => false,
                ],
                [
                    'type' => 'directory',
                    'directories' => [
                        '/etc/apache2/conf-enabled',
                        '/etc/apache2/mods-enabled',
                        '/etc/apache2/sites-enabled',
                        '/etc/httpd/conf.d',
                        '/etc/httpd/modsecurity.d'
                    ],
                    'files' => 'load',
                    'expr' => [
                        '!r:^#',
                        'r:loadmodule\sdav'
                    ],
                    'negate' => false,
                ],
                [
                    'type' => 'file_or_directory',
                    'files' => [
                        '/etc/httpd/conf.d'
                    ],
                    'expr' => [
                        '!r:^#',
                        'r:loadmodule\sdav'
                    ],
                    'negate' => false,
                ],
                [
                    'type' => 'directory',
                    'directories' => [
                        '/etc/apache2/mods-enabled'
                    ],
                    'files' => 'dav.load',
                    'expr' => null,
                    'negate' => false,
                ],
            ],
        ], $rule);
    }

    public function testMatchRegistryEntries()
    {
        $rule = OssecRulesParser::parse("
            [CIS - Microsoft Windows Server 2012 R2 - 18.3.5: Ensure 'MSS: (KeepAliveTime) How often keep-alive packets are sent in milliseconds' is set to 'Enabled: 300,000 or 5 minutes'] [any] [https://workbench.cisecurity.org/benchmarks/288]
            r:HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters -> KeepAliveTime -> !493e0;
            r:HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters -> !KeepAliveTime;
        ");

        $this->assertEquals([
            'rule_name' => 'CIS - Microsoft Windows Server 2012 R2 - 18.3.5: Ensure \'MSS: (KeepAliveTime) How often keep-alive packets are sent in milliseconds\' is set to \'Enabled: 300,000 or 5 minutes\'',
            'match_type' => 'any',
            'references' => [
                'https://workbench.cisecurity.org/benchmarks/288'
            ],
            'rules' => [
                [
                    'type' => 'registry',
                    'entry' => 'HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters',
                    'key' => 'KeepAliveTime',
                    'value' => [
                        '!493e0'
                    ],
                    'negate' => false,
                ],
                [
                    'type' => 'registry',
                    'entry' => 'HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters',
                    'key' => '!KeepAliveTime',
                    'value' => null,
                    'negate' => false,
                ],
            ],
        ], $rule);
    }

    public function testMatchCommand1()
    {
        $rule = OssecRulesParser::parse("
            [Ensure rsync service is either not installed or masked] [any] []
            c:dpkg-query -W -f='\${binary:Package}\\t\${Status}\\t\${db:Status-Status}\\n' rsync -> r:unknown ok not-installed|dpkg-query: no packages found matching rsync;
            c:systemctl is-active rsync -> r:^inactive;
            c:systemctl is-enabled rsync -> r:^masked;
        ");

        $this->assertEquals([
            'rule_name' => 'Ensure rsync service is either not installed or masked',
            'match_type' => 'any',
            'references' => [],
            'rules' => [
                [
                    'type' => 'command',
                    'cmd' => 'dpkg-query -W -f=\'${binary:Package}\t${Status}\t${db:Status-Status}\n\' rsync',
                    'expr' => [
                        'r:unknown ok not-installed|dpkg-query: no packages found matching rsync'
                    ],
                    'negate' => false,
                ],
                [
                    'type' => 'command',
                    'cmd' => 'systemctl is-active rsync',
                    'expr' => [
                        'r:^inactive'
                    ],
                    'negate' => false,
                ],
                [
                    'type' => 'command',
                    'cmd' => 'systemctl is-enabled rsync',
                    'expr' => [
                        'r:^masked'
                    ],
                    'negate' => false,
                ],
            ]
        ], $rule);
    }

    public function testMatchCommand2()
    {
        $rule = OssecRulesParser::parse("
            [Ensure only strong Ciphers are used] [none] [https://nvd.nist.gov/vuln/detail/CVE-2016-2183,https://www.openssh.com/txt/cbc.adv,https://nvd.nist.gov/vuln/detail/CVE-2008-5161]
            c:sshd -T -> r:^ciphers && r:3des-cbc|aes128-cbc|aes192-cbc|aes256-cbc;
        ");

        $this->assertEquals([
            'rule_name' => 'Ensure only strong Ciphers are used',
            'match_type' => 'none',
            'references' => [
                'https://nvd.nist.gov/vuln/detail/CVE-2016-2183',
                'https://www.openssh.com/txt/cbc.adv',
                'https://nvd.nist.gov/vuln/detail/CVE-2008-5161'
            ],
            'rules' => [
                [
                    'type' => 'command',
                    'cmd' => 'sshd -T',
                    'expr' => [
                        'r:^ciphers',
                        'r:3des-cbc|aes128-cbc|aes192-cbc|aes256-cbc'
                    ],
                    'negate' => false,
                ]
            ]
        ], $rule);
    }

    public function testParsePasswordHistoryRule()
    {
        $rule = OssecRulesParser::parse("
            [Ensure 'Enforce password history' is set to '24 or more password(s)'.] [all] [https://www.cisecurity.org/white-papers/cis-password-policy-guide/]
            c:net.exe accounts -> n:Length of password history maintained:\s+(\d+) compare >= 24;
        ");

        $this->assertEquals([
            'rule_name' => 'Ensure \'Enforce password history\' is set to \'24 or more password(s)\'.',
            'match_type' => 'all',
            'references' => [
                'https://www.cisecurity.org/white-papers/cis-password-policy-guide/'
            ],
            'rules' => [
                [
                    'type' => 'command',
                    'cmd' => 'net.exe accounts',
                    'expr' => [
                        'n:Length of password history maintained:\s+(\d+) compare >= 24'
                    ],
                    'negate' => false,
                ]
            ]
        ], $rule);
    }

    public function testEvaluateRegex()
    {
        $ctx = [
            'file_get_contents' => function (string $file) {
                return "
                    127.0.0.1       localhost
                    127.0.1.1       InfinityBook
                    
                    # The following lines are desirable for IPv6 capable hosts
                    ::1     ip6-localhost ip6-loopback
                    fe00::0 ip6-localnet
                    ff00::0 ip6-mcastprefix
                    ff02::1 ip6-allnodes
                    ff02::2 ip6-allrouters
                ";
            },
            'file_exists' => function (string $file) {
                return true;
            },
        ];
        $rule = OssecRulesParser::parse("
            [localhost resolves to 127.0.0.1] [all] []
            f:/etc/hosts -> !r:^# && r:127.0.0.1\s+localhost\s*$;
        ");

        $this->assertTrue(OssecRulesParser::evaluate($ctx, $rule));

        $ctx = [
            'file_get_contents' => function (string $file) {
                return "
                    # 127.0.0.1       localhost
                    # 127.0.1.1       InfinityBook
                    
                    # The following lines are desirable for IPv6 capable hosts
                    ::1     ip6-localhost ip6-loopback
                    fe00::0 ip6-localnet
                    ff00::0 ip6-mcastprefix
                    ff02::1 ip6-allnodes
                    ff02::2 ip6-allrouters
                ";
            },
            'file_exists' => function (string $file) {
                return true;
            },
        ];
        $rule = OssecRulesParser::parse("
            [localhost resolves to 127.0.0.1] [all] []
            f:/etc/hosts -> !r:^# && r:127.0.0.1\s+localhost\s*$;
        ");

        $this->assertFalse(OssecRulesParser::evaluate($ctx, $rule));
    }

    public function testEvaluateComparison()
    {
        $ctx = [
            'file_get_contents' => function (string $file) {
                return "
                    cpu  2867769 316 693554 80312777 178490 0 11546 0 0 0
                    cpu0 134305 2 32649 3997830 12958 0 2832 0 0 0
                    cpu1 93395 1 100269 3998975 2162 0 749 0 0 0
                    cpu2 139640 4 39372 4003964 14489 0 1618 0 0 0
                    cpu3 95970 0 14349 4106525 4167 0 548 0 0 0
                    cpu4 252280 37 55259 3874689 14455 0 747 0 0 0
                    cpu5 166302 130 26380 4006604 4778 0 244 0 0 0
                    cpu6 266999 36 61192 3847732 15348 0 1619 0 0 0
                    cpu7 158375 1 21495 3961949 2067 0 238 0 0 0
                    cpu8 196219 0 50447 3932667 15842 0 508 0 0 0
                    cpu9 110983 49 13231 4097801 2690 0 375 0 0 0
                    cpu10 181207 0 45449 3958400 14000 0 462 0 0 0
                    cpu11 110142 10 15418 4092677 3206 0 433 0 0 0
                    cpu12 161520 2 41578 3987663 14936 0 169 0 0 0
                    cpu13 151379 2 39005 4004566 12484 0 99 0 0 0
                    cpu14 143826 0 32161 4022775 11247 0 208 0 0 0
                    cpu15 139080 30 28446 4034292 10468 0 123 0 0 0
                    cpu16 91379 0 21864 4095806 5870 0 216 0 0 0
                    cpu17 91431 1 19706 4099079 6031 0 144 0 0 0
                    cpu18 91344 0 17961 4102717 5843 0 119 0 0 0
                    cpu19 91984 4 17313 4086054 5438 0 88 0 0 0
                ";
            },
            'file_exists' => function (string $file) {
                return true;
            },
        ];
        $rule = OssecRulesParser::parse("
            [CPU user time is above 2000000 and less than 3000000] [all] []
            f:/proc/stat -> n:^cpu\s+(\d+)\s+.*$ compare >= 2000000 && n:^cpu\s+(\d+)\s+.*$ compare <= 3000000;
            f:/proc/stat -> !n:^cpu\s+(\d+)\s+.*$ compare < 2000000 && !n:^cpu\s+(\d+)\s+.*$ compare > 3000000;
        ");

        $this->assertTrue(OssecRulesParser::evaluate($ctx, $rule));

        $rule = OssecRulesParser::parse("
            [CPU user time is above 2000000 and less than 3000000] [any] []
            f:/proc/stat -> n:^cpu\s+(\d+)\s+.*$ compare >= 2000000 && n:^cpu\s+(\d+)\s+.*$ compare <= 3000000;
            f:/proc/stat -> !n:^cpu\s+(\d+)\s+.*$ compare < 2000000 && !n:^cpu\s+(\d+)\s+.*$ compare > 3000000;
        ");

        $this->assertTrue(OssecRulesParser::evaluate($ctx, $rule));

        $rule = OssecRulesParser::parse("
            [CPU user time is above 2000000 and less than 3000000] [none] []
            f:/proc/stat -> n:^cpu\s+(\d+)\s+.*$ compare < 2000000;
            f:/proc/stat -> n:^cpu\s+(\d+)\s+.*$ compare > 3000000;
        ");

        $this->assertTrue(OssecRulesParser::evaluate($ctx, $rule));
    }
}
