<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Notice;

trait NoticeRepo
{
    public static function addNotice(array $data)
    {
        $title      = \data_get($data, 'title');
        $content    = \data_get($data, 'content');
        $to_user_id = \data_get($data, 'to_user_id');
        $type       = \data_get($data, 'type');
        return Notice::create(
            [
                'title'      => $title,
                'content'    => $content,
                'to_user_id' => $to_user_id,
                'user_id'    => 1,
                'type'       => $type,
            ]
        );}
}
