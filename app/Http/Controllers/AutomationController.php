<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\AutomationSetting;

class AutomationController extends Controller
{
    public function store(Request $request, Property $property)
    {
        // Base rules (allow template_id and agent_email to pass through)
        $rules = [
            'lead_type'               => 'required|string|in:qualified,unqualified',
            'actions'                 => 'required|array|min:1',
            'actions.*.type'          => 'required|string|in:send_email,email_agent,schedule_visit',
            'actions.*.send_method'   => 'required|string|in:immediate,manual',
            'actions.*.template_id'   => 'nullable|integer|exists:email_templates,id',
            'actions.*.agent_email'   => 'nullable|email',
        ];

        $validator = Validator::make($request->all(), $rules);

        // Conditional requirements per action type
        $validator->after(function ($v) use ($request) {
            foreach ((array) $request->input('actions', []) as $i => $action) {
                $type = $action['type'] ?? null;

                if ($type === 'send_email' && empty($action['template_id'])) {
                    $v->errors()->add("actions.$i.template_id", 'Template is required for Send Email.');
                }

                if ($type === 'email_agent' && empty($action['agent_email'])) {
                    $v->errors()->add("actions.$i.agent_email", 'Agent email is required for Email and Notify me.');
                }
            }
        });

        $data = $validator->validate();

        DB::transaction(function () use ($property, $data) {
            // Clear existing actions for this property + lead_type only
            AutomationSetting::where('property_id', $property->id)
                ->where('lead_type', $data['lead_type'])
                ->delete();

            // Insert new actions (one row per action)
            foreach ($data['actions'] as $a) {
                AutomationSetting::create([
                    'property_id'   => $property->id,
                    'lead_type'     => $data['lead_type'], // 👈 store lead_type
                    'action'        => $a['type'],
                    'template_id'   => $a['template_id'] ?? null,
                    'agent_email'   => $a['agent_email'] ?? null,
                    'send_method'   => $a['send_method'],
                ]);
            }
        });

        return back()->with('success', 'Automation settings saved!');
    }

}
