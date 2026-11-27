<?php

namespace Tests\Feature;

use App\Events\InboxMessageReceived;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class MessageModerationTest extends TestCase
{
    use RefreshDatabase {
        refreshDatabase as private runSafeDatabaseRefresh;
    }

    private const OLLAMA_ENDPOINT =
    'http://127.0.0.1:11434/api/chat';

    private RealEstateUser $tenant;

    private RealEstateUser $agent;

    private Conversation $conversation;

    public function refreshDatabase(): void
    {
        // Protect the local development database.
        if (
            config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || filled(config('database.connections.sqlite.url'))
        ) {
            throw new RuntimeException(
                'Message moderation tests require an in-memory SQLite database.'
            );
        }

        $this->runSafeDatabaseRefresh();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config([
            'moderation.ollama.url' => 'http://127.0.0.1:11434',
            'moderation.ollama.model' => 'qwen3.5:4b',
            'moderation.ollama.think' => false,
        ]);

        Http::preventStrayRequests();

        $this->tenant = $this->account('tenant');
        $this->agent = $this->account('agent');

        $this->conversation = $this->createConversation(
            $this->tenant,
            $this->agent
        );

        Event::fake([
            MessageSent::class,
            InboxMessageReceived::class,
        ]);
    }

    private function account(string $role): RealEstateUser
    {
        return RealEstateUser::forceCreate([
            'role' => $role,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'test-password',
        ]);
    }

    private function createConversation(
        RealEstateUser $tenant,
        RealEstateUser $agent
    ): Conversation {
        $property = Property::forceCreate([
            'agent_id' => $agent->id,
            'title' => 'Test apartment',
            'description' => 'Test property',
            'category' => 'apartment',
            'price' => 350,
            'area_sqm' => 120,
            'city' => 'Amman',
            'address' => 'Test address',
            'status' => 'available',
            'verification_status' => 'verified',
            'ownership_deed_url' => 'test.pdf',
        ]);

        return Conversation::forceCreate([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'agent_id' => $agent->id,
        ]);
    }

    private function endpoint(
        ?Conversation $conversation = null
    ): string {
        return route(
            'conversations.messages.store',
            $conversation ?? $this->conversation
        );
    }

    private function fakeOllama(string $decision): void
    {
        Http::fake([
            self::OLLAMA_ENDPOINT => Http::response([
                'message' => [
                    'role' => 'assistant',
                    'content' => $decision,
                ],
                'done' => true,
                'done_reason' => 'stop',
            ], 200),
        ]);
    }

    private function assertMessageWasNotSent(): void
    {
        $this->assertDatabaseCount('messages', 0);

        $this->assertNull(
            $this->conversation->fresh()->last_message_at
        );

        Event::assertNotDispatched(MessageSent::class);

        Event::assertNotDispatched(
            InboxMessageReceived::class
        );
    }

    // ---------------------------------------------------------
    // Allowed messages
    // ---------------------------------------------------------

    #[DataProvider('allowedMessages')]
    public function test_participant_can_send_allowed_message(
        string $role,
        string $body
    ): void {
        $this->fakeOllama('ALLOW');

        $sender = $role === 'tenant'
            ? $this->tenant
            : $this->agent;

        $this->actingAs($sender)
            ->postJson($this->endpoint(), [
                'body' => $body,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'sender_id' => $sender->id,
            'body' => $body,
        ]);

        $this->assertDatabaseCount('messages', 1);

        $this->assertNotNull(
            $this->conversation->fresh()->last_message_at
        );

        Event::assertDispatched(MessageSent::class);

        Event::assertDispatched(
            InboxMessageReceived::class
        );

        Http::assertSentCount(1);
    }

    public static function allowedMessages(): array
    {
        return [
            'tenant sends normal message' => [
                'tenant',
                'مرحبا، الشقة لسا متوفرة؟',
            ],

            'agent sends property price' => [
                'agent',
                'الإيجار 350 دينار بالشهر',
            ],
        ];
    }

    // ---------------------------------------------------------
    // Blocked messages
    // ---------------------------------------------------------

    #[DataProvider('blockedMessages')]
    public function test_violating_messages_are_blocked(
        string $role,
        string $body,
        string $aiDecision,
        string $expectedReason
    ): void {
        $this->fakeOllama($aiDecision);

        $sender = $role === 'tenant'
            ? $this->tenant
            : $this->agent;

        $this->actingAs($sender)
            ->postJson($this->endpoint(), [
                'body' => $body,
            ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'code',
                'MESSAGE_MODERATION_BLOCKED'
            )
            ->assertJsonPath(
                'reason',
                $expectedReason
            )
            ->assertJsonValidationErrors('body');

        $this->assertMessageWasNotSent();

        Http::assertSentCount(1);
    }

    public static function blockedMessages(): array
    {
        return [
            'tenant shares phone number' => [
                'tenant',
                'رن علي 0791234567',
                'BLOCK',
                'phone_number',
            ],

            'agent shares email address' => [
                'agent',
                'راسلني على test@example.com',
                'BLOCK',
                'email_address',
            ],

            'social media username' => [
                'tenant',
                '@example_user',
                'BLOCK',
                'social_contact',
            ],

            'AI detects external communication' => [
                'agent',
                'خلينا نحكي برا التطبيق',
                'BLOCK',
                'external_communication',
            ],

            'Regex blocks despite AI allowing' => [
                'tenant',
                'رقمي 0791234567',
                'ALLOW',
                'phone_number',
            ],
        ];
    }

    // ---------------------------------------------------------
    // Ollama unavailable
    // ---------------------------------------------------------

    public function test_message_is_not_sent_when_ollama_is_unavailable(): void
    {
        Http::fake([
            self::OLLAMA_ENDPOINT => Http::response([
                'error' => 'Service unavailable',
            ], 503),
        ]);

        $this->actingAs($this->tenant)
            ->postJson($this->endpoint(), [
                'body' => 'هل الشقة متوفرة؟',
            ])
            ->assertStatus(503)
            ->assertJsonPath(
                'code',
                'MESSAGE_MODERATION_UNAVAILABLE'
            )
            ->assertJsonPath(
                'reason',
                'service_unavailable'
            );

        $this->assertMessageWasNotSent();

        Http::assertSentCount(1);
    }

    // ---------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------

    public function test_non_participant_cannot_send_messages(): void
    {
        $this->fakeOllama('ALLOW');

        $outsider = $this->account('tenant');

        $this->actingAs($outsider)
            ->postJson($this->endpoint(), [
                'body' => 'Hello',
            ])
            ->assertForbidden();

        $this->assertMessageWasNotSent();

        Http::assertNothingSent();
    }

    public function test_guest_cannot_send_messages(): void
    {
        $this->fakeOllama('ALLOW');

        $this->postJson($this->endpoint(), [
            'body' => 'Hello',
        ])->assertUnauthorized();

        $this->assertMessageWasNotSent();

        Http::assertNothingSent();
    }

    // ---------------------------------------------------------
    // Validation
    // ---------------------------------------------------------

    #[DataProvider('invalidMessages')]
    public function test_invalid_messages_are_rejected_before_ollama(
        string $body
    ): void {
        $this->fakeOllama('ALLOW');

        $this->actingAs($this->tenant)
            ->postJson($this->endpoint(), [
                'body' => $body,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');

        $this->assertMessageWasNotSent();

        Http::assertNothingSent();
    }

    public static function invalidMessages(): array
    {
        return [
            'empty message' => [''],

            'message exceeds maximum length' => [
                str_repeat('a', 2001),
            ],
        ];
    }

    public function test_cannot_reply_to_message_from_another_conversation(): void
    {
        $this->fakeOllama('ALLOW');

        $otherTenant = $this->account('tenant');

        $otherConversation = $this->createConversation(
            $otherTenant,
            $this->agent
        );

        $otherMessage = $otherConversation->messages()->make([
            'body' => 'Existing message',
        ]);

        $otherMessage->sender()->associate($otherTenant);

        $otherMessage->save();

        $this->actingAs($this->tenant)
            ->postJson($this->endpoint(), [
                'body' => 'My reply',
                'reply_to_id' => $otherMessage->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reply_to_id');

        $this->assertDatabaseCount('messages', 1);

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $this->conversation->id,
            'body' => 'My reply',
        ]);

        $this->assertNull(
            $this->conversation->fresh()->last_message_at
        );

        Event::assertNotDispatched(MessageSent::class);

        Event::assertNotDispatched(
            InboxMessageReceived::class
        );

        Http::assertNothingSent();
    }

    // ---------------------------------------------------------
    // Admin support
    // ---------------------------------------------------------

    #[DataProvider('supportParticipants')]
    public function test_admin_support_conversation_is_excluded(
        string $senderRole
    ): void {
        $this->fakeOllama('BLOCK');

        $admin = $this->account('admin');

        $support = Conversation::forceCreate([
            'property_id' => null,
            'tenant_id' => $this->tenant->id,
            'agent_id' => $admin->id,
        ]);

        $sender = $senderRole === 'admin'
            ? $admin
            : $this->tenant;

        $body = 'Contact number: 0791234567';

        $this->actingAs($sender)
            ->postJson($this->endpoint($support), [
                'body' => $body,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $support->id,
            'sender_id' => $sender->id,
            'body' => $body,
        ]);

        Http::assertNothingSent();

        Event::assertDispatched(MessageSent::class);

        Event::assertDispatched(
            InboxMessageReceived::class
        );
    }

    public static function supportParticipants(): array
    {
        return [
            'admin sends support message' => ['admin'],

            'tenant replies to admin' => ['tenant'],
        ];
    }

    // ---------------------------------------------------------
    // Localization
    // ---------------------------------------------------------

    #[DataProvider('moderationLanguages')]
    public function test_violation_message_uses_website_language(
        string $locale,
        string $expectedMessage
    ): void {
        $this->fakeOllama('BLOCK');

        $this->actingAs($this->tenant)
            ->withSession([
                'locale' => $locale,
            ])
            ->postJson($this->endpoint(), [
                'body' => '0791234567',
            ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                $expectedMessage
            )
            ->assertJsonPath(
                'errors.body.0',
                $expectedMessage
            );

        $this->assertMessageWasNotSent();

        Http::assertSentCount(1);
    }

    public static function moderationLanguages(): array
    {
        return [
            'English website' => [
                'en',
                'Sharing phone numbers is not allowed. Please edit your message.',
            ],

            'Arabic website' => [
                'ar',
                'يمنع مشاركة أرقام الهواتف. يرجى تعديل رسالتك.',
            ],
        ];
    }

    // ---------------------------------------------------------
    // Original conversation functionality
    // ---------------------------------------------------------

    public function test_allowed_reply_preserves_original_conversation_flow(): void
    {
        $this->fakeOllama('ALLOW');

        $originalMessage = $this->conversation
            ->messages()
            ->make([
                'body' => 'Is the apartment available?',
            ]);

        $originalMessage->sender()->associate(
            $this->tenant
        );

        $originalMessage->save();

        $this->actingAs($this->agent)
            ->postJson($this->endpoint(), [
                'body' => 'Yes, it is available.',
                'reply_to_id' => $originalMessage->id,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->agent->id,
            'reply_to_id' => $originalMessage->id,
            'body' => 'Yes, it is available.',
        ]);

        $this->assertDatabaseCount('messages', 2);

        Event::assertDispatched(MessageSent::class);

        Event::assertDispatched(
            InboxMessageReceived::class
        );

        Http::assertSentCount(1);
    }

    public function test_sender_can_correct_and_resend_blocked_message(): void
    {
        Http::fake([
            self::OLLAMA_ENDPOINT => Http::sequence()
                ->push([
                    'message' => ['content' => 'BLOCK'],
                    'done' => true,
                ], 200)
                ->push([
                    'message' => ['content' => 'ALLOW'],
                    'done' => true,
                ], 200),
        ]);

        $this->actingAs($this->tenant);

        $this->postJson($this->endpoint(), [
            'body' => 'ابعثلي رقمك',
        ])->assertUnprocessable();

        $this->assertMessageWasNotSent();

        $correctedBody = 'ممكن نتفق على موعد المعاينة هون؟';

        $this->postJson($this->endpoint(), [
            'body' => $correctedBody,
        ])->assertCreated();

        $this->assertDatabaseCount('messages', 1);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->tenant->id,
            'body' => $correctedBody,
        ]);

        $this->assertDatabaseMissing('messages', [
            'body' => 'ابعثلي رقمك',
        ]);

        Event::assertDispatchedTimes(
            MessageSent::class,
            1
        );

        Event::assertDispatchedTimes(
            InboxMessageReceived::class,
            1
        );

        Http::assertSentCount(2);
    }
}
