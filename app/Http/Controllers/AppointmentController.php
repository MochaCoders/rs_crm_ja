<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Mail\AppointmentConfirmed;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function index()
    {
        return Inertia::render('CreateAppointment');
    }
    /**
     * Show the appointment form.
     * Prefills the email from the query string (?email=foo@bar.com) or from the logged-in user.
     */
    public function create(Request $request, Property $property)
    {
        $prefillEmail = $request->query('email') ?? optional($request->user())->email ?? '';

        return Inertia::render('Appointment', [
            'property' => $property->only(['id', 'title']),
            'email'    => $prefillEmail,
        ]);
    }

    /**
     * Store appointment.
     * Expects scheduled_at as an ISO string (UTC) or a valid date string.
     */
    public function store(Request $request, Property $property)
    {
        $data = $request->validate([
            'email'        => 'required|email',
            'scheduled_at' => 'required|date|after:now', // ISO8601 from client (UTC)
        ]);

        $scheduledAtUtc = Carbon::parse($data['scheduled_at'])
            ->utc()
            ->setSecond(0);

        $exists = Appointment::where('property_id', $property->id)
            ->where('email', $data['email'])
            ->where('scheduled_at', $scheduledAtUtc)
            ->exists();

        if ($exists) {
            $whenLocal = $scheduledAtUtc->copy()
                ->setTimezone(config('app.timezone'))
                ->format('M j, Y g:i A');

            return back()
                ->with('error', "You already have an appointment scheduled for {$whenLocal}.")
                ->withErrors(['scheduled_at' => 'Duplicate appointment time for this email and property.']);
        }

        try {
            $appointment = Appointment::create([
                'property_id'  => $property->id,
                'email'        => $data['email'],
                'scheduled_at' => $scheduledAtUtc,   // store UTC
                'status'       => 'scheduled',
            ]);
        } catch (QueryException $e) {
            if ((string)$e->getCode() === '23000') {
                $whenLocal = $scheduledAtUtc->copy()
                    ->setTimezone(config('app.timezone'))
                    ->format('M j, Y g:i A');

                return back()
                    ->with('error', "An appointment for {$whenLocal} already exists.")
                    ->withErrors(['scheduled_at' => 'Duplicate appointment time.']);
            }
            throw $e;
        }

        // ✅ Build the payload array
        $payload = [
            'name'          => optional($appointment->user)->name,        // from User model
            'email'          => $appointment->email,        // from User model
            'property_name' => optional($appointment->property)->title,
            'scheduled_at'  => $appointment->scheduled_at
                ->setTimezone(config('app.timezone'))
                ->translatedFormat('l, F j, Y g:i A'), // Thursday, August 1, 2025
        ];

        // ✅ Send the confirmation email
        Mail::to($appointment->email)->send(new AppointmentConfirmed($payload));

        $when = $scheduledAtUtc->copy()
            ->setTimezone(config('app.timezone'))
            ->format('M j, Y g:i A');

        return redirect()
            ->route('appointments.create', ['property' => $property->id, 'email' => $data['email']])
            ->with('success', "Appointment scheduled for {$when}!");
    }

}
