<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualificationRule extends Model
{
    protected $fillable = [
            'property_id',
            'lead_question_id',
            'answer',
        ];

    public function question()
    {
        return $this->belongsTo(LeadQuestion::class, 'lead_question_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
