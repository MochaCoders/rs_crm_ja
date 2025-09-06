<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmed extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $payload;
    /**
     * Create a new message instance.
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {

        return $this->subject('Your appointment is confirmed')
                    ->markdown('emails.appointments.confirmed', [
                        'payload' => $this->payload,
                    ]);
    }
}
