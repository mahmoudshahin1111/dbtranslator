<?php

Route::get('/', 'LiteMs\Translator\TranslationController@index');
Route::post('/', 'TranslationController@store');

Route::get('/translation/{trans_id}/{lang}', 'LiteMs\Translator\TranslationController@show');
