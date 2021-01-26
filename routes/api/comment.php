<?php

use Illuminate\Support\Facades\Route;

//评论

//发表新评论，回复评论，回复评论中的评论
Route::middleware('auth:api')->post('/comment', 'CommentController@save');

// 点赞
Route::middleware('auth:api')->get('/comment/{id}/like', 'CommentController@like');
// 举报
Route::middleware('auth:api')->get('/comment/{id}/report', 'CommentController@report');

// 未登录查看评论列表
Route::get('/comment/{id}/{type}', 'CommentController@get');

// 已登录查看评论列表，能获取是否已点赞，已举报状态
Route::middleware('auth:api')->get('/comment/{id}/{type}/with-token', 'CommentController@getWithToken');
