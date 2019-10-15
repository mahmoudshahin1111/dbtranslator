# DBTranslator (Laravel+JQUERY+DataTables+Bootstrap 4.3)

now you can translate all tables as you wants.
```php
Route::get('/', 'LiteMs\Translator\TranslationController@index');
Route::post('/', 'LiteMs\Translator\TranslationController@store');
Route::get('/translation/{trans_id}/{lang}', 'LiteMs\Translator\TranslationController@show');
```
*Routes is 
-Main page is  '/'
-You Mast Has Language Model or change Config for Language Model Like That 
```php
<?php
return [
.....
    'language_model'=>App\Language::class,
.......
];
```
-make migrate to create translation table
  -that table has table will translate and columns you will translate
  
you can see example on my blog 
https://mahmoudshahin1111.blogspot.com/
