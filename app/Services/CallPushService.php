<?php

namespace App\Services;

use App\Models\{Booking, PushSubscription, User};
use Minishlink\WebPush\{Subscription, WebPush};

class CallPushService
{
    public function sendIncomingCall(Booking $booking, User $caller): void
    {
        if (!config('services.webpush.public_key') || !config('services.webpush.private_key')) return;
        $recipientId = $booking->user_id === $caller->id ? $booking->counselor?->user_id : $booking->user_id;
        if (!$recipientId) return;

        $push = new WebPush(['VAPID' => [
            'subject' => config('services.webpush.subject'),
            'publicKey' => config('services.webpush.public_key'),
            'privateKey' => config('services.webpush.private_key'),
        ]], ['TTL' => 60, 'urgency' => 'high', 'topic' => 'call-'.$booking->id]);
        $payload = json_encode([
            'type' => 'incoming-call', 'booking_id' => $booking->id,
            'title' => 'مكالمة واردة عبر أمان',
            'body' => 'مكالمة مشفرة من '.($caller->role === 'counselor' ? $booking->counselor->name : $caller->alias),
            'url' => route('bookings.chat', $booking).'?incoming_call=1',
        ], JSON_UNESCAPED_UNICODE);

        foreach (PushSubscription::where('user_id', $recipientId)->get() as $stored) {
            $push->queueNotification(new Subscription($stored->endpoint, $stored->public_key, $stored->auth_token, $stored->content_encoding), $payload);
        }
        foreach ($push->flush() as $report) {
            if ($report->isSubscriptionExpired()) PushSubscription::where('endpoint', $report->getEndpoint())->delete();
        }
    }
}
