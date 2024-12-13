<?php

namespace Tests\Unit;

use App\Helpers\OssecRulesParser;
use Tests\TestCase;

class OssecRuleParserTest extends TestCase
{
    public function testParseLinuxRule()
    {
        $parser = new OssecRulesParser();
        $rule = $parser->parse("
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
            'reference' => 'https://workbench.cisecurity.org/benchmarks/307, https://workbench.cisecurity.org/benchmarks/308',
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
        $parser = new OssecRulesParser();
        $rule = $parser->parse("
            [CIS - Microsoft Windows Server 2012 R2 - 18.3.5: Ensure 'MSS: (KeepAliveTime) How often keep-alive packets are sent in milliseconds' is set to 'Enabled: 300,000 or 5 minutes'] [any] [https://workbench.cisecurity.org/benchmarks/288]
            r:HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters -> KeepAliveTime -> !493e0;
            r:HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Tcpip\Parameters -> !KeepAliveTime;
        ");

        $this->assertEquals([[
            'application_name' => 'CIS - Microsoft Windows Server 2012 R2 - 18.3.5: Ensure \'MSS: (KeepAliveTime) How often keep-alive packets are sent in milliseconds\' is set to \'Enabled: 300,000 or 5 minutes\'',
            'match_type' => 'any',
            'reference' => 'https://workbench.cisecurity.org/benchmarks/288',
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
}
