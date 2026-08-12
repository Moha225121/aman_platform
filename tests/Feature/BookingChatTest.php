<?php

namespace Tests\Feature;

use App\Models\{Booking, BookingMessage, Counselor, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_participants_can_chat_after_booking_is_accepted(): void
    {
        $client=User::factory()->create(['role'=>'user','alias'=>'مسترشد']);
        $counselorUser=User::factory()->create(['role'=>'counselor','alias'=>'مرشد']);
        $other=User::factory()->create(['role'=>'user']);
        $counselor=Counselor::create(['user_id'=>$counselorUser->id,'name'=>'مرشد الاختبار','title'=>'مرشد نفسي','rating'=>5]);
        $booking=Booking::create(['user_id'=>$client->id,'counselor_id'=>$counselor->id,'status'=>'accepted','session_method'=>'online']);

        $this->actingAs($client)->get(route('bookings.chat',$booking))->assertOk();
        $this->actingAs($counselorUser)->postJson(route('bookings.messages.store',$booking),['body'=>'مرحبًا بك'])->assertCreated();
        $this->actingAs($client)->getJson(route('bookings.messages',$booking))->assertOk()->assertJsonPath('messages.0.body','مرحبًا بك');
        $this->actingAs($other)->get(route('bookings.chat',$booking))->assertForbidden();

        $booking->update(['status'=>'pending']);
        $this->actingAs($client)->get(route('bookings.chat',$booking))->assertForbidden();
    }

    public function test_notifications_report_unread_messages_and_opening_chat_marks_them_read(): void
    {
        $client = User::factory()->create(['role' => 'user']);
        $counselorUser = User::factory()->create(['role' => 'counselor']);
        $counselor = Counselor::create([
            'user_id' => $counselorUser->id,
            'name' => 'Test counselor',
            'title' => 'Counselor',
            'rating' => 5,
        ]);
        $booking = Booking::create([
            'user_id' => $client->id,
            'counselor_id' => $counselor->id,
            'status' => 'accepted',
            'session_method' => 'online',
        ]);
        $message = BookingMessage::create([
            'booking_id' => $booking->id,
            'sender_id' => $counselorUser->id,
            'body' => 'New message',
        ]);

        $this->actingAs($client)->getJson(route('chat.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath("bookings.{$booking->id}", 1)
            ->assertJsonPath('online_bookings.0', $booking->id)
            ->assertJsonPath('latest.id', $message->id)
            ->assertJsonPath('latest.preview', 'New message');

        $this->actingAs($client)->get(route('bookings.chat', $booking))->assertOk();
        $this->assertNotNull($message->fresh()->read_at);

        $this->actingAs($client)->getJson(route('chat.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath("bookings.{$booking->id}", 0)
            ->assertJsonPath('latest', null);
    }

    public function test_only_online_booking_participants_can_exchange_call_signals(): void
    {
        $client = User::factory()->create(['role' => 'user']);
        $counselorUser = User::factory()->create(['role' => 'counselor']);
        $outsider = User::factory()->create(['role' => 'user']);
        $counselor = Counselor::create([
            'user_id' => $counselorUser->id,
            'name' => 'Call counselor',
            'title' => 'Counselor',
            'rating' => 5,
        ]);
        $booking = Booking::create([
            'user_id' => $client->id,
            'counselor_id' => $counselor->id,
            'status' => 'accepted',
            'session_method' => 'online',
        ]);

        $this->actingAs($client)->postJson(route('bookings.call.signals.store', $booking), [
            'type' => 'offer',
            'payload' => ['description' => ['type' => 'offer', 'sdp' => 'test-sdp']],
        ])->assertCreated();

        $this->actingAs($counselorUser)->getJson(route('bookings.call.signals', $booking))
            ->assertOk()
            ->assertJsonPath('signals.0.type', 'offer')
            ->assertJsonPath('signals.0.payload.description.sdp', 'test-sdp');

        $this->actingAs($client)->getJson(route('bookings.call.signals', $booking))
            ->assertOk()->assertJsonCount(0, 'signals');
        $this->actingAs($outsider)->getJson(route('bookings.call.signals', $booking))->assertForbidden();

        $booking->update(['session_method' => 'in_person']);
        $this->actingAs($client)->postJson(route('bookings.call.signals.store', $booking), [
            'type' => 'invite',
        ])->assertForbidden();
    }

    public function test_authenticated_user_can_register_a_push_subscription(): void
    {
        $user = User::factory()->create();
        $endpoint = 'https://push.example.test/subscription/123';

        $this->actingAs($user)->postJson(route('push.subscriptions.store'), [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'public-key', 'auth' => 'auth-token'],
            'contentEncoding' => 'aes128gcm',
        ])->assertCreated();

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint_hash' => hash('sha256', $endpoint),
        ]);
    }
}
