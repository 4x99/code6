<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeFragment extends Model
{
    const UPDATED_AT = null;
    protected $table = 'code_fragment';
    protected $fillable = ['uuid', 'content'];
}
