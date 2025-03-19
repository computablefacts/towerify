<?php

use Illuminate\Support\Facades\Route;

Route::post('/llm1', 'CyberBuddyController@llm1')->middleware('auth');

Route::post('/llm2', 'CyberBuddyController@llm2')->middleware('auth');

Route::get('/templates', 'CyberBuddyController@templates')->middleware('auth');

Route::post('/templates', 'CyberBuddyController@saveTemplate')->middleware('auth');

Route::delete('/templates/{id}', 'CyberBuddyController@deleteTemplate')->middleware('auth');

Route::get('/files', 'CyberBuddyController@files')->middleware('auth');

Route::delete('/files/{id}', 'CyberBuddyController@deleteFile')->middleware('auth');

Route::get('/files/stream/{secret}', 'CyberBuddyController@streamFile');

Route::get('/files/download/{secret}', 'CyberBuddyController@downloadFile');

Route::post('/files/one', 'CyberBuddyController@uploadOneFile')->middleware('auth:sanctum');

Route::post('/files/many', 'CyberBuddyController@uploadManyFiles')->middleware('auth:sanctum');

Route::get('/collections', 'CyberBuddyController@collections')->middleware('auth');

Route::delete('/collections/{id}', 'CyberBuddyController@deleteCollection')->middleware('auth');

Route::post('/collections/{id}', 'CyberBuddyController@saveCollection')->middleware('auth');

Route::delete('/chunks/{id}', 'CyberBuddyController@deleteChunk')->middleware('auth');

Route::post('/chunks/{id}', 'CyberBuddyController@saveChunk')->middleware('auth');

Route::delete('/prompts/{id}', 'CyberBuddyController@deletePrompt')->middleware('auth');

Route::post('/prompts/{id}', 'CyberBuddyController@savePrompt')->middleware('auth');

Route::delete('/conversations/{id}', 'CyberBuddyController@deleteConversation')->middleware('auth');

Route::delete('/frameworks/{id}', 'CyberBuddyController@unloadFramework')->middleware('auth');

Route::post('/frameworks/{id}', 'CyberBuddyController@loadFramework')->middleware('auth');

Route::get('/cyber-buddy', 'CyberBuddyController@showPage')->middleware('auth');

Route::get('/cyber-buddy/chat', 'CyberBuddyController@showChat');

Route::match(['get', 'post'], 'botman', 'CyberBuddyController@handle');

Route::group([
    'prefix' => 'tables',
], function () {
    Route::get('/', 'CyberBuddyController@listTables')->name('list-tables');
    Route::post('/columns', 'CyberBuddyController@listTablesColumns')->name('list-tables-columns');
    Route::post('/import', 'CyberBuddyController@importTables')->name('import-tables');
    Route::get('/available', 'CyberBuddyController@availableTables')->name('available-tables');
    Route::post('/query', 'CyberBuddyController@queryTables')->name('query-tables');
    Route::post('/prompt-to-query', 'CyberBuddyController@promptToTablesQuery')->name('prompt-to-tables-query');
})->middleware(['auth']);

Route::group([
    'prefix' => 'assistant',
], function () {
    Route::get('/', 'CyberBuddyNextGenController@showAssistant');
    Route::post('/converse', 'CyberBuddyNextGenController@converse');
})->middleware(['auth']);
