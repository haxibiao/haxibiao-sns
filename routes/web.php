<?php

//用户的关注，粉丝，收藏，喜欢，足迹页面 web routes
//关注
Route::get('/follow', 'FollowController@index');
//消息
Route::get('/notification', 'NotificationController@index');
