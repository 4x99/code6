<?php
/*
 * @Author: Byc4i
 * @Description: 
 * @Date: 2021-06-24 09:56:06
 * @LastEditTime: 2021-06-24 10:01:00
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigNotify extends Model
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
