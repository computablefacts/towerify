<?php

use Illuminate\Support\Facades\Route;

Route::post('/summarize', 'TheCyberBriefController@summarize')->name('summarize');
