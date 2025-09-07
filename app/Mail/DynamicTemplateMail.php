<?php

namespace App\Mail;

use App\Models\Property;
use App\Models\Submission;
use App\Models\LeadQuestion;
use App\Models\LeadResponse;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class DynamicTemplateMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $template;
    public $submission;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailTemplate $template, Submission $submission, Property $property)
    {
        $this->template = $template;
        $this->submission = $submission;
        $this->property = $property;

    }

    public function build()
    {

        $appointmentUrl = url("/appointments/create/{$this->submission->property_id}?email=" . urlencode($this->getLeadResponse('email')));

        $variables = [
            'lead.email'       => $this->getLeadResponse('email'),
            'lead.name'        => $this->getLeadResponse('name'),
            'lead.telephone'   => $this->getLeadResponse('telephone'),
            'property.name'    => $this->property->title ?? '',
            'property.address' => $this->property->address ?? '',
            'appointment.set'  => $this->renderAppointmentButton($appointmentUrl),
        ];

        // Replace tokens in template
        $body = $this->replaceTokens($this->template->body, $variables);
        $subject = $this->replaceTokens($this->template->subject, $variables);

        return $this->subject($subject)
                    ->markdown('emails.dynamic-template')
                    ->with([
                        'body' => $body,
                    ]);
    }

    /**
     * Replace {{ tokens }} in the template with real values.
     */
    protected function replaceTokens(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{ '.$key.' }}', $value, $text); // no e()
        }

        return $text;
    }

    /**
     * Get a lead response by "type" or "label".
     * You can adjust this depending on how you store lead questions.
     */
    protected function getLeadResponse(string $field)
    {
        $leadQuestion = LeadQuestion::where('property_id', $this->submission->property_id)
            ->where(function ($query) use ($field) {
                $query->where('type', $field) // e.g. "email"
                      ->orWhere('question', 'like', "%$field%"); // fallback if using question text
            })
            ->first();

        if (!$leadQuestion) {
            return '';
        }

        return LeadResponse::where('submission_id', $this->submission->id)
            ->where('lead_question_id', $leadQuestion->id)
            ->value('response') ?? '';
    }

    protected function renderAppointmentButton(string $url): string
    {
        return view('emails.partials.appointment-button', ['url' => $url])->render();
    }
}
