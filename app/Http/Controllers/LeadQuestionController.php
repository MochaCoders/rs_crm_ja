<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Property;
use App\Models\LeadQuestion;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\QualificationRule;

class LeadQuestionController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = $request->query('property_id');
        $property   = Property::findOrFail($propertyId);

        // all questions for this property
        $questions = LeadQuestion::where('property_id', $propertyId)->get();

        // existing rules
        $rules = QualificationRule::where('property_id', $propertyId)
            ->get(['lead_question_id', 'answer']);

        // email templates
        $templates = EmailTemplate::orderBy('name')->get(['id', 'name', 'subject', 'body']);

        return Inertia::render('LeadQuestions/Index', [
            'questions'        => $questions,
            'property_id'      => $propertyId,
            'property'         => $property,
            'rules'            => $rules,
            'emailTemplates'   => $templates,
            // Separate automation settings
                'qualifiedAutomationSettings'   => $property->automationSettings()
                    ->where('lead_type', 'qualified')
                    ->get(),

                'unqualifiedAutomationSettings' => $property->automationSettings()
                    ->where('lead_type', 'unqualified')
                    ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'questions' => 'required|array',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:input,textarea,email,checkbox,radio',
            'questions.*.options' => 'nullable|array',
        ]);

        // If you're resetting only questions for that property:
        LeadQuestion::where('property_id', $data['property_id'])->delete();

        foreach ($data['questions'] as $q) {
            LeadQuestion::create([
                'property_id' => $data['property_id'],
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => in_array($q['type'], ['checkbox', 'radio']) ? $q['options'] : null
            ]);
        }

        return redirect()->back()->with('success', 'Lead questions updated.');
    }

}
