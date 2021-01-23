<?php

namespace Haxibiao\Sns\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Reportable
{

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * 记录几个最主要的举报记录在json数据字段
     */
    public function latestReports()
    {
        $json = json_decode($this->json, true);
        if (empty($json)) {
            $json = [];
        }
        $reports = [];
        if (isset($json['reports'])) {
            $reports = $json['reports'];
        }
        return $reports;
    }

}
