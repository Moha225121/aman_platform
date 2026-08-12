<?php

namespace App\Http\Controllers;

use App\Models\{Booking, CallSignal};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingCallController extends Controller
{
    public function signals(Request $request, Booking $booking)
    {
        $this->authorizeParticipant($request, $booking);
        $after = max(0, (int) $request->query('after', 0));

        CallSignal::where('created_at', '<', now()->subMinutes(10))->delete();

        return response()->json([
            'signals' => CallSignal::query()
                ->where('booking_id', $booking->id)
                ->where('sender_id', '!=', $request->user()->id)
                ->where('id', '>', $after)
                ->orderBy('id')
                ->limit(100)
                ->get()
                ->map(fn (CallSignal $signal) => [
                    'id' => $signal->id,
                    'type' => $signal->type,
                    'payload' => $signal->payload,
                ]),
        ]);
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorizeParticipant($request, $booking);
        $data = $request->validate([
            'type' => ['required', Rule::in(['invite', 'accept', 'offer', 'answer', 'ice', 'decline', 'hangup'])],
            'payload' => ['nullable', 'array'],
        ]);

        $encodedPayload = json_encode($data['payload'] ?? []);
        abort_if(strlen($encodedPayload) > 100000, 422, 'Call signal is too large.');

        $signal = CallSignal::create([
            'booking_id' => $booking->id,
            'sender_id' => $request->user()->id,
            'type' => $data['type'],
            'payload' => $data['payload'] ?? [],
            'created_at' => now(),
        ]);

        return response()->json(['id' => $signal->id], 201);
    }

    private function authorizeParticipant(Request $request, Booking $booking): void
    {
        $user = $request->user();
        $isCounselor = $booking->counselor?->user_id === $user->id;
        abort_unless(
            $booking->status === 'accepted'
            && $booking->session_method === 'online'
            && ($booking->user_id === $user->id || $isCounselor),
            403
        );
    }
}
