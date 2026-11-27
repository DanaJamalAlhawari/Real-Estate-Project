<?php

namespace App\Services\Moderation;

class ContactPatternDetector
{
    public const PHONE_NUMBER = 'phone_number';

    public const EMAIL_ADDRESS = 'email_address';

    public const SOCIAL_CONTACT = 'social_contact';

    public function detect(string $message): ?string
    {
        $message = $this->normalize($message);

        if ($this->containsPhoneNumber($message)) {
            return self::PHONE_NUMBER;
        }

        if ($this->containsEmailAddress($message)) {
            return self::EMAIL_ADDRESS;
        }

        if ($this->containsSocialContact($message)) {
            return self::SOCIAL_CONTACT;
        }

        return null;
    }

    private function normalize(string $message): string
    {
        $message = strtr($message, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',

            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',

            '＠' => '@',
            '．' => '.',
            '＋' => '+',
            '–' => '-',
            '—' => '-',
            '−' => '-',
        ]);

        // Remove invisible formatting characters.
        $message = preg_replace(
            '/\p{Cf}/u',
            '',
            $message
        ) ?? $message;

        return trim($message);
    }

    private function containsPhoneNumber(string $message): bool
    {
        $separator = '[\p{Zs}\t().-]*';

        $patterns = [

            // Jordanian mobile numbers.
            "~(?<![0-9])0{$separator}7"
                . "(?:{$separator}[0-9]){8}(?![0-9])~u",

            // Jordanian landline numbers.
            "~(?<![0-9])0{$separator}[2356]"
                . "(?:{$separator}[0-9]){7}(?![0-9])~u",

            // Jordanian mobile numbers with country code.
            "~(?<![0-9])9{$separator}6{$separator}2"
                . "{$separator}7"
                . "(?:{$separator}[0-9]){8}(?![0-9])~u",

            // International phone numbers.
            "~(?<![0-9])(?:\+|0{$separator}0)"
                . "{$separator}[1-9]"
                . "(?:{$separator}[0-9]){7,14}(?![0-9])~u",

        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message) === 1) {
                return true;
            }
        }

        return false;
    }

    private function containsEmailAddress(string $message): bool
    {
        $patterns = [

            // Standard email addresses.
            '~[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}~iu',

            // Email addresses with spaces.
            '~[a-z0-9._%+\-]+\s*@\s*[a-z0-9\-]+'
                . '(?:\s*\.\s*[a-z0-9\-]+)*'
                . '\s*\.\s*[a-z]{2,}~iu',

            // Obfuscated email addresses.
            '~[a-z0-9._%+\-]+\s+at\s+[a-z0-9\-]+'
                . '(?:\s+dot\s+[a-z0-9\-]+)*'
                . '\s+dot\s+[a-z]{2,}~iu',

        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message) === 1) {
                return true;
            }
        }

        return false;
    }

    private function containsSocialContact(string $message): bool
    {
        $patterns = [

            // Social media usernames.
            '~(?<![\p{L}\p{N}._])@[\p{L}\p{N}_]'
                . '[\p{L}\p{N}._]{1,29}~u',

            // External social media and messaging links.
            '~(?<![\p{L}\p{N}.\-])'
                . '(?:https?://)?(?:www\.)?'
                . '(?:wa\.me|whatsapp\.com|api\.whatsapp\.com'
                . '|t\.me|telegram\.me|telegram\.org'
                . '|instagram\.com|facebook\.com'
                . '|fb\.me|m\.me|messenger\.com'
                . '|snapchat\.com|tiktok\.com'
                . '|discord\.gg|discord\.com'
                . '|x\.com|twitter\.com|linkedin\.com)'
                . '/[^\s]+~iu',

        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message) === 1) {
                return true;
            }
        }

        return false;
    }} 