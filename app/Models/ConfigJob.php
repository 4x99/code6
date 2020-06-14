<?php

namespace App\Models;

class ConfigJob extends ModelBase
{
    const STORE_TYPE_ALL = 0;
    const STORE_TYPE_FILE_STORE_ONCE = 1;
    const STORE_TYPE_REPO_STORE_ONCE = 2;
    protected $table = 'config_job';
    protected $fillable = ['keyword', 'scan_page', 'scan_interval_min', 'store_type', 'description'];
}
