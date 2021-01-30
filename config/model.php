<?php

/**
 * 使用场景:Model
 */

function definedMorphModelType()
{
    return [
        'users'        => 'Haxibiao\Breeze\User',
        'feedbacks'    => 'Haxibiao\Sns\Feedback',
        'comments'     => 'Haxibiao\Sns\Comment',
        'questions'    => 'Haxibiao\Question\Question',
        'audit'        => 'Haxibiao\Question\Audit',
        'explanations' => 'Haxibiao\Question\Explanation',
        'videos'       => 'Haxibiao\Media\Video',
        'posts'        => 'Haxibiao\Media\Post',
        'categories'   => 'Haxibiao\Content\Category',
        'collections'  => 'Haxibiao\Content\Collection',
    ];
}

function get_model($alias)
{
    return definedMorphModelType()[$alias];
}
