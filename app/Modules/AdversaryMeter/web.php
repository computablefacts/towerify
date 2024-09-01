<?php

use Illuminate\Support\Facades\Route;

/** @deprecated */
Route::get('/user', function () {
    return \Illuminate\Support\Facades\Auth::user();
});