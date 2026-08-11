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

    public function notifications(Request $request)
    {
        $user = $request->user();
        $query = Booking::query()->where('status', 'accepted');

        if ($user->role === 'counselor') {
            $query->whereHas('counselor', fn ($counselor) => $counselor->where('user_id', $user->id));
        } else {
            $query->where('user_id', $user->id);
        }

        $bookings = $query->withCount(['messages as unread_messages_count' => fn ($messages) => $messages
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
        ])->get(['id']);

        $latestUnread = \App\Models\BookingMessage::query()
            ->whereIn('booking_id', $bookings->pluck('id'))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->latest('id')
            ->first(['id', 'booking_id', 'body']);

        return response()->json([
            'total' => $bookings->sum('unread_messages_count'),
            'bookings' => $bookings->mapWithKeys(fn ($booking) => [$booking->id => $booking->unread_messages_count]),
            'latest' => $latestUnread ? [
                'id' => $latestUnread->id,
                'booking_id' => $latestUnread->booking_id,
                'preview' => mb_strimwidth($latestUnread->body, 0, 80, '…'),
            ] : null,
        ]);
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
