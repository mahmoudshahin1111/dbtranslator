<?php

Route::get('/', 'LiteMs\Translator\TranslationController@index');
Route::post('/', 'LiteMs\Translator\TranslationController@store');
Route::get('/translation/{trans_id}/{lang}', 'LiteMs\Translator\TranslationController@show');
