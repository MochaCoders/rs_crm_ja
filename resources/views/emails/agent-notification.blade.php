@component('mail::message')
# New Lead Submission

A new lead has been submitted for property: **{{ $property_title }}**.

Thanks,  
{{ config('app.name') }}
@endcomponent