<?php

namespace App\Models;

class ConfigJob extends ModelBase
{
    protected $table = 'config_job';
    protected $fillable = ['keyword', 'scan_page', 'scan_interval_min', 'description'];
}
