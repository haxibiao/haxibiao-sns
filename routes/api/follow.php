<?php

use Illuminate\Support\Facades\Route;

//关注
Route::middleware('auth:api')->post('/follow/{id}/{type}', 'FollowController@toggle');
Route::middleware('auth:api')->get('/follow/{id}/{type}', 'FollowController@touch');
Route::middleware('auth:api')->get('/follows', 'FollowController@follows');
Route::middleware('auth:api')->get('/follow/recommends', 'FollowController@recommends');
