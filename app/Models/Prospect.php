<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    protected $fillable = ['name', 'email', 'telephone'];

    public function units()
    {
        return $this->hasMany(Unit::class, 'purchaser_id');
    }
}
