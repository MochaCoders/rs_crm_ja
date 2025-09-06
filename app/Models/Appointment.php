<?php

namespace App\Models;

use App\Models\User;
use App\Models\Property;
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
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
