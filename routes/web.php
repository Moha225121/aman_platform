<?php

use App\Http\Controllers\{AdminController,AuthController,BookingController,CompanionController,CounselorController};
use App\Models\{Counselor,Service,SupportProgram};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', fn () => view('welcome', [
    'services' => Schema::hasTable('services') ? Service::where('active', true)->get() : collect(),
    'programs' => Schema::hasTable('support_programs') ? SupportProgram::where('active', true)->get() : collect(),
    'counselors' => Schema::hasTable('counselors') ? Counselor::where('active', true)->get() : collect(),
]));
Route::post('/register', [AuthController::class,'register'])->name('register');
Route::post('/login', [AuthController::class,'login'])->name('login');
Route::post('/logout', [AuthController::class,'logout'])->middleware('auth')->name('logout');
Route::get('/dashboard', fn () => view('dashboard',[
    'bookings'=>auth()->user()->bookings()->with(['counselor','service','supportProgram'])->latest()->get(),
    'counselors'=>Counselor::where('active',true)->orderByDesc('rating')->get(),
    'services'=>Service::where('active',true)->get(),
    'programs'=>SupportProgram::where('active',true)->get(),
]))->middleware('auth')->name('dashboard');
Route::post('/bookings',[BookingController::class,'store'])->middleware('auth')->name('bookings.store');
Route::post('/companion/message',[CompanionController::class,'message'])->middleware(['auth','throttle:20,1'])->name('companion.message');
Route::get('/counselor',[CounselorController::class,'dashboard'])->middleware('auth')->name('counselor.dashboard');
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function(){
    Route::get('/',[AdminController::class,'index'])->name('dashboard');
    Route::patch('/bookings/{booking}',[AdminController::class,'booking'])->name('bookings.update');
    Route::post('/counselors',[AdminController::class,'storeCounselor'])->name('counselors.store');
    Route::patch('/counselors/{counselor}',[AdminController::class,'updateCounselor'])->name('counselors.update');
    Route::delete('/counselors/{counselor}',[AdminController::class,'destroyCounselor'])->name('counselors.destroy');
});
