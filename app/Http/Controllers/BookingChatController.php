<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingChatController extends Controller
{
    public function show(Request $request, Booking $booking)
    {
        $this->authorizeParticipant($request, $booking);
        $booking->load(['user', 'counselor.user', 'service', 'supportProgram']);
        $this->markRead($request, $booking);
        return view('bookings.chat', compact('booking'));
    }

    public function messages(Request $request, Booking $booking)
    {
        $this->authorizeParticipant($request, $booking);
        $this->markRead($request, $booking);
        $messages = $booking->messages()->with('sender:id,alias,role')->orderBy('id')->get()->map(fn ($message) => [
            'id' => $message->id,
            'body' => $message->body,
            'mine' => $message->sender_id === $request->user()->id,
            'sender' => $message->sender->role === 'counselor' ? $booking->counselor->name : $message->sender->alias,
            'time' => $message->created_at->translatedFormat('h:i A'),
        ]);
        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorizeParticipant($request, $booking);
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $message = $booking->messages()->create(['sender_id' => $request->user()->id, 'body' => trim($data['body'])]);
        return response()->json(['id' => $message->id], 201);
    }

    private function authorizeParticipant(Request $request, Booking $booking): void
    {
        $user = $request->user();
        $isCounselor = $booking->counselor?->user_id === $user->id;
        abort_unless($booking->status === 'accepted' && ($booking->user_id === $user->id || $isCounselor), 403);
    }

    private function markRead(Request $request, Booking $booking): void
    {
        $booking->messages()->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
    }
}
