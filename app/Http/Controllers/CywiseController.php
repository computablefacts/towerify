<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CywiseController extends Controller
{
    public function __construct()
    {
        //
    }

    public function show(string $hash, Request $request)
    {
        $step = $request->get('step');
        return view('cywise', [
            'hash' => $hash,
            'step' => $step ?? 1,
        ]);
    }
}
