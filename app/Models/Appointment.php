<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
            'property_id',
            'email',
            'scheduled_at',
            'status',
        ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(\App\Models\Property::class);
    }
}
