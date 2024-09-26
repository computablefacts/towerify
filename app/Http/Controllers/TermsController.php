<?php

namespace App\Http\Controllers;

use Parsedown;

class TermsController extends Controller
{
    /**
     * Show the terms of service for the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $file = file_exists(public_path('/markdown/terms.' . app()->getLocale() . '.md'))
            ? public_path('/markdown/terms.' . app()->getLocale() . '.md')
            : public_path('/markdown/terms.md');

        return view('markdown', [
            'terms' => (new Parsedown)->text(file_get_contents($file))
        ]);
    }
}
