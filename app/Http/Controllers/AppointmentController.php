<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\Appointment;
use Illuminate\Http\Request;

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

        // Normalize to UTC and zero seconds so equality checks are consistent
        $scheduledAtUtc = Carbon::parse($data['scheduled_at'])
            ->utc()
            ->setSecond(0);

        // Prevent duplicates: same property + email + exact datetime
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
            Appointment::create([
                'property_id'  => $property->id,
                'email'        => $data['email'],
                'scheduled_at' => $scheduledAtUtc,   // store UTC
                'status'       => 'scheduled',
            ]);
        } catch (QueryException $e) {
            // If you also add a DB unique index, this gracefully handles race conditions
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

        $when = $scheduledAtUtc->copy()
            ->setTimezone(config('app.timezone'))
            ->format('M j, Y g:i A');

        return redirect()
            ->route('appointments.create', ['property' => $property->id, 'email' => $data['email']])
            ->with('success', "Appointment scheduled for {$when}!");
    }
}
