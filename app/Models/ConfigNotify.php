<?php

namespace App\Models;

class ConfigNotify extends ModelBase
{
    const TYPE = [
        self::TYPE_EMAIL,
        self::TYPE_DING_TALK,
        self::TYPE_WORK_WECHAT,
        self::TYPE_TELEGRAM,
        self::TYPE_FEISHU,
        self::TYPE_WEBHOOK,
    ];
    const TYPE_EMAIL = 'email';
    const TYPE_DING_TALK = 'dingTalk';
    const TYPE_WORK_WECHAT = 'workWechat';
    const TYPE_TELEGRAM = 'telegram';
    const TYPE_FEISHU = 'feishu';
    const TYPE_WEBHOOK = 'webhook';

    protected $table = 'config_notify';
    protected $fillable = ['type', 'value', 'enable', 'interval_min', 'start_time', 'end_time'];
}
