<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Imports\UnitsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UnitController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
        ]);

        $file = $request->file('file');
        $propertyId = $request->input('property_id');

        // Parse and create units (example: with Laravel Excel or fgetcsv)
        // Laravel Excel example:
        Excel::import(new UnitsImport($propertyId), $file);

        return back()->with('success', 'Units uploaded successfully.');
    }

    public function close(Request $request)
    {

        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'prospect_id' => 'required|exists:prospects,id',
        ]);

        $unit = Unit::find($request->unit_id);
        $unit->status = 'Sold';
        $unit->purchaser_id = $request->prospect_id; // Ensure `purchaser_id` exists in your `units` table
        $unit->save();

        return back()->with('success', 'Unit closed successfully.');
    }

    public function reopen(Request $request)
    {
        $unit = Unit::findOrFail($request->unit_id);
        $unit->purchaser_id = null;
        $unit->status = 'Available'; // Optional: reset status
        $unit->save();

        return back()->with('success', 'Unit reopened successfully.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return back()->with('success', 'Unit deleted successfully.');
    }
}
