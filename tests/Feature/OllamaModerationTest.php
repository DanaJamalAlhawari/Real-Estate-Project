<?php

namespace Tests\Feature;

use App\Services\Moderation\ContactPatternDetector;
use App\Services\Moderation\MessageModerationService;
use App\Services\Moderation\OllamaModerationClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OllamaModerationTest extends TestCase
{
    private const OLLAMA_ENDPOINT =
        'http://127.0.0.1:11434/api/chat';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'moderation.ollama.url' => 'http://127.0.0.1:11434',
            'moderation.ollama.model' => 'qwen3.5:4b',
            'moderation.ollama.think' => false,
            'moderation.ollama.temperature' => 0,
            'moderation.ollama.num_predict' => 64,
            'moderation.ollama.keep_alive' => '15m',
        ]);

        Http::preventStrayRequests();
    }

    private function fakeOllamaDecision(string $decision): void
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

    public function test_client_sends_correct_payload(): void
    {
        $this->fakeOllamaDecision('ALLOW');

        $client = app(OllamaModerationClient::class);

        $message = 'سعر الشقة 85000 دينار';

        $this->assertSame(
            'ALLOW',
            $client->classify($message)
        );

        Http::assertSent(function (Request $request) use ($message) {
            $data = $request->data();

            return $request->url() === self::OLLAMA_ENDPOINT
                && $request->method() === 'POST'
                && $data['model'] === 'qwen3.5:4b'
                && $data['stream'] === false
                && $data['think'] === false
                && $data['keep_alive'] === '15m'
                && $data['messages'][0]['role'] === 'system'
                && ! empty($data['messages'][0]['content'])
                && $data['messages'][1]['role'] === 'user'
                && $data['messages'][1]['content'] === $message
                && $data['options']['temperature'] === 0
                && $data['options']['num_predict'] === 64;
        });

        Http::assertSentCount(1);
    }

    public function test_client_supports_a_different_model(): void
    {
        config([
            'moderation.ollama.model' => 'qwen3:8b',
            'moderation.ollama.think' => null,
        ]);

        $this->fakeOllamaDecision('ALLOW');

        $client = app(OllamaModerationClient::class);

        $this->assertSame(
            'ALLOW',
            $client->classify('Is the apartment available?')
        );

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return $data['model'] === 'qwen3:8b'
                && ! array_key_exists('think', $data);
        });

        Http::assertSentCount(1);
    }

    #[DataProvider('invalidResponses')]
    public function test_client_rejects_invalid_responses(
        array $response,
        int $status
    ): void {
        Http::fake([
            self::OLLAMA_ENDPOINT => Http::response(
                $response,
                $status
            ),
        ]);

        $client = app(OllamaModerationClient::class);

        $this->assertNull(
            $client->classify('مرحبا')
        );

        Http::assertSentCount(1);
    }

    public static function invalidResponses(): array
    {
        return [

            'server error' => [
                ['error' => 'Internal server error'],
                500,
            ],

            'model not installed' => [
                ['error' => 'Model not found'],
                404,
            ],

            'incomplete response' => [
                [
                    'done' => false,
                    'message' => ['content' => 'ALLOW'],
                ],
                200,
            ],

            'missing message content' => [
                ['done' => true],
                200,
            ],

            'unexpected model output' => [
                [
                    'done' => true,
                    'message' => [
                        'content' => 'This message is safe.',
                    ],
                ],
                200,
            ],

        ];
    }

    public function test_client_handles_connection_failure(): void
    {
        Http::fake(function () {
            throw new ConnectionException(
                'Ollama server is unavailable.'
            );
        });

        $client = app(OllamaModerationClient::class);

        $this->assertNull(
            $client->classify('مرحبا')
        );
    }

    #[DataProvider('moderationDecisions')]
    public function test_moderation_service_decisions(
        string $message,
        ?string $aiDecision,
        string $expectedStatus,
        ?string $expectedReason
    ): void {
        if ($aiDecision === null) {
            Http::fake([
                self::OLLAMA_ENDPOINT => Http::response([
                    'error' => 'Service unavailable',
                ], 503),
            ]);
        } else {
            $this->fakeOllamaDecision($aiDecision);
        }

        $service = app(MessageModerationService::class);

        $result = $service->moderate($message);

        $this->assertSame([
            'status' => $expectedStatus,
            'reason' => $expectedReason,
        ], $result);

        // Every message must be checked by Ollama,
        // including messages already detected by Regex.
        Http::assertSentCount(1);
    }

    public static function moderationDecisions(): array
    {
        return [

            'both checks allow message' => [
                'سعر الشقة 85000 دينار',
                'ALLOW',
                MessageModerationService::ALLOW,
                null,
            ],

            'AI detects external communication' => [
                'خلينا نحكي برا التطبيق',
                'BLOCK',
                MessageModerationService::BLOCK,
                MessageModerationService::EXTERNAL_COMMUNICATION,
            ],

            'Regex blocks despite AI allowing' => [
                'رن علي 0791234567',
                'ALLOW',
                MessageModerationService::BLOCK,
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'both checks detect a violation' => [
                'راسلني على test@example.com',
                'BLOCK',
                MessageModerationService::BLOCK,
                ContactPatternDetector::EMAIL_ADDRESS,
            ],

            'Regex detects violation while Ollama unavailable' => [
                '@example_user',
                null,
                MessageModerationService::BLOCK,
                ContactPatternDetector::SOCIAL_CONTACT,
            ],

            'Ollama unavailable without Regex violation' => [
                'مرحبا، الشقة متوفرة؟',
                null,
                MessageModerationService::UNAVAILABLE,
                MessageModerationService::SERVICE_UNAVAILABLE,
            ],

        ];
    }
}