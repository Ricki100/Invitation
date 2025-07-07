<?php
/**
 * Test script for Google Sheets integration
 * Use this to verify your Google Apps Script is working correctly
 */

// Configuration
$webapp_url = 'https://script.google.com/macros/s/AKfycbxQ4g4Te1GNjFjYQogWgHRZWNK86_ky8pQhOfqiza9fv0fX8rSKjfVEiB_3Qw2tHdKMKA/exec';
$test_sheet_id = 'YOUR_SHEET_ID_HERE'; // Replace with your actual sheet ID
$test_guest_name = 'Test Guest';

echo "<h1>Google Sheets Integration Test</h1>\n";

// Test 1: Check if guest exists (should return NOT_FOUND for new guest)
echo "<h2>Test 1: Check for existing guest</h2>\n";
$post_data = [
    'action' => 'check_existing',
    'name' => $test_guest_name,
    'sheet_id' => $test_sheet_id
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($post_data),
        'timeout' => 10
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($webapp_url, false, $context);

echo "<p><strong>Response:</strong> " . htmlspecialchars($result) . "</p>\n";
echo "<p><strong>Expected:</strong> NOT_FOUND (for new guest) or EXISTS (if already in sheet)</p>\n";

// Test 2: Add new RSVP
echo "<h2>Test 2: Add new RSVP</h2>\n";
$post_data = [
    'name' => $test_guest_name,
    'rsvp' => 'Accepted',
    'phone' => '123-456-7890'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($post_data),
        'timeout' => 10
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($webapp_url, false, $context);

echo "<p><strong>Response:</strong> " . htmlspecialchars($result) . "</p>\n";
echo "<p><strong>Expected:</strong> Success</p>\n";

// Test 3: Check again (should return EXISTS)
echo "<h2>Test 3: Check for guest after adding RSVP</h2>\n";
$post_data = [
    'action' => 'check_existing',
    'name' => $test_guest_name,
    'sheet_id' => $test_sheet_id
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($post_data),
        'timeout' => 10
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($webapp_url, false, $context);

echo "<p><strong>Response:</strong> " . htmlspecialchars($result) . "</p>\n";
echo "<p><strong>Expected:</strong> EXISTS</p>\n";

echo "<h2>Test Summary</h2>\n";
echo "<p>If all tests passed, your Google Apps Script is working correctly!</p>\n";
echo "<p>Check your Google Sheet to see the test data.</p>\n";
echo "<p><strong>Note:</strong> You may want to delete the test entry from your Google Sheet after testing.</p>\n";
?> 