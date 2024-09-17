<?php

use Illuminate\Support\Facades\Route;

Route::get('/cyber-buddy', 'CyberBuddyController@showPage');

Route::get('/cyber-buddy/chat', 'CyberBuddyController@showChat');

Route::match(['get', 'post'], 'botman', 'CyberBuddyController@handle');