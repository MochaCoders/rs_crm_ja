<?php


// app/Models/Submission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'property_id'];

    public function responses()
    {
        return $this->hasMany(LeadResponse::class);
    }

    public function files()
    {
        return $this->hasMany(ProspectFile::class);
    }
}
