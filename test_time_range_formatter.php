<?php
// Test the time range Smarty modifier directly

class MockLanguage {
    public $locale;
}

require_once __DIR__ . '/code/web/sys/Smarty/plugins/modifier.format_time_range_locale.php';

$GLOBALS['activeLanguage'] = new MockLanguage();
$GLOBALS['activeLanguage']->locale = 'en_US';

echo "Testing smarty_modifier_format_time_range_locale()\n";
echo str_repeat("=", 80) . "\n\n";

$testCases = [
    ['Same period (AM)', '2025-01-15 10:00:00', '2025-01-15 11:30:00', '12'],
    ['Same period (PM)', '2025-01-15 14:00:00', '2025-01-15 16:30:00', '12'],
    ['Crossing periods (AM to PM)', '2025-01-15 10:00:00', '2025-01-15 14:30:00', '12'],
    ['Crossing periods (PM to AM next day)', '2025-01-15 23:00:00', '2025-01-16 01:00:00', '12'],
    ['24-hour format', '2025-01-15 10:00:00', '2025-01-15 14:30:00', '24'],
    ['Event length from SQL: 90 min', '2025-01-15 18:00:00', strtotime('2025-01-15 18:00:00') + (90 * 60), '12'],
    ['Event length from SQL: 120 min', '2025-01-15 14:00:00', strtotime('2025-01-15 14:00:00') + (120 * 60), '12'],
];

foreach ($testCases as list($name, $start, $end, $format)) {
    echo "{$name}:\n";
    echo "  Start: " . (is_numeric($start) ? date('Y-m-d H:i:s', $start) : $start) . "\n";
    echo "  End:   " . (is_numeric($end) ? date('Y-m-d H:i:s', $end) : $end) . "\n";
    $result = smarty_modifier_format_time_range_locale($start, $end, $format);
    echo "  Result: {$result}\n\n";
}

echo str_repeat("=", 80) . "\n";
echo "Time range formatter test completed!\n";
