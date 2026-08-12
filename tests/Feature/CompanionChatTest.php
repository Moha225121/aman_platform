<?php

namespace Tests\Feature;

use App\Models\{SupportProgram,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CompanionChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_send_a_companion_message(): void
    {
        $this->postJson(route('companion.message'), ['message' => 'Hello'])
            ->assertRedirect(route('login'));
    }

    public function test_only_clients_can_use_the_companion(): void
    {
        $user = User::factory()->create(['role' => 'counselor']);
        $this->actingAs($user)->postJson(route('companion.message'), ['message' => 'Hello'])->assertForbidden();
    }

    public function test_it_returns_the_openai_reply(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        config()->set('services.openai.model', 'gpt-5.6-sol');
        Http::fake([
            'api.openai.com/v1/embeddings' => Http::response(['data' => []]),
            'api.openai.com/v1/responses' => Http::response(['output_text' => 'رد داعم وآمن']),
        ]);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->postJson(route('companion.message'), [
            'message' => 'أشعر بالقلق',
            'history' => [['role' => 'assistant', 'content' => 'كيف يمكنني مساعدتك؟']],
        ])->assertOk()->assertJsonPath('reply', 'رد داعم وآمن');

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://api.openai.com/v1/responses'
            && $request['model'] === 'gpt-5.6-sol'
            && $request['store'] === false
            && count($request['input']) === 2
        );
    }

    public function test_it_gives_the_model_only_active_support_programs(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        Http::fake([
            'api.openai.com/v1/embeddings' => Http::response(['data' => []]),
            'api.openai.com/v1/responses' => Http::response(['output_text' => 'البرنامج الأنسب لاحتياجك هو برنامج دعم الأسرة.']),
        ]);
        SupportProgram::create(['name' => 'برنامج دعم الأسرة', 'description' => 'دعم متخصص للأسرة.', 'active' => true]);
        SupportProgram::create(['name' => 'برنامج متوقف', 'description' => 'غير متاح.', 'active' => false]);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->postJson(route('companion.message'), [
            'message' => 'أريد معرفة البرنامج المناسب لي',
        ])->assertOk();

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://api.openai.com/v1/responses') return false;

            return str_contains($request['instructions'], 'برنامج دعم الأسرة')
                && str_contains($request['instructions'], 'دعم متخصص للأسرة.')
                && !str_contains($request['instructions'], 'برنامج متوقف');
        });
    }

    public function test_provider_failure_returns_a_safe_error(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        Http::fake([
            'api.openai.com/v1/embeddings' => Http::response(['data' => []]),
            'api.openai.com/v1/responses' => Http::response(['error' => ['message' => 'upstream failure']], 500),
        ]);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->postJson(route('companion.message'), ['message' => 'أحتاج إلى دعم'])
            ->assertStatus(502)->assertJsonStructure(['message']);
    }
}
