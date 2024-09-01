<?php

use Illuminate\Support\Facades\Route;

/** @deprecated */
Route::middleware('auth:api')->get('/user', function () {
    return \Illuminate\Support\Facades\Auth::user();
});