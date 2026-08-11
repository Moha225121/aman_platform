<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CounselorController extends Controller
{
    public function dashboard(Request $request)
    {
        abort_unless($request->user()->role === 'counselor' && $request->user()->counselor, 403);
        $counselor=$request->user()->counselor;
        $bookings=$counselor->bookings()->with(['user','service','supportProgram'])->withCount(['messages as unread_messages_count'=>fn($messages)=>$messages->where('sender_id','!=',$request->user()->id)->whereNull('read_at')])->orderByRaw('scheduled_at IS NULL')->orderBy('scheduled_at')->latest('created_at')->get();
        return view('counselor.dashboard',compact('counselor','bookings'));
    }
}
