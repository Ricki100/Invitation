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

// Create a temporary directory for QR codes
$temp_dir = 'temp_qr_' . $event_id . '_' . time();
if (!file_exists($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

// Download QR codes for each guest
foreach ($guests as $guest) {
    $rsvp_link = $base_url . 'rsvp.php?event_id=' . urlencode($event_id) . '&guest=' . urlencode($guest);
    $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($rsvp_link);
    
    // Sanitize filename
    $filename = preg_replace('/[^a-zA-Z0-9\s\-_\.]/', '', $guest);
    $filename = str_replace(' ', '_', $filename);
    $filename = $temp_dir . '/' . $filename . '_QR.png';
    
    // Download QR code image
    $qr_image = file_get_contents($qr_code_url);
    if ($qr_image !== false) {
        file_put_contents($filename, $qr_image);
    }
}

// Create ZIP file
$zip_filename = 'qr_codes_' . $event_id . '_' . date('Y-m-d_H-i-s') . '.zip';
$zip_path = $temp_dir . '/' . $zip_filename;

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
    // Add all QR code files to ZIP
    $files = glob($temp_dir . '/*.png');
    foreach ($files as $file) {
        $zip->addFile($file, basename($file));
    }
    $zip->close();
    
    // Download the ZIP file
    if (file_exists($zip_path)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($zip_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        readfile($zip_path);
        
        // Clean up temporary files
        array_map('unlink', glob($temp_dir . '/*'));
        rmdir($temp_dir);
        exit;
    }
}

// If we get here, something went wrong
echo "Error creating ZIP file. Please try again.";
?> 