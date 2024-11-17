<?php

use Illuminate\Support\Facades\Route;

Route::delete('/files/{id}', 'FederaController@deleteFile')->middleware('auth');

Route::get('/files/stream/{secret}', 'FederaController@streamFile');

Route::get('/files/download/{secret}', 'FederaController@downloadFile');

Route::post('/files/one', 'FederaController@uploadOneFile')->middleware('auth:sanctum');

Route::post('/files/many', 'FederaController@uploadManyFiles')->middleware('auth:sanctum');

Route::get('/collections', 'FederaController@collections')->middleware('auth');

Route::delete('/collections/{id}', 'FederaController@deleteCollection')->middleware('auth');
