<?php

namespace App\Models;

use App\Models\LeadQuestion;
use Illuminate\Database\Eloquent\Model;

class LeadResponse extends Model
{
    protected $fillable = [
        'submission_id',
        'lead_question_id',
        'response',
    ];

    public function question()
    {
        return $this->belongsTo(LeadQuestion::class, 'lead_question_id');
    }
}
