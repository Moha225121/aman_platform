<?php

namespace Tests\Feature;

use App\Models\{Booking, Counselor, User};
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
}
