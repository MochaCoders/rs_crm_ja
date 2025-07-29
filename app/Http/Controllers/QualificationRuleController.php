<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\QualificationRule;
use Inertia\Inertia;

class QualificationRuleController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $data = $request->validate([
            'lead_question_id' => 'required|exists:lead_questions,id',
            'answer'           => 'required|string',
        ]);

        QualificationRule::updateOrCreate(
            [
                'property_id'      => $property->id,
                'lead_question_id' => $data['lead_question_id'],
            ],
            [
                'answer' => $data['answer'],
            ]
        );

        return back()->with('success', 'Qualification rule saved.');
    }

    public function destroy(Property $property, $leadQuestionId)
    {
        QualificationRule::where('property_id', $property->id)
            ->where('lead_question_id', $leadQuestionId)
            ->delete();

        return back()->with('success', 'Qualification rule removed.');
    }
}
