<?php

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| All routes in this file are assigned the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    // 1. Location-Wise Hospital & Branch Search
    Route::get('/hospitals', [ApiController::class, 'hospitals'])->name('api.v1.hospitals');

    // 2. Doctors List & Filtering
    Route::get('/doctors', [ApiController::class, 'doctors'])->name('api.v1.doctors');

    // 3. Doctor Profile with Schedule, Fees, & Customer Testimonials
    Route::get('/doctors/{id}', [ApiController::class, 'doctorProfile'])->name('api.v1.doctors.profile');

    // 4. Hospital-Wise Health Checkup Packages
    Route::get('/packages', [ApiController::class, 'packages'])->name('api.v1.packages');

    // 5. Quick Appointment Booking via API
    Route::post('/appointments', [ApiController::class, 'createAppointment'])->name('api.v1.appointments.create');

    // 6. Book Health Checkup Package via API
    Route::post('/packages/book', [ApiController::class, 'bookPackage'])->name('api.v1.packages.book');

    // 7. Customer Testimonials & Reviews
    Route::get('/testimonials', [ApiController::class, 'testimonials'])->name('api.v1.testimonials');
});
