<?php

use Illuminate\Support\Facades\Route;

//消息
Route::middleware('auth:api')->get('notification/chats', 'NotificationController@chats');
Route::middleware('auth:api')->get('notification/chat/{id}', 'NotificationController@chat');
Route::middleware('auth:api')->post('notification/chat/{id}/send', 'NotificationController@sendMessage');
Route::middleware('auth:api')->get('notifications/{type}', 'NotificationController@notifications');
//未读消息
Route::middleware('auth:api')->get('/unreads', 'UserController@unreads');
