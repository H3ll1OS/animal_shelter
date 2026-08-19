<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MobileController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('/bootstrap', [MobileController::class, 'bootstrap']);

    Route::middleware('api.token')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::match(['put', 'post'], '/account', [MobileController::class, 'updateAccount']);
        Route::put('/account/password', [MobileController::class, 'updatePassword']);
        Route::post('/pets/{pet}/adoptions', [MobileController::class, 'storeAdoption']);
        Route::post('/donations', [MobileController::class, 'storeDonation']);

        Route::post('/pets', [MobileController::class, 'storePet']);
        Route::put('/pets/{pet}', [MobileController::class, 'updatePet']);
        Route::delete('/pets/{pet}', [MobileController::class, 'destroyPet']);

        Route::post('/adoptions/{adoption}/approve', [MobileController::class, 'approveAdoption']);
        Route::post('/adoptions/{adoption}/reject', [MobileController::class, 'rejectAdoption']);
        Route::delete('/donations/{donation}', [MobileController::class, 'destroyDonation']);

        Route::post('/health-records', [MobileController::class, 'storeHealthRecord']);
        Route::put('/health-records/{healthRecord}', [MobileController::class, 'updateHealthRecord']);
        Route::delete('/health-records/{healthRecord}', [MobileController::class, 'destroyHealthRecord']);

        Route::post('/vaccine-schedules', [MobileController::class, 'storeVaccineSchedule']);
        Route::post('/vaccine-schedules/{vaccineSchedule}/complete', [MobileController::class, 'completeVaccineSchedule']);
        Route::delete('/vaccine-schedules/{vaccineSchedule}', [MobileController::class, 'destroyVaccineSchedule']);

        Route::post('/euthanasia-logs', [MobileController::class, 'storeEuthanasiaLog']);
        Route::put('/euthanasia-logs/{euthanasiaLog}', [MobileController::class, 'updateEuthanasiaLog']);
        Route::delete('/euthanasia-logs/{euthanasiaLog}', [MobileController::class, 'destroyEuthanasiaLog']);

        Route::put('/users/{user}/role-status', [MobileController::class, 'updateUserRoleStatus']);
        Route::delete('/users/{user}', [MobileController::class, 'destroyUser']);
    });
});
