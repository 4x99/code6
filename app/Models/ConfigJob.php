<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigJob extends Model
{
    protected $table = 'config_job';
    protected $fillable = ['keyword', 'scan_page', 'scan_interval_min', 'description'];
}
