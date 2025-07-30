<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use App\Models\AutomationSetting;

class AutomationController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $data = $request->validate([
            'action'      => 'required|in:email',
            'template_id' => 'required_if:action,email|exists:email_templates,id',
            'send_method' => 'required|in:immediate,manual',
        ]);

        AutomationSetting::updateOrCreate(
            ['property_id' => $property->id],
            $data
        );

        return back()->with('success', 'Automation settings saved.');
    }
}
