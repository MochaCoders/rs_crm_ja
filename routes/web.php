<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeadFormController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadQuestionController;
use App\Http\Controllers\QualificationRuleController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('properties', PropertyController::class);
    Route::post('/properties/{property}/qualification-rules', [QualificationRuleController::class, 'store'])->name('qualification-rules.store');
    Route::delete('/properties/{property}/qualification-rules/{lead_question}', [QualificationRuleController::class, 'destroy'])->name('qualification-rules.destroy');

    Route::post('/properties/{property}/qualification-automation', [AutomationController::class, 'store'])->name('qualification-automation.store');
    Route::resource('email-templates', EmailTemplateController::class)->except(['show']);

});

Route::post('/units/upload', [UnitController::class, 'upload'])->name('units.upload');
Route::post('/units/close', [UnitController::class, 'close'])->name('units.close');
Route::post('/units/reopen', [UnitController::class, 'reopen'])->name('units.reopen');
Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

// Route::get('/properties/{property}/lead-questions', [LeadQuestionController::class, 'index'])->name('lead-questions.index');
Route::get('/lead-questions', [LeadQuestionController::class, 'index'])->name('lead-questions.index');
Route::post('/lead-questions', [LeadQuestionController::class, 'store'])->name('lead-questions.store');

Route::get('/properties/{property}/submissions', [LeadFormController::class, 'submissionsPage'])->name('lead-form.submissions');
Route::get('/lead-form/{property}', [LeadFormController::class, 'show'])->name('lead.form');
Route::post('/lead-form', [LeadFormController::class, 'submit'])->name('lead.submit');

Route::post('/submissions/{property}/headings', [LeadFormController::class, 'updateHeadings'])->name('submissions.updateHeadings');


require __DIR__.'/auth.php';
