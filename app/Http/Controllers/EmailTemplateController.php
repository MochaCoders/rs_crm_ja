<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\LeadQuestion;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $user       = $request->user();
        $propertyId = $request->query('property_id');

        // All templates (global; adjust if you scope templates by owner)
        $templates = EmailTemplate::orderBy('name')->get(['id', 'name', 'subject', 'body']);

        // Properties that belong to the authenticated user
        // Assumes properties table has a user_id column and Property has 'title'
        $properties = Property::query()
            ->where('user_id', $user->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        // Build base variables
        $variables = [
            ['label' => 'Lead Email',                 'token' => 'lead.email'],
            ['label' => 'Lead Name',                  'token' => 'lead.name'],
            ['label' => 'Lead Telephone',             'token' => 'lead.telephone'],
            ['label' => 'Property Name',              'token' => 'property.name'],
            ['label' => 'Property Address',           'token' => 'property.address'],
            ['label' => 'Set Appointment Link',       'token' => 'appointment.set'],
        ];

        return Inertia::render('EmailTemplates/Index', [
            'templates'     => $templates,
            'variables'     => $variables,
            'properties'    => $properties,
            'propertyId'    => $propertyId,      // currently selected property (nullable
        ]);
    }

    public function create()
    {
        return Inertia::render('EmailTemplates/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'nullable|exists:properties,id',
            'name'        => 'required|string|unique:email_templates,name',
            'subject'     => 'required|string',
            'body'        => 'required|string',
        ]);

        EmailTemplate::create($data);

        // Keep the selected property_id in the query so the user stays on the same context
        $propertyId = $request->query('property_id');

        return redirect()
            ->route('email-templates.index', array_filter(['property_id' => $propertyId]))
            ->with('success', 'Email template created.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return Inertia::render('EmailTemplates/Edit', [
            'template' => $emailTemplate,
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $request->validate([
            'name'    => 'required|string|unique:email_templates,name,' . $emailTemplate->id,
            'subject' => 'required|string',
            'body'    => 'required|string',
        ]);

        $emailTemplate->update($data);

        // Keep the selected property_id in the query so the user stays on the same context
        $propertyId = $request->query('property_id');

        return redirect()
            ->route('email-templates.index', array_filter(['property_id' => $propertyId]))
            ->with('success', 'Email template updated.');
    }

    public function destroy(EmailTemplate $emailTemplate, Request $request)
    {
        $emailTemplate->delete();

        $propertyId = $request->query('property_id');

        return redirect()
            ->route('email-templates.index', array_filter(['property_id' => $propertyId]))
            ->with('success', 'Email template deleted.');
    }
}
