<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('name')->get();
        return Inertia::render('EmailTemplates/Index', [
            'templates' => $templates,
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

        EmailTemplate::create($data);

        return redirect()->route('email-templates.index')
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
