<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\LeadAction;
use Illuminate\Http\Request;

class LeadActionController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $data = $request->validate([
            'trigger'      => 'required|in:qualifies,not_qualify',
            'action_type'  => 'required|string',
            'template_id'  => 'nullable|exists:email_templates,id',
        ]);

        // Ensure template_id only set if action_type is email
        if ($data['action_type'] !== 'email') {
            $data['template_id'] = null;
        }

        $property->actions()->create($data);

        return back()->with('success', 'Action added.');
    }
}
