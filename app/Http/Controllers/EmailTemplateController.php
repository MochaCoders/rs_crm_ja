<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index()
    {
        // 1) Load templates
        $templates = EmailTemplate::where('template_owner_id', auth()->id())
                        ->orderBy('name')
                        ->get(['id', 'name', 'subject', 'body']);

        // 2) Define your available variables
        //    You could also fetch these from a DB table if you prefer.
        $variables = [
            ['label' => 'Customer Email',      'placeholder' => '{{email}}'],
            ['label' => 'Customer Name',       'placeholder' => '{{name}}'],
            ['label' => 'Property Name',      'placeholder' => '{{property_name}}'],
            ['label' => 'My Name',      'placeholder' => '{{my_name}}'],
            ['label' => 'My Email',      'placeholder' => '{{my_email}}'],
            ['label' => 'My Telephone',      'placeholder' => '{{my_telephone}}'],
        ];

        // 3) Render Inertia view with both arrays
        return Inertia::render('EmailTemplates/Index', [
            'templates' => $templates,
            'variables' => $variables,
        ]);
    }


    public function create()
    {
        return Inertia::render('EmailTemplates/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|unique:email_templates,name',
            'subject' => 'required|string',
            'body'    => 'required|string',
        ]);

        // Associate the template with the current user
        $data['template_owner_id'] = auth()->id();

        EmailTemplate::create($data);

        return redirect()
            ->route('email-templates.index')
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

        return redirect()->route('email-templates.index')
                         ->with('success', 'Email template updated.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()->route('email-templates.index')
                         ->with('success', 'Email template deleted.');
    }
}
