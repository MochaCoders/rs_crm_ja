<?php

namespace App\Models;

use App\Models\Property;
use Illuminate\Database\Eloquent\Model;

class LeadAction extends Model
{
    protected $fillable = ['trigger', 'action_type', 'template_id'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
