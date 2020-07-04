<?php

namespace App\Models;

class CodeLeak extends ModelBase
{
    const STATUS_PENDING = 0;
    const STATUS_FALSE = 1;
    const STATUS_ABNORMAL = 2;
    const STATUS_SOLVED = 3;
    protected $table = 'code_leak';
    protected $fillable = [
        'uuid',
        'status',
        'repo_owner',
        'repo_name',
        'html_url_blob',
        'path',
        'repo_description',
        'keyword',
        'description',
        'handle_user',
    ];
}
