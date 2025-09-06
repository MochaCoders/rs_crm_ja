<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class AgentNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $property_title;

    public function __construct($property_title)
    {
        $this->property_title = $property_title;
    }

    public function build()
    {
        return $this->subject('New Lead Submission')
                    ->markdown('emails.agent-notification')
                    ->with([
                        'property_title' => $this->property_title,
                    ]);
    }
}
