<?php

use Illuminate\Support\Facades\Route;

Route::get('/cyber-todo/{hash}', 'CyberTodoController@show');