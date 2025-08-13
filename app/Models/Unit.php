<?php

namespace App\Models;

use App\Models\Submission;
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
        'submission_id'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

}
