<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'agent' => [
                'id' => $this->agent->id,
                'name' => $this->agent->name,
                'email' => $this->agent->email,
            ],
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'parish' => $this->parish,
            // Add units
            'units' => $this->units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'description' => $unit->description,
                    'price' => $unit->price,
                    'currency' => $unit->currency,
                    'type' => $unit->type,
                    'status' => $unit->status,
                    'submission_id' => $unit->submission_id,
                ];
            }),
        ];
    }
}
