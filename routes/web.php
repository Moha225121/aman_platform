<?php

use App\Http\Controllers\{AdminController,AuthController,BookingCallController,BookingController,BookingChatController,CompanionController,CounselorController,PushSubscriptionController};
use App\Models\{Counselor,Service,SupportProgram};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', fn () => view('welcome', [
    'services' => Schema::hasTable('services') ? Service::where('active', true)->get() : collect(),
    'programs' => Schema::hasTable('support_programs') ? SupportProgram::where('active', true)->get() : collect(),
    'counselors' => Schema::hasTable('counselors') ? Counselor::where('active', true)->get() : collect(),
]));
Route::get('/login', fn () => redirect('/?auth=login'));
Route::get('/register', fn () => redirect('/?auth=register'));
Route::get('/logout', fn () => redirect('/'));
Route::post('/register', [AuthController::class,'register'])->name('register');
Route::post('/login', [AuthController::class,'login'])->name('login');
Route::post('/logout', [AuthController::class,'logout'])->middleware('auth')->name('logout');
Route::get('/dashboard', fn () => view('dashboard',[
    'bookings'=>auth()->user()->bookings()->with(['counselor','service','supportProgram'])->withCount(['messages as unread_messages_count'=>fn($messages)=>$messages->where('sender_id','!=',auth()->id())->whereNull('read_at')])->latest()->get(),
    'counselors'=>Counselor::where('active',true)->orderByDesc('rating')->get(),
    'services'=>Service::where('active',true)->get(),
    'programs'=>SupportProgram::where('active',true)->get(),
]))->middleware('auth')->name('dashboard');
Route::post('/bookings',[BookingController::class,'store'])->middleware('auth')->name('bookings.store');
Route::middleware('auth')->group(function () {
    Route::get('/bookings/{booking}/chat',[BookingChatController::class,'show'])->name('bookings.chat');
    Route::get('/bookings/{booking}/messages',[BookingChatController::class,'messages'])->name('bookings.messages');
    Route::post('/bookings/{booking}/messages',[BookingChatController::class,'store'])->middleware('throttle:30,1')->name('bookings.messages.store');
    Route::get('/bookings/{booking}/call/signals',[BookingCallController::class,'signals'])->name('bookings.call.signals');
    Route::post('/bookings/{booking}/call/signals',[BookingCallController::class,'store'])->middleware('throttle:180,1')->name('bookings.call.signals.store');
    Route::get('/chat/notifications',[BookingChatController::class,'notifications'])->name('chat.notifications');
    Route::get('/push/config',[PushSubscriptionController::class,'config'])->name('push.config');
    Route::post('/push/subscriptions',[PushSubscriptionController::class,'store'])->name('push.subscriptions.store');
});
Route::post('/companion/message',[CompanionController::class,'message'])->middleware(['auth','throttle:20,1'])->name('companion.message');
Route::get('/counselor',[CounselorController::class,'dashboard'])->middleware('auth')->name('counselor.dashboard');
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function(){
    Route::get('/',[AdminController::class,'index'])->name('dashboard');
    Route::patch('/bookings/{booking}',[AdminController::class,'booking'])->name('bookings.update');
    Route::post('/counselors',[AdminController::class,'storeCounselor'])->name('counselors.store');
    Route::patch('/counselors/{counselor}',[AdminController::class,'updateCounselor'])->name('counselors.update');
    Route::delete('/counselors/{counselor}',[AdminController::class,'destroyCounselor'])->name('counselors.destroy');
});
