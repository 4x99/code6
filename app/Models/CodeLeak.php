<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeLeak extends Model
{
    protected $table = 'code_leak';
    protected $fillable = ['uuid', 'html_url_blob', 'keyword', 'path', 'repo_owner', 'repo_name', 'repo_description'];
}
