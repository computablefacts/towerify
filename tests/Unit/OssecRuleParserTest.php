<?php

namespace Tests\Unit;

use App\Helpers\OssecRulesParser;
use Tests\TestCase;

class OssecRuleParserTest extends TestCase
{
    public function testParseLinuxRule()
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

        $this->assertEquals([[
            'application_name' => 'CIS - Apache Configuration - 2.3: WebDAV Modules are enabled',
            'match_type' => 'any',
            'references' => ['https://workbench.cisecurity.org/benchmarks/307', 'https://workbench.cisecurity.org/benchmarks/308'],
            'rules' => [[
                'type' => OssecRulesParser::DIRECTORY,
                'negate' => false,
                'directories' => ['/etc/apache2/conf-enabled', '/etc/apache2/mods-enabled', '/etc/apache2/sites-enabled', '/etc/httpd/conf.d', '/etc/httpd/modsecurity.d'],
                'files_pattern' => 'conf',
                'checks' => [[
                    'type' => OssecRulesParser::REGEX,
                    'negate' => true,
                    'expression' => '^#',
                ], [
                    'type' => OssecRulesParser::REGEX,
                    'negate' => false,
                    'expression' => 'loadmodule\sdav',
                ]],
            ], [
                'type' => OssecRulesParser::DIRECTORY,
                'negate' => false,
                'directories' => ['/etc/apache2/conf-enabled', '/etc/apache2/mods-enabled', '/etc/apache2/sites-enabled', '/etc/httpd/conf.d', '/etc/httpd/modsecurity.d'],
                'files_pattern' => 'load',
                'checks' => [[
                    'type' => OssecRulesParser::REGEX,
                    'negate' => true,
                    'expression' => '^#',
                ], [
                    'type' => OssecRulesParser::REGEX,
                    'negate' => false,
                    'expression' => 'loadmodule\sdav',
                ]],
            ], [
                'type' => OssecRulesParser::FILE_OR_DIRECTORY,
                'negate' => false,
                'files' => ['/etc/httpd/conf.d'],
                'checks' => [[
                    'type' => OssecRulesParser::REGEX,
                    'negate' => true,
                    'expression' => '^#',
                ], [
                    'type' => OssecRulesParser::REGEX,
                    'negate' => false,
                    'expression' => 'loadmodule\sdav',
                ]],
            ], [
                'type' => OssecRulesParser::DIRECTORY,
                'negate' => false,
                'directories' => ['/etc/apache2/mods-enabled'],
                'files_pattern' => 'dav.load',
                'checks' => [],
            ]],
        ]], $rule);
    }

    public function testParseWindowsRule()
    {
        $rule = OssecRulesParser::parse("
            [CIS - Microsoft Windows Server 2012 R2 - 18.3.5: Ensure 'MSS: (KeepAliveTime) How often keep-alive packets are sent in milliseconds' is set to 'Enabled: 300,000 or 5 minutes'] [any] [https://workbench.cisecurity.org/benchmarks/288]
            r:HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters -> KeepAliveTime -> !493e0;
            r:HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters -> !KeepAliveTime;
        ");

        $this->assertEquals([[
            'application_name' => 'CIS - Microsoft Windows Server 2012 R2 - 18.3.5: Ensure \'MSS: (KeepAliveTime) How often keep-alive packets are sent in milliseconds\' is set to \'Enabled: 300,000 or 5 minutes\'',
            'match_type' => 'any',
            'references' => ['https://workbench.cisecurity.org/benchmarks/288'],
            'rules' => [[
                'type' => OssecRulesParser::REGISTRY,
                'negate' => false,
                'registries' => ['HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters'],
                'key_checks' => [[
                    'type' => OssecRulesParser::EQUALS_TO,
                    'negate' => false,
                    'expression' => 'KeepAliveTime',
                ]],
                'value_checks' => [[
                    'type' => OssecRulesParser::EQUALS_TO,
                    'negate' => true,
                    'expression' => '493e0',
                ]],
            ], [
                'type' => OssecRulesParser::REGISTRY,
                'negate' => false,
                'registries' => ['HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters'],
                'key_checks' => [[
                    'type' => OssecRulesParser::EQUALS_TO,
                    'negate' => true,
                    'expression' => 'KeepAliveTime',
                ]],
                'value_checks' => [],
            ]]
        ]], $rule);
    }

    public function testParseSimpleCommandRule()
    {
        $rule = OssecRulesParser::parse("
            [Ensure rsync service is either not installed or masked] [any] []
            c:dpkg-query -W -f='\${binary:Package}\\t\${Status}\\t\${db:Status-Status}\\n' rsync -> r:unknown ok not-installed|dpkg-query: no packages found matching rsync;
            c:systemctl is-active rsync -> r:^inactive;
            c:systemctl is-enabled rsync -> r:^masked;
        ");

        $this->assertEquals([[
            'application_name' => 'Ensure rsync service is either not installed or masked',
            'match_type' => 'any',
            'references' => [],
            'rules' => [[
                'type' => OssecRulesParser::COMMAND,
                'negate' => false,
                'command' => 'dpkg-query -W -f=\'${binary:Package}\\t${Status}\\t${db:Status-Status}\\n\' rsync',
                'checks' => [[
                    'type' => OssecRulesParser::REGEX,
                    'negate' => false,
                    'expression' => 'unknown ok not-installed|dpkg-query: no packages found matching rsync',
                ]],
            ], [
                'type' => OssecRulesParser::COMMAND,
                'negate' => false,
                'command' => 'systemctl is-active rsync',
                'checks' => [[
                    'type' => OssecRulesParser::REGEX,
                    'negate' => false,
                    'expression' => '^inactive',
                ]],
            ], [
                'type' => OssecRulesParser::COMMAND,
                'negate' => false,
                'command' => 'systemctl is-enabled rsync',
                'checks' => [[
                    'type' => OssecRulesParser::REGEX,
                    'negate' => false,
                    'expression' => '^masked',
                ]],
            ]]
        ]], $rule);
    }

    public function testParseComplexCommandRule()
    {
        $rule = OssecRulesParser::parse("
            [Ensure only strong Ciphers are used] [none] [https://nvd.nist.gov/vuln/detail/CVE-2016-2183,https://www.openssh.com/txt/cbc.adv,https://nvd.nist.gov/vuln/detail/CVE-2008-5161]
            c:sshd -T -> r:^ciphers && r:3des-cbc|aes128-cbc|aes192-cbc|aes256-cbc;
        ");

        $this->assertEquals([[
            'application_name' => 'Ensure only strong Ciphers are used',
            'match_type' => 'none',
            'references' => ['https://nvd.nist.gov/vuln/detail/CVE-2016-2183', 'https://www.openssh.com/txt/cbc.adv', 'https://nvd.nist.gov/vuln/detail/CVE-2008-5161'],
            'rules' => [[
                'type' => OssecRulesParser::COMMAND,
                'negate' => false,
                'command' => 'sshd -T',
                'checks' => [[
                    'type' => OssecRulesParser::REGEX,
                    'negate' => false,
                    'expression' => '^ciphers',
                ], [
                    'type' => OssecRulesParser::REGEX,
                    'negate' => false,
                    'expression' => '3des-cbc|aes128-cbc|aes192-cbc|aes256-cbc',
                ]],
            ]]
        ]], $rule);
    }
}
