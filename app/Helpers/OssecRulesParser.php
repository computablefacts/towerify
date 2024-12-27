<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OSSEC rule parser.
 *
 * [Application name] [any or all or none] [reference]
 * type:<entry name>;
 *
 * Type can be:
 *  - f (for file or directory)
 *  - p (process running)
 *  - r (registry running)
 *  - d (any file inside the directory)
 *  - c (for executing a command)
 *
 * Additional values:
 *  - For the registry and for directories, use "->" to look for a specific entry and another "->" to look for the value.
 *  - Also, use " -> r:^\. -> ..." to search all files in a directory.
 *  - For files, use "->" to look for a specific value in the file.
 *
 * Values can be preceded by:
 *  - =: (for equal) - default
 *  - r: (for ossec regexes)
 *  - >: (for strcmp greater)
 *  - <: (for strcmp  lower)
 *
 * Multiple patterns can be specified by using " && " between them. All of them must match for it to return true.
 */
class OssecRulesParser
{
    const string PROCESS_RULE = 'process';
    const string REGISTRY_RULE = 'registry';
    const string DIRECTORY_RULE = 'directory';
    const string FILE_OR_DIRECTORY_RULE = 'file_or_directory';
    const string COMMAND_RULE = 'command';

    public static function evaluate(array $ctx, array $rule): bool
    {
        $matchType = $rule['match_type'];
        foreach ($rule['rules'] as $r) {
            $isOk = match ($r['type']) {
                self::FILE_OR_DIRECTORY_RULE => self::evaluateFileOrDirectory($ctx, $r),
                self::DIRECTORY_RULE => self::evaluateFilesInDirectory($ctx, $r),
                default => false,
            };
            $isOk = $r['negate'] ? !$isOk : $isOk;
            if (($matchType === 'all' && !$isOk) || ($matchType === 'none' && $isOk)) {
                return false;
            }
            if ($matchType === 'any' && $isOk) {
                return true;
            }
        }
        return true;
    }

