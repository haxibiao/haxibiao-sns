<?php
/**
 * 广播专用的Redis（来自docker-laravel-echo-serve暴露的微服务）
 */
return [
	'broadcast' => [
		'url'      => env('ECHO_REDIS_URL'),
		'host'     => env('ECHO_REDIS_HOST', 'socket.haxibiao.cn'),
		'password' => env('ECHO_REDIS_PASSWORD', "echoserver"),
		'port'     => env('ECHO_REDIS_PORT', '6479'),
		'database' => env('ECHO_REDIS_DB', '0'),
	]
];
