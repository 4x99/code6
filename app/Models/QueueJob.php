<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueJob extends Model
{
    const UPDATED_AT = null;
    protected $table = 'queue_job';
    protected $fillable = ['keyword'];
}
