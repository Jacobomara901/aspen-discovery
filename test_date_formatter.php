<?php
// Test script for locale-aware date formatting
// Run from command line: php test_date_formatter.php

// Simulate the activeLanguage object
class MockLanguage {
    public $locale;
    public function __construct($locale) {
        $this->locale = $locale;
    }
}

// Test timestamp (January 15, 2025)
$testTimestamp = strtotime('2025-01-15');

// Test different locales
$testLocales = [
    'en-US' => 'English (US)',
    'en-GB' => 'English (UK)',
    'en-CA' => 'English (Canada)',
    'es-US' => 'Spanish (US)',
    'fr-CA' => 'French (Canada)',
    'de-DE' => 'German (Germany)',
    'ja-JP' => 'Japanese (Japan)',
];

echo "Testing IntlDateFormatter with different locales\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($testLocales as $locale => $name) {
    echo "$name ($locale):\n";

    // Test different styles
    $styles = ['short', 'medium', 'long', 'full'];
    $styleMap = [
        'short'  => IntlDateFormatter::SHORT,
        'medium' => IntlDateFormatter::MEDIUM,
        'long'   => IntlDateFormatter::LONG,
        'full'   => IntlDateFormatter::FULL,
    ];

    foreach ($styles as $style) {
        $formatter = new IntlDateFormatter(
            $locale,
            $styleMap[$style],
            IntlDateFormatter::NONE,
            date_default_timezone_get()
        );

        $formatted = $formatter->format($testTimestamp);
        echo "  {$style}: {$formatted}\n";
    }
    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "Test completed successfully!\n";
echo "If you see formatted dates above, IntlDateFormatter is working.\n";
