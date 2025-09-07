<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Property;
use App\Models\Prospect;
use App\Models\Submission;
use App\Models\LeadQuestion;
use App\Models\LeadResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PropertyResource;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;

class PropertyController extends Controller
{
    public function index()
    {
        return Inertia::render('Properties/Index', [
            'properties' => PropertyResource::collection(
                Auth::user()->properties()->latest()->get()
            ),
        ]);
    }

    public function store(StorePropertyRequest $request)
    {
        $validated = $request->validated();

        $property = Property::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return Redirect::route('properties.index')->with('success', 'Property created successfully.');

    }

    public function show(Property $property)
    {
        $hasForm = LeadQuestion::where('property_id', $property->id)->exists();

        $hasEntries = Submission::where('property_id', $property->id)
            ->whereHas('responses')
            ->exists();
        $prospects = Submission::with([
                // Eager-load each response's question text
                'responses.question:id,question',          // keep the payload light
                'files:id,submission_id,filename,path',
            ])
            ->where('property_id', $property->id)
            ->orderByDesc('created_at')
            ->get(['id', 'uuid', 'property_id', 'created_at']);

        return Inertia::render('Properties/Edit', [
            'property'    => PropertyResource::make($property),
            'has_form'    => $hasForm,
            'has_entries' => $hasEntries,
            'prospects'   => $prospects,
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property)
    {
        // $this->authorize('update', $property);
        $property->update($request->validated());
        return redirect()->back()->with('success', 'Property updated successfully.');


    }

    public function destroy(Property $property)
    {
        // $this->authorize('delete', $property);
        $property->delete();

        return Redirect::route('properties.index')->with('success', 'Property created successfully.');
    }
}