    public static function parse(string $text): array
    {
        $rules = [];
        $lines = array_map('trim', explode("\n", $text));
        $vars = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || Str::startsWith($line, "#")) {
                continue;
            }
            $matches = null;
            if (Str::startsWith($line, "$") && Str::endsWith($line, ";")) { // global variables
                $idx = Str::position($line, "=");
                $var = trim(Str::substr($line, 0, $idx));
                $files = array_map('trim', explode(',', Str::substr($line, $idx + 1, Str::length($line) - $idx - 2)));
                $vars[$var] = $files;
            } else if (preg_match('/\[(?<appname>.+)]\s*\[(?<matchtype>any|all|none)]\s*\[(?<references>.*)]/i', $line, $matches)) { // rule description
                $rules[] = [
                    'rule_name' => $matches['appname'],
                    'match_type' => $matches['matchtype'],
                    'references' => $matches['references'] ?
                        collect(explode(',', $matches['references']))
                            ->map(fn(string $ref) => trim($ref))
                            ->filter(fn(string $ref) => !empty($ref))
                            ->toArray() :
                        [],
                    'rules' => [],
                ];
            } else if (preg_match('/^(not\s+)*(?<type>[fpdrc]:)(?<rule>.+);$/i', $line, $matches)) { // rule patterns
                $rule = match ($matches['type']) {
                    'f:' => self::parseFileOrDirectory(trim($matches['rule']), $vars),
                    'p:' => self::parseRunningProcesses(trim($matches['rule']), $vars),
                    'r:' => self::parseRegistry(trim($matches['rule']), $vars),
                    'd:' => self::parseFilesInDirectory(trim($matches['rule']), $vars),
                    'c:' => self::parseCommandOutput(trim($matches['rule']), $vars),
                    default => null,
                };
                if (!empty($rules) && !empty($rule)) {
                    $rule['negate'] = Str::startsWith($line, "not ");
                    $rules[count($rules) - 1]['rules'][] = $rule;
                }
            }
        }
        if (count($rules) != 1) {
            throw new \Exception("Invalid number of rules: {$text}");
        }
        return $rules[0];
    }

    private static function evaluateFileOrDirectory(array $ctx, array $rule): bool
    {
        $results = [];
        foreach ($rule['files'] as $file) {
            if (!isset($rule['expr']) || count($rule['expr']) <= 0) {
                $results[] = $ctx['file_exists']($file);
            } else {
                $isOk = true;
                $lines = array_filter(array_map('trim', explode("\n", $ctx['file_get_contents']($file))), fn($line) => !empty($line));
                foreach ($lines as $line) {
                    foreach ($rule['expr'] as $expr) {
                        $matches = null;
                        $negate = Str::startsWith($expr, "!");
                        if ($negate) {
                            $expr = trim(Str::substr($expr, 1));
                        }
                        if (preg_match('/^n:(.*)\s+compare\s+([><=])+\s*(.*)$/i', $expr, $matches)) {
                            $op = trim($matches[2]);
                            $number = trim($matches[3]);
                            $matchez = null;
                            if (!preg_match("/{$matches[1]}/i", $line, $matchez)) {
                                $isOk = $negate;
                            } else if ($op === '>') {
                                $isOk = $negate ? $matchez[1] <= $number : $matchez[1] > $number;
                            } else if ($op === '<') {
                                $isOk = $negate ? $matchez[1] >= $number : $matchez[1] < $number;
                            } else if ($op === '=') {
                                $isOk = $negate ? $matchez[1] != $number : $matchez[1] = $number;
                            } else if ($op === '>=') {
                                $isOk = $negate ? $matchez[1] < $number : $matchez[1] >= $number;
                            } else if ($op === '<=') {
                                $isOk = $negate ? $matchez[1] > $number : $matchez[1] <= $number;
                            } else if ($op === '!=' || $op === '<>') {
                                $isOk = $negate ? $matchez[1] == $number : $matchez[1] != $number;
                            } else {
                                Log::error("Unknown operator in rule: ");
                                Log::error($rule);
                                $isOk = false;
                            }
                        } else if (preg_match('/^r:(.*)$/i', $expr, $matches)) {
                            if (preg_match("/{$matches[1]}/i", $line)) {
                                $isOk = !$negate;
                            } else {
                                $isOk = $negate;
                            }
                        } else {
                            Log::error("Unknown expression in rule: ");
                            Log::error($rule);
                            $isOk = false;
                        }
                        if (!$isOk) {
                            break;
                        }
                    }
                    if ($isOk) {
                        break;
                    }
                }
                $results[] = $isOk;
            }
        }
        return collect($results)->reduce(fn($carry, $item) => $carry && $item, true);
    }

    private static function parseFileOrDirectory(string $rule, array $vars): array
    {
        $matches = null;
        if (preg_match('/(.*)\s*->\s+(.*)/i', $rule, $matches)) {
            return [
                'type' => self::FILE_OR_DIRECTORY_RULE,
                'files' => $vars[trim($matches[1])] ?? [trim($matches[1])],
                'expr' => array_map('trim', explode(" && ", $matches[2])),
            ];
        }
        if (!Str::contains($rule, "->")) {
            return [
                'type' => self::FILE_OR_DIRECTORY_RULE,
                'files' => $vars[$rule] ?? [$rule],
                'expr' => null,
            ];
        }
        throw new \Exception("Invalid FILE_OR_DIRECTORY rule: {$rule}");
    }

    private static function evaluateFilesInDirectory(array $ctx, array $rule): bool
    {
        $results = [];
        foreach ($rule['directories'] as $directory) {
            if ((!isset($rule['expr']) || count($rule['expr']) <= 0) && empty($rule['files'])) {
                $results[] = $ctx['directory_exists']($directory);
            } else if (!isset($rule['expr']) || count($rule['expr']) <= 0) {
                $isOk = true;
                foreach ($ctx['scandir']($directory) as $file) {
                    $expr = $rule['files'];
                    $negate = Str::startsWith($expr, "!");
                    if ($negate) {
                        $expr = trim(Str::substr($expr, 1));
                    }
                    $matches = null;
                    if (preg_match("/^r:(.*)$/i", $expr, $matches)) {
                        if (preg_match("/{$matches[1]}/i", $file)) {
                            $isOk = !$negate;
                        } else {
                            $isOk = $negate;
                        }
                    } else {
                        Log::error("Unknown expression in rule: ");
                        Log::error($rule);
                        $isOk = false;
                    }
                    if ($isOk) {
                        break;
                    }
                }
                $results[] = $isOk;
            } else {
                $isOk = true;
                foreach ($ctx['scandir']($directory) as $file) {
                    $expr = $rule['files'];
                    $negate = Str::startsWith($expr, "!");
                    if ($negate) {
                        $expr = trim(Str::substr($expr, 1));
                    }
                    if (preg_match("/^r:(.*)$/i", $expr, $matches)) {
                        if (preg_match("/{$matches[1]}/i", $file)) {
                            $isOk = !$negate;
                        } else {
                            $isOk = $negate;
                        }
                        if ($isOk) {
                            $isOk = self::evaluateFileOrDirectory($ctx, [
                                'type' => self::FILE_OR_DIRECTORY_RULE,
                                'files' => [$file],
                                'expr' => $rule['expr'],
                            ]);
                        }
                    } else {
                        Log::error("Unknown expression in rule: ");
                        Log::error($rule);
                        $isOk = false;
                    }
                    if ($isOk) {
                        break;
                    }
                }
                $results[] = $isOk;
            }
        }
        return collect($results)->reduce(fn($carry, $item) => $carry && $item, true);
    }

    private static function parseFilesInDirectory(string $rule, array $vars): array
    {
        $matches = null;
        if (preg_match('/(.*)\s*->\s+(.*)\s*->\s+(.*)/i', $rule, $matches)) {
            return [
                'type' => self::DIRECTORY_RULE,
                'directories' => $vars[trim($matches[1])] ?? [trim($matches[1])],
                'files' => trim($matches[2]),
                'expr' => array_map('trim', explode(" && ", $matches[3])),
            ];
        }
        if (preg_match('/(.*)\s*->\s+(.*)/i', $rule, $matches)) {
            return [
                'type' => self::DIRECTORY_RULE,
                'directories' => $vars[trim($matches[1])] ?? [trim($matches[1])],
                'files' => trim($matches[2]),
                'expr' => null,
            ];
        }
        if (!Str::contains($rule, "->")) {
            return [
                'type' => self::DIRECTORY_RULE,
                'directories' => $vars[$rule] ?? [$rule],
                'files' => null,
                'expr' => null,
            ];
        }
        throw new \Exception("Invalid DIRECTORY rule: {$rule}");
    }

    private static function parseRunningProcesses(string $rule, array $vars): array
    {
        throw new \Exception("Invalid PROCESS rule: {$rule}");
    }

    private static function parseRegistry(string $rule, array $vars): array
    {
        $matches = null;
        if (preg_match('/(.*)\s*->\s+(.*)\s*->\s+(.*)/i', $rule, $matches)) {
            return [
                'type' => self::REGISTRY_RULE,
                'entry' => $vars[trim($matches[1])][0] ?? trim($matches[1]),
                'key' => trim($matches[2]),
                'value' => array_map('trim', explode(" && ", $matches[3])),
            ];
        }
        if (preg_match('/(.*)\s*->\s+(.*)/i', $rule, $matches)) {
            return [
                'type' => self::REGISTRY_RULE,
                'entry' => $vars[trim($matches[1])][0] ?? trim($matches[1]),
                'key' => trim($matches[2]),
                'value' => null,
            ];
        }
        if (!Str::contains($rule, "->")) {
            return [
                'type' => self::REGISTRY_RULE,
                'entry' => $vars[$rule][0] ?? [$rule],
                'key' => null,
                'value' => null,
            ];
        }
        throw new \Exception("Invalid REGISTRY rule: {$rule}");
    }

    private static function parseCommandOutput(string $rule, array $vars): array
    {
        $matches = null;
        if (preg_match('/(.*)\s*->\s+(.*)/i', $rule, $matches)) {
            return [
                'type' => self::COMMAND_RULE,
                'cmd' => $vars[trim($matches[1])][0] ?? trim($matches[1]),
                'expr' => array_map('trim', explode(" && ", $matches[2])),
            ];
        }
        if (!Str::contains($rule, "->")) {
            return [
                'type' => self::COMMAND_RULE,
                'cmd' => $vars[$rule][0] ?? $rule,
                'expr' => null,
            ];
        }
        throw new \Exception("Invalid COMMAND rule: {$rule}");
    }
}