<?php

namespace App\Models;

class QueueJob extends ModelBase
{
    const UPDATED_AT = null;
    protected $table = 'queue_job';
    protected $fillable = ['keyword'];
}
