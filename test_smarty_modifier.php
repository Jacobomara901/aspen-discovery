<?php
// Test the Smarty modifier directly

// Simulate activeLanguage global
class MockLanguage {
    public $locale;
}

// Include the modifier
require_once __DIR__ . '/code/web/sys/Smarty/plugins/modifier.format_date_locale.php';

// Test timestamp
$testDate = strtotime('2025-01-15 14:30:00');

// Test with different locales
$testLocales = ['en-US', 'en-GB', 'fr-CA', 'es-US'];

echo "Testing smarty_modifier_format_date_locale()\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($testLocales as $locale) {
    $GLOBALS['activeLanguage'] = new MockLanguage();
    $GLOBALS['activeLanguage']->locale = $locale;

    echo "Locale: {$locale}\n";
    echo "  short:  " . smarty_modifier_format_date_locale($testDate, 'short') . "\n";
    echo "  medium: " . smarty_modifier_format_date_locale($testDate, 'medium') . "\n";
    echo "  long:   " . smarty_modifier_format_date_locale($testDate, 'long') . "\n";
    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "Smarty modifier test completed!\n";
