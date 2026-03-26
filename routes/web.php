<?php

use Illuminate\Support\Facades\Route;

// --- AUTH & USER CONTROLLERS ---
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;

// --- PUBLIC CONTROLLERS ---
use App\Http\Controllers\Public\DonationController as PublicDonationController; 
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PetController as PublicPetController; 
use App\Http\Controllers\Public\AccountController;

// --- ADMIN CONTROLLERS ---
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\HealthController;
use App\Http\Controllers\Admin\PetController as AdminPetController; 
use App\Http\Controllers\Admin\UserController; // Assuming this is the Admin User controller
use App\Http\Controllers\Admin\AdoptionController; 

// =========================================================================
// 🔐 AUTHENTICATION ROUTES
// =========================================================================

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Developer Login
Route::get('/developer/login', [LoginController::class, 'showLoginForm'])->name('developer.login');
// Choice page after privileged login
Route::get('/dashboard-choice', [LoginController::class, 'dashboardChoice'])->middleware('auth')->name('dashboard.choice');

// Registration
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Password Reset
Route::prefix('password')->name('password.')->group(function () {
    Route::get('/forgot', [PasswordResetController::class, 'showForgotPasswordForm'])->name('request');
    Route::post('/forgot', [PasswordResetController::class, 'sendResetLink'])->name('email');
    Route::get('/reset/{token}', [PasswordResetController::class, 'showResetPasswordForm'])->name('reset');
    Route::post('/reset', [PasswordResetController::class, 'resetPassword'])->name('update');
});


// =========================================================================
// 🌎 PUBLIC ROUTES
// =========================================================================

// Home and Static Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/donate', [HomeController::class, 'donate'])->name('donate');
Route::get('/donations', function () {return redirect()->route('donate');});
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// Account settings (authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/account/settings', [AccountController::class, 'edit'])->name('account.settings');
    Route::patch('/account/settings', [AccountController::class, 'update'])->name('account.settings.update');
    Route::get('/account/password', [AccountController::class, 'editPassword'])->name('account.password.edit');
    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
});

// Adoption & Pet Viewing Routes 
Route::get('/adopt', [PublicPetController::class, 'index'])->name('adopt');
Route::get('/adopt/{pet}', [PublicPetController::class, 'show'])->name('pets.show'); 

// Authenticated Actions (Donations, Adoption Submission)
Route::middleware('auth')->group(function () {
    Route::post('/adopt/{pet}', [PublicPetController::class, 'adopt'])->name('pets.adopt'); 
});

//donation
Route::post('/donate', [PublicDonationController::class, 'store'])->name('donations.store');


// =========================================================================
// 🔒 ADMIN ROUTES
// =========================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard Landing Page
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // Pet Management (Using the Admin PetController)
    Route::resource('pets', AdminPetController::class);

    // Adoption Applications (Listing and Actions)
    Route::resource('adoptions', AdoptionController::class)->only(['index', 'show']);
    Route::match(['post', 'get'], '/adoptions/{adoption}/approve', [AdoptionController::class, 'approve'])->name('adoptions.approve');
    Route::match(['post', 'get'], '/adoptions/{adoption}/reject', [AdoptionController::class, 'reject'])->name('adoptions.reject');

    // HEALTH MODULES (Grouped: Final name prefix is 'admin.health.')
    Route::prefix('health')->name('health.')->group(function () {
        
        // Health Monitoring
        Route::get('/monitoring', [HealthController::class, 'monitoring'])->name('monitoring');
        Route::get('/monitoring/create', [HealthController::class, 'createMonitoring'])->name('monitoring.create');
        Route::post('/monitoring', [HealthController::class, 'storeMonitoring'])->name('monitoring.store');
        Route::get('/monitoring/{alert}', [HealthController::class, 'showMonitoring'])->name('monitoring.show');
        Route::get('/monitoring/{alert}/edit', [HealthController::class, 'editMonitoring'])->name('monitoring.edit');
        Route::put('/monitoring/{alert}', [HealthController::class, 'updateMonitoring'])->name('monitoring.update');
        Route::delete('/monitoring/{alert}', [HealthController::class, 'destroyMonitoring'])->name('monitoring.destroy');
        
        // Vaccination Schedule
        Route::get('/vaccination-schedule', [HealthController::class, 'vaccinationSchedule'])->name('vaccination');

        // Vaccination Schedule 
        Route::get('/vaccination-schedule/create',[HealthController::class, 'createVaccineSchedule'])->name('vaccine_create');
        Route::post('/vaccination-schedule',[HealthController::class, 'storeVaccineSchedule']) ->name('vaccine_store');
        
        // Vaccine show
        Route::get('/vaccination-schedule/{schedule}', [HealthController::class, 'showVaccineSchedule'])->name('vaccinations.show');

        // Vaccination Actions 
        Route::post('/vaccination-schedule/{schedule}/complete', [HealthController::class, 'markVaccineComplete'])->name('vaccinations.mark_complete');
        
        // Vaccination delete
        Route::delete('/vaccination-schedule/{schedule}', [HealthController::class, 'destroy'])->name('vaccinations.destroy');

        // Euthanasia Logs
        Route::get('/euthanasia', [HealthController::class, 'euthanasiaLogs'])->name('euthanasia');
        Route::get('/euthanasia/create', [HealthController::class, 'createEuthanasia'])->name('euthanasia.create');
        Route::post('/euthanasia', [HealthController::class, 'storeEuthanasia'])->name('euthanasia.store');
        Route::get('/euthanasia/{log}', [HealthController::class, 'showEuthanasia'])->name('euthanasia.show');
        Route::get('/euthanasia/{log}/edit', [HealthController::class, 'editEuthanasia'])->name('euthanasia.edit');
        Route::put('/euthanasia/{log}', [HealthController::class, 'updateEuthanasia'])->name('euthanasia.update');
        Route::delete('/euthanasia/{log}', [HealthController::class, 'destroyEuthanasia'])->name('euthanasia.destroy');
    });

    // Donations
    Route::resource('donations', AdminDonationController::class)->except(['create', 'edit', 'update']);
    

});

    // =========================================================================
    // 🔧 DEVELOPER ROUTES
    // =========================================================================
    Route::middleware(['auth', 'developer'])->prefix('developer')->name('developer.')->group(function () {

        // Dashboard Landing Page

        // User Management (Developer-only)
    Route::resource('users', UserController::class)->only(['index', 'destroy']);
        
    // Custom route for updating role/status
    Route::put('/users/{user}/update-role', [UserController::class, 'updateRole'])->name('users.update-role');

    });
