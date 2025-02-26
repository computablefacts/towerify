<?php

namespace App\Helpers;

use App\Models\YnhOssecCheck;
use Illuminate\Support\Collection;

class OssecCheckScript
{
    const bool WINDOWS_IS_SUPPORTED = true;
    const bool DEBIAN_IS_SUPPORTED = true;
    const bool UBUNTU_IS_SUPPORTED = true;
    const bool CENTOS_IS_SUPPORTED = false;

    const string OS_WINDOWS = 'windows';
    const string OS_DEBIAN = 'debian';
    const string OS_UBUNTU = 'ubuntu';
    const string OS_CENTOS = 'centos';

    public static function hasScript(YnhOssecCheck|Collection $check, string $os = null): bool
    {
        if ($check instanceof YnhOssecCheck) {
            return self::hasScriptCheck($check, $os);
        }
        return self::hasScriptCollection($check, $os);
    }

    private static function hasScriptCheck(YnhOssecCheck $check, string $os = null): bool
    {
        if (is_null($os)) {
            return $check->policy->isWindows() && OssecCheckScript::WINDOWS_IS_SUPPORTED
                || $check->policy->isDebian() && OssecCheckScript::DEBIAN_IS_SUPPORTED
                || $check->policy->isUbuntu() && OssecCheckScript::UBUNTU_IS_SUPPORTED
                || $check->policy->isCentOs() && OssecCheckScript::CENTOS_IS_SUPPORTED;
        }
        return match ($os) {
            self::OS_WINDOWS => $check->policy->isWindows() && OssecCheckScript::WINDOWS_IS_SUPPORTED,
            self::OS_DEBIAN => $check->policy->isDebian() && OssecCheckScript::DEBIAN_IS_SUPPORTED,
            self::OS_UBUNTU => $check->policy->isUbuntu() && OssecCheckScript::UBUNTU_IS_SUPPORTED,
            self::OS_CENTOS => $check->policy->isCentOs() && OssecCheckScript::CENTOS_IS_SUPPORTED,
            default => false,
        };
    }

    private static function hasScriptCollection(Collection $checks, string $os = null): bool
    {
        return $checks->reduce(function (bool $result, YnhOssecCheck $check) use ($os) {
            return $result || self::hasScriptCheck($check, $os);
        }, false);
    }

    public static function generateScript(YnhOssecCheck|Collection $check, string $os): string
    {
        if ($check instanceof YnhOssecCheck) {
            return self::generateScriptCheck($check, $os);
        }
        return self::generateScriptCollection($check, $os);
    }

    public static function generateScriptCheck(YnhOssecCheck $check, string $os): string
    {
        $ruleJson = $check->requirementsWithCywiseLinkJson();
        return match ($os) {
            self::OS_WINDOWS => self::wrapWindowsScript($ruleJson),
            self::OS_DEBIAN => self::wrapDebianScript($ruleJson),
            self::OS_UBUNTU => self::wrapUbuntuScript($ruleJson),
            default => throw new \Exception('Invalid OS'),
        };
    }

    public static function generateScriptCollection(Collection $checks, string $os): string
    {
        $rulesJson = $checks->map(fn(YnhOssecCheck $check) => $check->requirementsWithCywiseLinkJson())->join("\n");
        return match ($os) {
            self::OS_WINDOWS => self::wrapWindowsScript($rulesJson),
            self::OS_DEBIAN => self::wrapDebianScript($rulesJson),
            self::OS_UBUNTU => self::wrapUbuntuScript($rulesJson),
            default => throw new \Exception('Invalid OS'),
        };
    }

    public static function scriptName(YnhOssecCheck|Collection $check, string $os): string
    {
        if ($check instanceof YnhOssecCheck) {
            return match ($os) {
                self::OS_WINDOWS => "Test-OssecRule-$check->uid.ps1",
                self::OS_DEBIAN => "debian-test-ossec-rule-$check->uid.sh",
                self::OS_UBUNTU => "ubuntu-test-ossec-rule-$check->uid.sh",
                self::OS_CENTOS => "centos-test-ossec-rule-$check->uid.sh",
                default => throw new \Exception('Invalid OS'),
            };
        }
        return match ($os) {
            self::OS_WINDOWS => "Test-OssecRules.ps1",
            self::OS_DEBIAN => "debian-test-ossec-rules.sh",
            self::OS_UBUNTU => "ubuntu-test-ossec-rules.sh",
            self::OS_CENTOS => "centos-test-ossec-rules.sh",
            default => throw new \Exception('Invalid OS'),
        };
    }

    private static function wrapWindowsScript(string $ruleJson): string
    {
        return self::windowsScriptStart() . "\n" . $ruleJson . "\n" . self::windowsScriptEnd();
    }

    private static function wrapDebianScript(string $ruleJson): string
    {
        return self::debianScriptStart() . "\n" . self::wrapWindowsScript($ruleJson) . "\n" . self::debianScriptEnd();
    }

    private static function wrapUbuntuScript(string $ruleJson): string
    {
        return self::ubuntuScriptStart() . "\n" . self::wrapWindowsScript($ruleJson) . "\n" . self::ubuntuScriptEnd();
    }

    private static function windowsScriptStart(): string
    {
        $scriptParts = explode('__PUT_RULES_HERE__', file_get_contents(resource_path('ossec/powershell/Test-OssecRules.ps1')));
        return $scriptParts[0];
    }

    private static function windowsScriptEnd(): string
    {
        $scriptParts = explode('__PUT_RULES_HERE__', file_get_contents(resource_path('ossec/powershell/Test-OssecRules.ps1')));
        return $scriptParts[1];
    }

    private static function debianScriptStart(): string
    {
        $scriptParts = explode('__PUT_POWERSHELL_HERE__', file_get_contents(resource_path('ossec/debian/debian-test-ossec-rules.sh')));
        return $scriptParts[0];
    }

    private static function debianScriptEnd(): string
    {
        $scriptParts = explode('__PUT_POWERSHELL_HERE__', file_get_contents(resource_path('ossec/debian/debian-test-ossec-rules.sh')));
        return $scriptParts[1];
    }

    private static function ubuntuScriptStart(): string
    {
        $scriptParts = explode('__PUT_POWERSHELL_HERE__', file_get_contents(resource_path('ossec/ubuntu/ubuntu-test-ossec-rules.sh')));
        return $scriptParts[0];
    }

    private static function ubuntuScriptEnd(): string
    {
        $scriptParts = explode('__PUT_POWERSHELL_HERE__', file_get_contents(resource_path('ossec/ubuntu/ubuntu-test-ossec-rules.sh')));
        return $scriptParts[1];
    }
}
