<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\LeadQuestion;
use Illuminate\Database\Seeder;

class LeadQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $property = Property::where('title', 'Toby Hammocks')->first();

        if (!$property) {
            $this->command->error('Property "Toby Hammocks" not found. Seeder aborted.');
            return;
        }

        $propertyId = $property->id;

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'First Name',
            'type' => 'input',
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Last Name',
            'type' => 'input',
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Phone Number',
            'type' => 'input',
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Email',
            'type' => 'input',
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Are you ready to purchase at Toby Hammocks?',
            'type' => 'radio',
            'options' => json_encode([
                'Yes, I am ready and Pre-approved',
                'I want to shop the market more',
            ]),
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Are you purchasing by way of Mortgage or Cash?',
            'type' => 'radio',
            'options' => json_encode(['Cash', 'Mortgage']),
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Are you already pre-approved?',
            'type' => 'radio',
            'options' => json_encode(['Yes I am', 'Not yet']),
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'How much are you pre-approved for?',
            'type' => 'input',
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'What is your Occupation? (Businessman & Businesswoman is NOT acceptable)',
            'type' => 'input',
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Where are you Employed?',
            'type' => 'input',
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'What type of unit are you interested in at Toby Hammocks?',
            'type' => 'radio',
            'options' => json_encode([
                'The 2 bedroom (828 sq ft)',
                'The 3 bedroom (1280 sq ft)',
            ]),
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'What are your reasons for purchasing?',
            'type' => 'radio',
            'options' => json_encode([
                'I am seeking personal family residence',
                'I am seeking investment property',
            ]),
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Are you prepared for your purchase with deposit and closing costs?',
            'type' => 'radio',
            'options' => json_encode([
                'Yes, I am',
                "No. I don't have my deposit ready",
            ]),
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Do you have a Conveyance attorney to procure your sale transaction?',
            'type' => 'radio',
            'options' => json_encode([
                'Yes, I do',
                'No and I would appreciate your recommendation',
            ]),
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'What is the name of your Attorney?',
            'type' => 'input',
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Are you a Jamaican citizen?',
            'type' => 'radio',
            'options' => json_encode([
                "Yes, I'm a 'Yaadie'",
                "No I'm not",
            ]),
        ]);

        LeadQuestion::create([
            'property_id' => $propertyId,
            'question' => 'Is this your first time purchasing Real Estate in Jamaica?',
            'type' => 'radio',
            'options' => json_encode([
                'Yes, it is my first time',
                "No, it isn't my first time",
            ]),
        ]);
    }
}
