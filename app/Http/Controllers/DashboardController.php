<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $propertyCount = Auth::user()->properties()->count();
        return Inertia::render('Dashboard', [
            'propertyCount' => $propertyCount,
        ]);
    }
}
