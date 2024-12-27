<?php

namespace App\Helpers;

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
    const string PROCESS = 'process';
    const string REGISTRY = 'registry';
    const string DIRECTORY = 'directory';
    const string FILE_OR_DIRECTORY = 'file_or_directory';
    const string EQUALS_TO = 'equals_to';
    const string GREATER_THAN = 'greater_than';
    const string LESS_THAN = 'less_than';
    const string REGEX = 'regex';
    const string COMMAND = 'command';

    public function parse(string $text): array
    {
        $rules = [];
        $lines = array_map('trim', explode("\n", $text));
        $vars = [];

        foreach ($lines as $line) {

            $line = trim($line);

            if (empty($line) || Str::startsWith($line, "#")) {
                continue;
            }
            if (Str::startsWith($line, "$") && Str::endsWith($line, ";")) {
                $idx = Str::position($line, "=");
                $var = trim(Str::substr($line, 0, $idx));
                $files = array_map('trim', explode(',', Str::substr($line, $idx + 1, Str::length($line) - $idx - 2)));
                $vars[$var] = $files;
            } else if (preg_match('/\[(?<appname>.+)]\s*\[(?<matchtype>any|all|none)]\s*\[(?<references>.*)]/i', $line, $matches)) {
                $rules[] = [
                    'application_name' => $matches['appname'],
                    'match_type' => $matches['matchtype'],
                    'references' => $matches['references'] ? explode(',', $matches['references']) : [],
                    'rules' => [],
                ];
            } else if (preg_match('/(?<type>[fpdrc]:)(?<rule>.+);/i', $line, $matches)) {
                $rule = match ($matches['type']) {
                    'f:' => $this->parseFileOrDirectory($matches['rule'], $vars),
                    'p:' => $this->parseRunningProcesses($matches['rule'], $vars),
                    'r:' => $this->parseRegistry($matches['rule'], $vars),
                    'd:' => $this->parseFilesInDirectory($matches['rule'], $vars),
                    'c:' => $this->parseCommandOutput($matches['rule'], $vars),
                    default => null,
                };
                if (!empty($rules) && !empty($rule)) {
                    $rules[count($rules) - 1]['rules'][] = $rule;
                }
            }
        }
        return $rules;
    }

    private function parseFileOrDirectory(string $rule, array $vars): array
    {
        $rule = trim($rule);
        $negate = Str::startsWith($rule, "!");
        if ($negate) {
            $rule = trim(Str::substr($rule, 1));
        }
        $parts = array_map('trim', explode("->", $rule));
        if (count($parts) === 1) {
            return [
                'type' => self::FILE_OR_DIRECTORY,
                'negate' => $negate,
                'files' => $vars[$parts[0]] ?? [$parts[0]],
                'checks' => []
            ];
        }
        if (count($parts) !== 2) {
            throw new \Exception("Invalid rule: {$rule}");
        }
        return [
            'type' => self::FILE_OR_DIRECTORY,
            'negate' => $negate,
            'files' => $vars[$parts[0]] ?? [$parts[0]],
            'checks' => $this->parseExpression($parts[1]),
        ];
    }

    private function parseFilesInDirectory(string $rule, array $vars): array
    {
        $rule = trim($rule);
        $negate = Str::startsWith($rule, "!");
        if ($negate) {
            $rule = trim(Str::substr($rule, 1));
        }
        $parts = array_map('trim', explode("->", $rule));
        if (count($parts) === 1) {
            return [
                'type' => self::DIRECTORY,
                'negate' => $negate,
                'directories' => $vars[$parts[0]] ?? [$parts[0]],
                'files_pattern' => null,
                'checks' => [],
            ];
        }
        if (count($parts) === 2) {
            return [
                'type' => self::DIRECTORY,
                'negate' => $negate,
                'directories' => $vars[$parts[0]] ?? [$parts[0]],
                'files_pattern' => $parts[1],
                'checks' => [],
            ];
        }
        if (count($parts) !== 3) {
            throw new \Exception("Invalid rule: {$rule}");
        }
        return [
            'type' => self::DIRECTORY,
            'negate' => $negate,
            'directories' => $vars[$parts[0]] ?? [$parts[0]],
            'files_pattern' => $parts[1],
            'checks' => $this->parseExpression($parts[2]),
        ];
    }

    private function parseRunningProcesses(string $rule, array $vars): ?array
    {
        $rule = trim($rule);
        $negate = Str::startsWith($rule, "!");
        if ($negate) {
            $rule = trim(Str::substr($rule, 1));
        }
        $parts = array_map('trim', explode("->", $rule));
        if (count($parts) === 1) {
            return [
                'type' => self::PROCESS,
                'negate' => $negate,
                'processes' => $vars[$parts[0]] ?? [$parts[0]],
                'checks' => [],
            ];
        }
        // TODO
        return null;
    }

    private function parseRegistry(string $rule, array $vars): array
    {
        $rule = trim($rule);
        $negate = Str::startsWith($rule, "!");
        if ($negate) {
            $rule = trim(Str::substr($rule, 1));
        }
        $parts = array_map('trim', explode("->", $rule));
        if (count($parts) === 1) {
            return [
                'type' => self::REGISTRY,
                'negate' => $negate,
                'registries' => $vars[$parts[0]] ?? [$parts[0]],
                'key_checks' => [],
                'value_checks' => [],
            ];
        }
        if (count($parts) === 2) {
            return [
                'type' => self::REGISTRY,
                'negate' => $negate,
                'registries' => $vars[$parts[0]] ?? [$parts[0]],
                'key_checks' => $this->parseExpression($parts[1]),
                'value_checks' => [],
            ];
        }
        if (count($parts) !== 3) {
            throw new \Exception("Invalid rule: {$rule}");
        }
        return [
            'type' => self::REGISTRY,
            'negate' => $negate,
            'registries' => $vars[$parts[0]] ?? [$parts[0]],
            'key_checks' => $this->parseExpression($parts[1]),
            'value_checks' => $this->parseExpression($parts[2]),
        ];
    }

    private function parseCommandOutput(string $rule, array $vars): array
    {
        $rule = trim($rule);
        $negate = Str::startsWith($rule, "!");
        if ($negate) {
            $rule = trim(Str::substr($rule, 1));
        }
        $parts = array_map('trim', explode("->", $rule));
        if (count($parts) != 2) {
            throw new \Exception("Invalid rule: {$rule}");
        }
        return [
            'type' => self::COMMAND,
            'negate' => $negate,
            'command' => $vars[$parts[0]] ?? $parts[0],
            'checks' => $this->parseExpression($parts[1]),
        ];
    }

    private function parseExpression(string $str): array
    {
        $expression = [];
        $parts = array_map('trim', explode(" && ", $str));
        foreach ($parts as $part) {
            $type = self::EQUALS_TO;
            $part = trim($part);
            $negate = Str::startsWith($part, "!");
            if ($negate) {
                $part = trim(Str::substr($part, 1));
            }
            if (Str::startsWith($part, "r:")) {
                $type = self::REGEX;
                $part = trim(Str::substr($part, 2));
            } else if (Str::startsWith($part, ">:")) {
                $type = self::GREATER_THAN;
                $part = trim(Str::substr($part, 2));
            } else if (Str::startsWith($part, "<:")) {
                $type = self::LESS_THAN;
                $part = trim(Str::substr($part, 2));
            } else if (Str::startsWith($part, "=:")) {
                $type = self::EQUALS_TO;
                $part = trim(Str::substr($part, 2));
            }
            $expression[] = [
                'type' => $type,
                'negate' => $negate,
                'expression' => $part,
            ];
        }
        return $expression;
    }
}