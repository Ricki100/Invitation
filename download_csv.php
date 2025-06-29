<?php
session_start();

// Get event ID from URL
$event_id = $_GET['event_id'] ?? '';

if (empty($event_id)) {
    header('Location: index.php');
    exit;
}

// Load event data from file
$events_file = 'data/events.json';
if (!file_exists($events_file)) {
    header('Location: index.php');
    exit;
}

$events = json_decode(file_get_contents($events_file), true) ?? [];
if (!isset($events[$event_id])) {
    header('Location: index.php');
    exit;
}

$event = $events[$event_id];
$guests = $event['guests'];

// Get the base URL for generating full links
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/';

// Generate CSV content
$csv_content = "Guest Name,RSVP Link,QR Code Image URL\n";

foreach ($guests as $guest) {
    $rsvp_link = $base_url . 'rsvp.php?event_id=' . urlencode($event_id) . '&guest=' . urlencode($guest);
    $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($rsvp_link);
    
    // Escape CSV values (handle commas and quotes)
    $guest_escaped = '"' . str_replace('"', '""', $guest) . '"';
    $rsvp_link_escaped = '"' . str_replace('"', '""', $rsvp_link) . '"';
    $qr_code_url_escaped = '"' . str_replace('"', '""', $qr_code_url) . '"';
    
    $csv_content .= $guest_escaped . ',' . $rsvp_link_escaped . ',' . $qr_code_url_escaped . "\n";
}

// Set headers for CSV download
$filename = 'rsvp_links_' . $event_id . '_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($csv_content));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output the CSV content
echo $csv_content;
?> 