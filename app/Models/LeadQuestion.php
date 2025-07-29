<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadQuestion extends Model
{
    protected $fillable = ['property_id','question', 'type', 'options'];

    protected $casts = [
        'options' => 'array',
    ];
}
