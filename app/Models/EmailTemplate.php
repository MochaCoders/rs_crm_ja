<?php

namespace App\Models;

use App\Models\Property;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'property_id',
        'name',
        'subject',
        'body',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
