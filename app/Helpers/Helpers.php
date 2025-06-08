<?php

if (!function_exists('format_bytes')) {
    function format_bytes($bytes, $precision = 2)
    {
        $units = array(' B', ' KiB', ' MiB', ' GiB', ' TiB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . $units[$pow];
    }
}
if (!function_exists('app_url')) {
    function app_url(): string
    {
        return rtrim(config('app.url'), '/');
    }
}
if (!function_exists('is_cywise')) {
    function is_cywise(): bool
    {
        return mb_strtolower(config('app.name')) === 'cywise';
    }
}
if (!function_exists('cywise_random_string')) {
    function cywise_random_string(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ&?!#';
        $lengthCharacters = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, $lengthCharacters - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}
if (!function_exists('cywise_hash')) {
    function cywise_hash(string $value): string
    {
        $key = config('towerify.hasher.nonce');
        $initializationVector = cywise_random_string(16);
        return $initializationVector . '_' . openssl_encrypt($value, 'AES-256-CBC', $key, 0, $initializationVector);
    }
}
if (!function_exists('cywise_unhash')) {
    function cywise_unhash(string $value): string
    {
        $key = config('towerify.hasher.nonce');
        $initializationVector = strtok($value, '_');
        $value2 = substr($value, strpos($value, '_') + 1);
        return openssl_decrypt($value2, 'AES-256-CBC', $key, 0, $initializationVector);
    }
}
if (!function_exists('app_config_override')) {
    function app_config_override(): array
    {
        $database = config('database.default');
        $config = config('database.connections.' . $database);

        try {
            if ($database == 'sqlite') {
                $dsn = "{$config['driver']}:{$config['database']}";
                $pdo = new PDO($dsn);
            } else {
                $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
                $pdo = new PDO($dsn, $config['username'], $config['password']);
            }

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $statement = $pdo->query("SELECT `key`, `value`, `is_encrypted` FROM app_config");
            $settings = $statement->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            foreach ($settings as $keyValuePair) {
                $key = $keyValuePair['key'];
                $value = $keyValuePair['value'];
                if ($keyValuePair['is_encrypted'] === 1) {
                    config([$key => cywise_unhash($value)]);
                } else {
                    config([$key => $value]);
                }
            }
            return ['loaded' => true];

        } catch (PDOException $e) {
            printf($e->getMessage());
            return ['loaded' => false];
        }
    }
}
