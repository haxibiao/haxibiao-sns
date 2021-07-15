<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Meetup;

/**
 * 内容的Sns特性
 */
trait WithSns
{
    use Favorable;
    use Likeable;
    use Commentable;
    use Followable;
    use Reportable;
    use Tippable;
    use Blockable;

    public function meetup(){
    	$meetUp = data_get($this,'meet_up',false);
    	if(blank($meetUp)){
    		return null;
		}
    	return $this->hasOne(Meetup::class);
	}
}
