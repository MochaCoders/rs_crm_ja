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

        // Eager‑load units → prospect
        $property->load(['units.prospect']);

        // Load basic prospect list
        $prospects = Prospect::select('id', 'name', 'email', 'telephone')->get();

        // Check if any Form exists for this property
        $hasForm = LeadQuestion::where('property_id', $property->id)->exists();

        // Check if any Submission exists for this property that has at least one response
        $hasEntries = Submission::where('property_id', $property->id)
            ->whereHas('responses')
            ->exists();

        return Inertia::render('Properties/Edit', [
                'property'   => new PropertyResource($property),
                'prospects'  => $prospects,
                'has_form'  => $hasForm,
                'has_entries' => $hasEntries,
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
