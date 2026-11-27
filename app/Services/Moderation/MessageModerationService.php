<?php

namespace App\Services\Moderation;

class MessageModerationService
{
    public const ALLOW = 'ALLOW';

    public const BLOCK = 'BLOCK';

    public const UNAVAILABLE = 'UNAVAILABLE';

    public const EXTERNAL_COMMUNICATION = 'external_communication';

    public const SERVICE_UNAVAILABLE = 'service_unavailable';

    public function __construct(
        private readonly ContactPatternDetector $detector,
        private readonly OllamaModerationClient $ollama,
    ) {
    }

    /**
     * @return array{
     *     status: string,
     *     reason: string|null
     * }
     */
    public function moderate(string $message): array
    {
        // Step 1: Detect explicit contact information.
        $patternViolation = $this->detector->detect($message);

        // Step 2: Always classify the message using Ollama.
        $aiDecision = $this->ollama->classify($message);

        // Step 3: Explicit contact violations take priority.
        if ($patternViolation !== null) {
            return [
                'status' => self::BLOCK,
                'reason' => $patternViolation,
            ];
        }

        // Step 4: Check AI-based violations.
        if ($aiDecision === OllamaModerationClient::BLOCK) {
            return [
                'status' => self::BLOCK,
                'reason' => self::EXTERNAL_COMMUNICATION,
            ];
        }

        // Step 5: Allow messages that pass both checks.
        if ($aiDecision === OllamaModerationClient::ALLOW) {
            return [
                'status' => self::ALLOW,
                'reason' => null,
            ];
        }

        // Step 6: Handle unavailable moderation service.
        return [
            'status' => self::UNAVAILABLE,
            'reason' => self::SERVICE_UNAVAILABLE,
        ];
    }
}