<?php

use App\Modules\AdversaryMeter\Mail\AuditReport;
use Illuminate\Support\Facades\Route;

Route::get('/cyber-todo/{hash}', 'CyberTodoController@show');

Route::get('/audit-report', fn() => AuditReport::create()['report'])->middleware('auth');