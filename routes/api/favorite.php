<?php

use Illuminate\Support\Facades\Route;

//收藏
Route::middleware('auth:api')->post('/favorite/{id}/{type}', 'FavoriteController@toggle');
Route::middleware('auth:api')->get('/favorite/{id}/{type}', 'FavoriteController@get');
