<?php

declare (strict_types = 1);

return [
    /**
     * user的关注列表为followers
     * user为主动方
     */
    'active_follow'  => [
        'App\User'  => 'followers',
    ],

    /**
     * user的粉丝列表为followers
     * user为被动方
     */
    'passive_follow' => [
        'App\User'       => 'followers',
        'App\Category'   => 'follows',
        'App\Collection' => 'follows',
    ],

];
