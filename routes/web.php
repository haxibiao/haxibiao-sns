<?php

use Illuminate\Support\Facades\Route;

//关注
Route::get('/follow', 'FollowController@index');
//消息
Route::get('/notification', 'NotificationController@index');
//聊天
Route::get('/chat/with/{uid}', 'ChatController@chat');
