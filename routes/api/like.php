<?php

use Illuminate\Support\Facades\Route;

//like赞
Route::middleware('auth:api')->post('/like/{id}/{type}', 'LikeController@toggle');
Route::middleware('auth:api')->get('/like/{id}/{type}', 'LikeController@get');
Route::get('/like/{id}/{type}/guest', 'LikeController@getForGuest');
