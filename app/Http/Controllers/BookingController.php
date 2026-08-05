<?php
namespace App\Http\Controllers;
use App\Models\Booking;
use Illuminate\Http\Request;
class BookingController extends Controller
{
    public function store(Request $request){$data=$request->validate(['counselor_id'=>'nullable|exists:counselors,id','service_id'=>'nullable|exists:services,id','support_program_id'=>'nullable|exists:support_programs,id','session_method'=>'required|in:in_person,online','note'=>'nullable|string|max:1000']);$request->user()->bookings()->create($data);return back()->with('success','تم إرسال طلبك وسنبلغك عند مراجعته.');}
}
