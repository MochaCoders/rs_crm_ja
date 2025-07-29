<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectFile extends Model
{
    protected $fillable = [
        'submission_id',
        'filename',
        'path',
    ];

}
