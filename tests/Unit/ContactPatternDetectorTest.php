<?php

namespace Tests\Unit;

use App\Services\Moderation\ContactPatternDetector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContactPatternDetectorTest extends TestCase
{
    #[DataProvider('contactMessages')]
    public function test_detects_external_contact_information(
        string $message,
        string $expected
    ): void {
        $detector = new ContactPatternDetector();

        $this->assertSame(
            $expected,
            $detector->detect($message)
        );
    }

    public static function contactMessages(): array
    {
        return [

            'Jordanian mobile number' => [
                'رن علي 0791234567',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'phone number with spaces' => [
                '079 123 4567',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'phone number with hyphens' => [
                '079-123-4567',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'phone number with dots' => [
                '07.9.123.4567',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'Arabic digits' => [
                'رقمي ٠٧٩١٢٣٤٥٦٧',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'Persian digits' => [
                '۰۷۹۱۲۳۴۵۶۷',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'international Jordanian number' => [
                '+962791234567',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'international number with 00 prefix' => [
                '00962791234567',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'Jordanian landline' => [
                '065123456',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'international phone number' => [
                '+447911123456',
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'phone with invisible character' => [
                "079\u{200B}1234567",
                ContactPatternDetector::PHONE_NUMBER,
            ],

            'standard email' => [
                'test@example.com',
                ContactPatternDetector::EMAIL_ADDRESS,
            ],

            'email with spaces' => [
                'test @ example . com',
                ContactPatternDetector::EMAIL_ADDRESS,
            ],

            'obfuscated email' => [
                'test at example dot com',
                ContactPatternDetector::EMAIL_ADDRESS,
            ],

            'social media username' => [
                '@example_user',
                ContactPatternDetector::SOCIAL_CONTACT,
            ],

            'Instagram link' => [
                'https://instagram.com/example_user',
                ContactPatternDetector::SOCIAL_CONTACT,
            ],

            'Telegram link' => [
                'https://t.me/example_user',
                ContactPatternDetector::SOCIAL_CONTACT,
            ],

            'WhatsApp link containing phone number' => [
                'https://wa.me/962791234567',
                ContactPatternDetector::PHONE_NUMBER,
            ],

        ];
    }

    #[DataProvider('normalMessages')]
    public function test_does_not_detect_contact_information_in_normal_messages(
        string $message
    ): void {
        $detector = new ContactPatternDetector();

        $this->assertNull(
            $detector->detect($message)
        );
    }

    public static function normalMessages(): array
    {
        return [

            'normal greeting' => [
                'مرحبا، الشقة لسا متوفرة؟',
            ],

            'property price' => [
                'سعر الشقة 85000 دينار',
            ],

            'land price' => [
                'سعر الأرض 250000 دينار',
            ],

            'property area' => [
                'مساحة الشقة 150 متر',
            ],

            'property identifier' => [
                'رقم العقار 123456',
            ],

            'apartment number' => [
                'رقم الشقة 12 والطابق الثالث',
            ],

            'rent and deposit' => [
                'الإيجار 350 والتأمين 100',
            ],

            'viewing appointment' => [
                'ممكن أشوف الشقة بكرا الساعة 5؟',
            ],

            'availability date' => [
                'العقار متوفر بتاريخ 2026-12-01',
            ],

            'refusing WhatsApp' => [
                'ما عندي واتساب خلينا نحكي هون',
            ],

            'refusing phone exchange' => [
                'ما بدي أعطيك رقمي',
            ],

            'refusing Instagram' => [
                'ما عندي حساب انستغرام',
            ],

            'internal communication' => [
                'خلينا نحكي هون بالتطبيق',
            ],

            'English property discussion' => [
                'The apartment has 3 bedrooms',
            ],

            'English internal communication' => [
                'I prefer to chat here instead of WhatsApp',
            ],

        ];
    }
}