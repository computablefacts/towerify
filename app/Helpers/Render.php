<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Blade;

class Render
{
    public static function stringToBlade(string $string, array $data = [])
    {
        if (!$data) {
            $data = [];
        }

        $data['__env'] = app(\Illuminate\View\Factory::class);
        $php = Blade::compileString($string);
        $obLevel = ob_get_level();

        ob_start();
        extract($data, EXTR_SKIP);

        try {
            eval('?' . '>' . $php);
        } catch (\Exception|\Throwable $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            throw $e;
        }
        return ob_get_clean();
    }
}