<?php

use Illuminate\Support\Facades\Route;

Route::get('/files', 'CyberBuddyController@files')->middleware('auth');

Route::get('/files/stream/{secret}', 'CyberBuddyController@streamFile');

Route::get('/files/download/{secret}', 'CyberBuddyController@downloadFile');

Route::post('/files/one', 'CyberBuddyController@uploadOneFile')->middleware('auth:sanctum');

Route::post('/files/many', 'CyberBuddyController@uploadManyFiles')->middleware('auth:sanctum');

Route::get('/collections', 'CyberBuddyController@collections')->middleware('auth');

Route::delete('/chunks/{id}', 'CyberBuddyController@deleteChunk')->middleware('auth');

Route::delete('/prompts/{id}', 'CyberBuddyController@deletePrompt')->middleware('auth');

Route::get('/cyber-buddy', 'CyberBuddyController@showPage')->middleware('auth');

Route::get('/cyber-buddy/chat', 'CyberBuddyController@showChat');

Route::match(['get', 'post'], 'botman', 'CyberBuddyController@handle');