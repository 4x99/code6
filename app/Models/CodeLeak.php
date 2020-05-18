<?php

namespace App\Models;

class CodeLeak extends ModelBase
{
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
