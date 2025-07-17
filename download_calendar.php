<?php
// Get event details from URL parameters
$event_name = $_GET['event_name'] ?? '';
$event_date = $_GET['event_date'] ?? '';
$event_location = $_GET['event_location'] ?? '';
$event_description = $_GET['event_description'] ?? '';

// Validate required parameters
if (empty($event_name) || empty($event_date)) {
    http_response_code(400);
    echo 'Missing required parameters';
    exit;
}

// Create event date/time
$event_datetime = new DateTime($event_date, new DateTimeZone('Africa/Harare'));
$event_datetime->setTime(14, 30); // Set to 2:30 PM local time
$end_datetime = clone $event_datetime;
$end_datetime->add(new DateInterval('PT5H30M')); // 5.5 hours duration (until 8:00 PM)

// Generate unique identifier
$uid = uniqid() . '@event-rsvp-generator.com';

// Create iCalendar content
$ics_content = "BEGIN:VCALENDAR\r\n";
$ics_content .= "VERSION:2.0\r\n";
$ics_content .= "PRODID:-//Event RSVP Generator//Calendar Event//EN\r\n";
$ics_content .= "CALSCALE:GREGORIAN\r\n";
$ics_content .= "METHOD:PUBLISH\r\n";
$ics_content .= "BEGIN:VEVENT\r\n";
$ics_content .= "UID:" . $uid . "\r\n";
$ics_content .= "DTSTART:" . $event_datetime->format('Ymd\THis') . "\r\n";
$ics_content .= "DTEND:" . $end_datetime->format('Ymd\THis') . "\r\n";
$ics_content .= "DTSTAMP:" . date('Ymd\THis') . "\r\n";
$ics_content .= "SUMMARY:" . escapeString($event_name) . "\r\n";

if (!empty($event_location)) {
    $ics_content .= "LOCATION:" . escapeString($event_location) . "\r\n";
}

if (!empty($event_description)) {
    $ics_content .= "DESCRIPTION:" . escapeString($event_description) . "\r\n";
}

$ics_content .= "STATUS:CONFIRMED\r\n";
$ics_content .= "SEQUENCE:0\r\n";
$ics_content .= "END:VEVENT\r\n";
$ics_content .= "END:VCALENDAR\r\n";

// Set headers for file download
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . sanitizeFilename($event_name) . '.ics"');
header('Content-Length: ' . strlen($ics_content));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output the calendar file
echo $ics_content;

/**
 * Escape special characters in iCalendar strings
 */
function escapeString($string) {
    $string = str_replace('\\', '\\\\', $string);
    $string = str_replace(';', '\\;', $string);
    $string = str_replace(',', '\\,', $string);
    $string = str_replace("\n", '\\n', $string);
    $string = str_replace("\r", '\\r', $string);
    return $string;
}

/**
 * Sanitize filename for download
 */
function sanitizeFilename($filename) {
    // Remove or replace invalid characters
    $filename = preg_replace('/[^a-zA-Z0-9\s\-_\.]/', '', $filename);
    $filename = trim($filename);
    $filename = str_replace(' ', '_', $filename);
    
    // Limit length
    if (strlen($filename) > 50) {
        $filename = substr($filename, 0, 50);
    }
    
    return $filename;
}
?> 