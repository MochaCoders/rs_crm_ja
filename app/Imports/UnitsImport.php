<?php

namespace App\Imports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UnitsImport implements ToModel, WithHeadingRow
{
    protected $propertyId;

    public function __construct($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    // Tell Laravel Excel that the real headings are on row 2
    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {

        // Skip if unit_number is missing
        if (empty($row['unit_number'])) {
            return null;
        }

        // Check if a unit with the same unit_number already exists for the property
        $exists = Unit::where('property_id', $this->propertyId)
                      ->where('unit_number', $row['unit_number'])
                      ->exists();

        if ($exists) {
            return null; // Skip this record
        }

        // Proceed to insert the unit
        return new Unit([
            'unit_number' => $row['unit_number'] ?? '',
            'description' => $row['description'] ?? '',
            'price'       => $row['price'] ?? 0,
            'currency'    => $row['currency'] ?? 'JMD',
            'type'        => $row['type'] ?? 'Apartment',
            'status'        => $row['status'] ?? 'Available',
            'property_id' => $this->propertyId,
        ]);

    }
}
