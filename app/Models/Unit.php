<?php

namespace App\Models;

use App\Models\Prospect;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'property_id',
        'unit_number',
        'description',
        'price',
        'currency',
        'type',
        'status',
        'purchaser_id'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'purchaser_id');
    }

}
