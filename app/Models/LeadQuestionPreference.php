<?php

// app/Models/LeadQuestionPreference.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadQuestionPreference extends Model
{
    protected $fillable = [
        'property_id',
        'selected_headings',
    ];

    protected $casts = [
        'selected_headings' => 'array',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
