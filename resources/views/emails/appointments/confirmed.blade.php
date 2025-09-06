@component('mail::message')
# Appointment Confirmed

Hi {{ $payload['name'] ?? $payload['email'] }},

Your appointment for {{ $payload['property_name'] }} is set for {{ $payload['scheduled_at'] }}.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
