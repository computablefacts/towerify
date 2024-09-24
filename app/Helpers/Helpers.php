<?php

if (!function_exists('tw_random_string')) {
    function tw_random_string($length): string
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
if (!function_exists('tw_hash')) {
    function tw_hash(string $value): string
    {
        $key = config('towerify.hasher.nonce');
        $initializationVector = tw_random_string(16);
        return $initializationVector . '_' . openssl_encrypt($value, 'AES-256-CBC', $key, 0, $initializationVector);
    }
}
if (!function_exists('tw_unhash')) {
    function tw_unhash(string $value): string
    {
        $key = config('towerify.hasher.nonce');
        $initializationVector = strtok($value, '_');
        $value2 = substr($value, strpos($value, '_') + 1);
        return openssl_decrypt($value2, 'AES-256-CBC', $key, 0, $initializationVector);
    }
}
if (!function_exists('bcrypt')) {
    // because the bcrypt driver is directly called by the AppShell package :-(
    // https://konekt.dev/appshell/3.x/README
    function bcrypt($value, $options = [])
    {
        return tw_hash($value);
    }
}
if (!function_exists('format_subscription_price')) {
    function format_subscription_price($price, bool $taxIncluded = false, string $currency = null)
    {
        return sprintf(
                config('vanilo.foundation.currency.format'),
                $price,
                $currency ?? config('vanilo.foundation.currency.sign')
            ) . ' / month' . ($taxIncluded ? ' (incl. taxes)' : ' (excl. taxes)');
    }
}

function app_url(): string
{
    return rtrim(config('app.url'), '/');
}
