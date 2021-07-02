<?php
namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Message;

trait MessageAttrs
{

    //兼容答赚之外 项目老字段message是string不是json格式数据
    public function getBodyAttribute()
    {
        $json    = [];
        $bodyRaw = $this->getRawOriginal('body') ?? '';
        $body    = @json_decode($bodyRaw, true);
        if (!is_array($body)) {
            $json['text'] = $bodyRaw;
        } else {
            $json = $body;
        }
        return $json;
    }

    /**
     * 消息的文字模式（最后一条消息，APP通知等场景）
     */
    public function getMessageAttribute()
    {
        $type = $this->type;
        if ($type == Message::IMAGE_TYPE) {
            return "图片消息";
        }
        if ($type == Message::AUDIO_TYPE) {
            return "语音消息";
        }
        if ($type == Message::VIDEO_TYPE) {
            return "视频消息";
        }
        return data_get($this->body, 'text', '文字消息');
    }
}
