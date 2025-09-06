<?php

namespace App\Models;

use App\Models\Property;
use Illuminate\Database\Eloquent\Model;

class AutomationSetting extends Model
{
    protected $fillable = [
            'property_id',
            'action',
            'template_id',
            'agent_email',
            'send_method',
        ];

    /**
     * The property this setting belongs to.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The email template selected.
     */
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }
}
