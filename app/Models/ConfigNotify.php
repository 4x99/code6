<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigNotify extends Model
{
    const TYPE = [
        self::TYPE_EMAIL,
        self::TYPE_DING_TALK,
        self::TYPE_WORK_WECHAT,
    ];
    const TYPE_EMAIL = 'email';
    const TYPE_DING_TALK = 'dingTalk';
    const TYPE_WORK_WECHAT = 'workWechat';

    protected $table = 'config_notify';
    protected $fillable = ['type', 'value', 'enable', 'interval'];
}
