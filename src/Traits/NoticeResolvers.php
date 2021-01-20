<?php

namespace Haxibiao\Sns\Traits;

use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Sns\Notice;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait NoticeResolvers
{
    /**
     * 发送验证码
     */
    public function resolveNotice($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $noticbuild = Notice::where('expires_at', '>', now());
        return $noticbuild;
    }

}
