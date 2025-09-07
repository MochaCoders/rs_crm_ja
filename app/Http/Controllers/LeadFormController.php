<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Property;
use App\Models\Submission;
use Illuminate\Support\Str;
use App\Models\LeadQuestion;
use App\Models\LeadResponse;
use App\Models\ProspectFile;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Mail\DynamicTemplateMail;
use App\Models\AutomationSetting;
use App\Models\QualificationRule;
use App\Mail\AgentNotificationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\LeadQuestionPreference;

class LeadFormController extends Controller
{
    public function index(Property $property)
    {
        return Inertia::render('LeadQuestions/Index', [
            'property' => $property,
            'questions' => $property->leadQuestions, // Assuming a relation exists
        ]);
    }
    public function show(Property $property)
    {
        $questions = LeadQuestion::where('property_id', $property->id)->get();

        return Inertia::render('LeadForm', [
            'questions' => $questions,
            'property' => $property,
            'property_id' => $property->id,
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'responses'   => 'required|array',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $submission = Submission::create([
            'uuid'        => Str::uuid(),
            'property_id' => $request->property_id,
        ]);

        $property =  Property::find($submission->property_id);

        // Save question responses
        foreach ($request->input('responses', []) as $questionId => $response) {
            LeadResponse::create([
                'submission_id'   => $submission->id,
                'lead_question_id' => $questionId,
                'response'        => is_array($response) ? json_encode($response) : $response,
            ]);
        }

        // Save attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $path = $file->store('prospect_uploads', 'public');

                    ProspectFile::create([
                        'submission_id' => $submission->id,
                        'filename'      => $file->getClientOriginalName(),
                        'path'          => $path,
                    ]);
                }
            }
        }

        /**
         * 🔥 Run automations for this property
         */
        $automations = AutomationSetting::where('property_id', $request->property_id)->get();


        foreach ($automations as $automation) {
            // 🚦 only process "immediate" automations
            if ($automation->send_method !== 'immediate') {
                continue;
            }

            switch ($automation->action) {
                case 'send_email':

                    $template = EmailTemplate::find($automation->template_id);
                    $emailQuestion = LeadQuestion::where('property_id', $submission->property_id)->where('type', 'email')->first();
                    $emailAddress = LeadResponse::where('submission_id', $submission->id)->where('lead_question_id', $emailQuestion->id)->value('response');
                    if ($emailAddress && filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($emailAddress)->send(new DynamicTemplateMail($template, $submission, $property));
                    }
                    break;

                case 'email_agent':
                    //DONE
                    $agent = $property->agent;
                    if ($agent) {
                        Mail::to($agent->email)->send(new AgentNotificationMail($property->title));
                    }
                    break;

                case 'schedule_visit':
                    // ⚡ trigger scheduling workflow here
                    break;
            }
        }

        return redirect()
            ->route('lead.form', ['property' => $request->input('property_id')])
            ->with('success', 'Thank you for your submission!');
    }

    /**
     * Save or update the selected column‐headings for a property's submissions table.
     */
    public function updateHeadings(Request $request, Property $property)
    {
        $data = $request->validate([
            'selected_headings'   => 'required|array',
            'selected_headings.*' => 'integer|exists:lead_questions,id',
        ]);

        LeadQuestionPreference::updateOrCreate(
            ['property_id' => $property->id],
            ['selected_headings' => $data['selected_headings']]
        );

        return back()->with('success', 'Table columns updated.');
    }

    /**
         * Render the submissions page, injecting any saved preferences.
         */
    public function submissionsPage(Property $property)
    {

        // 2) Load your column‐heading questions
        $headings = LeadQuestion::where('property_id', $property->id)
            ->get(['id', 'question'])
            ->map(fn ($q) => ['id' => $q->id, 'question' => $q->question]);

        // 3) Load all qualification rules for this property
        $rules = QualificationRule::where('property_id', $property->id)->get();

        // 4) Fetch + reshape submissions, marking each qualified or not
        $submissions = Submission::with('responses')
            ->where('property_id', $property->id)
            ->get()
            ->map(function ($sub) use ($rules) {
                // flatten answers into [ question_id => response ]
                $answers = $sub->responses
                    ->mapWithKeys(fn ($r) => [$r->lead_question_id => $r->response]);

                // check every rule
                $qualified = $rules->every(
                    fn ($rule) =>
                    isset($answers[$rule->lead_question_id]) &&
                    $answers[$rule->lead_question_id] === $rule->answer
                );

                return [
                    'id'           => $sub->id,
                    'submitted_at' => $sub->created_at->toDateString(),
                    'answers'      => $answers,
                    'qualified'    => $qualified,
                ];
            });

        // 5) Load saved column prefs
        $preference = LeadQuestionPreference::firstWhere('property_id', $property->id);
        $selected   = $preference
            ? $preference->selected_headings
            : $headings->pluck('id')->all();

        // 6) Render with all submissions + their qualified status
        return Inertia::render('LeadQuestions/Submissions', [
            'property'         => $property,
            'headings'         => $headings,
            'submissions'      => $submissions,
            'selectedHeadings' => $selected,
        ]);
    }

}
