<?php

namespace App\Models;

class CodeFragment extends ModelBase
{
    const UPDATED_AT = null;
    protected $table = 'code_fragment';
    protected $fillable = ['uuid', 'content'];
}
