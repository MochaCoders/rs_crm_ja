<?php

namespace App\Models;

use App\Models\User;
use App\Enums\Currency;
use App\Enums\UnitType;
use App\Models\AutomationSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'address',
        'parish',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function leadQuestions()
    {
        return $this->hasMany(LeadQuestion::class);
    }

    public function automationSettings()
    {
        return $this->hasMany(AutomationSetting::class);
    }
}
