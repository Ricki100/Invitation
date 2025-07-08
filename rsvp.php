<?php
session_start();

// Get parameters from URL
$event_id = $_GET['event_id'] ?? '';
$guest_name = $_GET['guest'] ?? '';

if (empty($event_id) || empty($guest_name)) {
    echo '<h2>Invalid RSVP link.</h2>';
    exit;
}

// Load event data from file
$events_file = 'data/events.json';
if (!file_exists($events_file)) {
    echo '<h2>Event not found.</h2>';
    exit;
}

$events = json_decode(file_get_contents($events_file), true) ?? [];
if (!isset($events[$event_id])) {
    echo '<h2>Event not found.</h2>';
    exit;
}

$event = $events[$event_id];

// Validate guest is in the event's guest list
if (!in_array($guest_name, $event['guests'])) {
    echo '<h2>Invalid guest for this event.</h2>';
    exit;
}

// Function to check if guest has already RSVP'd in Google Sheets
function checkExistingRSVP($guest_name, $sheet_link) {
    if (empty($sheet_link)) {
        return false; // No sheet link, allow RSVP
    }
    
    // Extract sheet ID from the link
    preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $sheet_link, $matches);
    if (empty($matches[1])) {
        return false;
    }
    
    $sheet_id = $matches[1];
    
    // Use Google Sheets API to check for existing record
    // For now, we'll use a simple approach with Apps Script
    $webapp_url = 'https://script.google.com/macros/s/AKfycbxQ4g4Te1GNjFjYQogWgHRZWNK86_ky8pQhOfqiza9fv0fX8rSKjfVEiB_3Qw2tHdKMKA/exec';
    $post_data = [
        'action' => 'check_existing',
        'name' => $guest_name,
        'sheet_id' => $sheet_id
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
    
    // If the result contains "EXISTS", the guest has already RSVP'd
    return strpos($result, 'EXISTS') !== false;
}

// Check if guest has already RSVP'd
$existing_rsvp = checkExistingRSVP($guest_name, $event['sheet_link'] ?? '');

// Check if Google Form link is provided and redirect if so
if (!empty($event['form_link'])) {
    // Generate prefilled Google Form link
    $form_url = $event['form_link'];
    
    // Add entry parameters for prefilling (you'll need to get these from your Google Form)
    // Example: entry.123456789=guest_name
    // You can find entry IDs by inspecting your Google Form's HTML or using the Form API
    $prefilled_params = [];
    
    // Common field names - you may need to adjust these based on your form
    $prefilled_params['entry.123456789'] = $guest_name; // Replace with actual entry ID for name field
    $prefilled_params['entry.987654321'] = $event['event_name']; // Replace with actual entry ID for event field
    
    if (!empty($prefilled_params)) {
        $form_url .= (strpos($form_url, '?') !== false ? '&' : '?') . http_build_query($prefilled_params);
    }
    
    // Redirect to Google Form
    header('Location: ' . $form_url);
    exit;
}

$rsvps_file = 'data/rsvps_' . $event_id . '.json';
if (!file_exists('data')) mkdir('data', 0755, true);

$submitted = false;
$rsvp_response = '';

// Load existing RSVP data
$rsvps = file_exists($rsvps_file) ? json_decode(file_get_contents($rsvps_file), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existing_rsvp) {
    $response = $_POST['rsvp_response'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $timestamp = date('Y-m-d H:i:s');
    $rsvps[$guest_name] = [
        'response' => $response,
        'timestamp' => $timestamp
    ];
    file_put_contents($rsvps_file, json_encode($rsvps, JSON_PRETTY_PRINT));
    // Send to Google Sheets via Apps Script Web App (without cURL)
    $webapp_url = 'https://script.google.com/macros/s/AKfycbxQ4g4Te1GNjFjYQogWgHRZWNK86_ky8pQhOfqiza9fv0fX8rSKjfVEiB_3Qw2tHdKMKA/exec';
    $post_data = [
        'name' => $guest_name,
        'rsvp' => $response,
        'phone' => $phone_number
    ];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($post_data),
            'timeout' => 10
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($webapp_url, false, $context);
    $submitted = true;
    $rsvp_response = $response;
}

// Check if guest has accepted (either from local file or Google Sheets)
$has_accepted = false;
if (isset($rsvps[$guest_name])) {
    $has_accepted = ($rsvps[$guest_name]['response'] === 'Accepted');
} elseif ($existing_rsvp) {
    // If we can't determine from local file but exists in sheets, assume accepted for privacy
    $has_accepted = true;
}

// Generate Google Maps link if location is provided and guest has accepted
$maps_link = '';
if (!empty($event['event_location']) && $has_accepted) {
    $maps_link = 'https://maps.google.com/?q=' . urlencode($event['event_location']);
}

// Generate calendar download link
$calendar_link = '';
if (!empty($event['event_date']) && !empty($event['event_name'])) {
    $date = new DateTime($event['event_date']);
    $calendar_link = 'download_calendar.php?' . http_build_query([
        'event_name' => $event['event_name'],
        'event_date' => $event['event_date'],
        'event_location' => $event['event_location'] ?? '',
        'event_description' => $event['event_description'] ?? ''
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
        }
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
        }
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            border-radius: 25px;
        }
        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #e0a800);
            border: none;
            border-radius: 25px;
            color: #212529;
        }
        .event-details {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .event-image {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-height: 300px;
            object-fit: cover;
            width: 100%;
        }
        .rsvp-buttons .btn {
            margin-bottom: 10px;
            font-size: 1.1em;
            padding: 15px 20px;
        }
        .thank-you-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .event-info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .action-buttons .btn {
            margin: 5px;
            border-radius: 25px;
            padding: 12px 25px;
            font-weight: 500;
        }
        .btn-outline-light {
            border: 2px solid white;
            color: white;
        }
        .btn-outline-light:hover {
            background: white;
            color: #28a745;
        }
        .btn-maps {
            background: linear-gradient(45deg, #4285f4, #34a853);
            border: none;
            color: white;
        }
        .btn-calendar {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            border: none;
            color: white;
        }
        .btn-sheet {
            background: linear-gradient(45deg, #0f9d58, #4285f4);
            border: none;
            color: white;
        }
        .event-meta {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .event-meta i {
            width: 20px;
            margin-right: 10px;
            color: #667eea;
        }
        .alert-warning {
            background: linear-gradient(45deg, #fff3cd, #ffeaa7);
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .location-restricted {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <?php if (isset($error_message)): ?>
                            <!-- Error Message -->
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Already Responded</strong><br>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                            
                            <!-- Show event details without location -->
                            <div class="event-info-card">
                                <h4 class="mb-4 text-center">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Event Details
                                </h4>
                                
                                <?php if (!empty($event['event_image'])): ?>
                                    <div class="text-center mb-4">
                                        <img src="<?php echo htmlspecialchars($event['event_image']); ?>" 
                                             alt="Event Image" class="event-image">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="event-meta">
                                    <i class="fas fa-calendar"></i>
                                    <strong>Event:</strong> <?php echo htmlspecialchars($event['event_name']); ?>
                                </div>
                                
                                <div class="event-meta">
                                    <i class="fas fa-clock"></i>
                                    <strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?>
                                </div>
                                
                                <?php if (!empty($event['event_location']) && isset($rsvp_response) && $rsvp_response === 'Accepted'): ?>
                                    <div class="event-meta">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <strong>Location:</strong> <?php echo htmlspecialchars($event['event_location']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($event['event_description'])): ?>
                                    <div class="event-meta">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['event_description'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                        <?php elseif ($submitted): ?>
                            <!-- Thank You Section -->
                            <div class="thank-you-section text-center">
                                <div class="mb-4">
                                    <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
                                </div>
                                <h2 class="mb-3">Thank You!</h2>
                                <p class="lead mb-0">Your RSVP has been submitted successfully.</p>
                                <p class="mb-0">
                                    <strong><?php echo htmlspecialchars($guest_name); ?></strong>, 
                                    thank you for responding to 
                                    <strong><?php echo htmlspecialchars($event['event_name']); ?></strong>.
                                </p>
                            </div>

                            <!-- Event Details Card -->
                            <div class="event-info-card">
                                <h4 class="mb-4 text-center">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Event Details
                                </h4>
                                
                                <?php if (!empty($event['event_image'])): ?>
                                    <div class="text-center mb-4">
                                        <img src="<?php echo htmlspecialchars($event['event_image']); ?>" 
                                             alt="Event Image" class="event-image">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="event-meta">
                                    <i class="fas fa-calendar"></i>
                                    <strong>Event:</strong> <?php echo htmlspecialchars($event['event_name']); ?>
                                </div>
                                
                                <div class="event-meta">
                                    <i class="fas fa-clock"></i>
                                    <strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?>
                                </div>
                                
                                <?php if (!empty($event['event_location']) && isset($rsvp_response) && $rsvp_response === 'Accepted'): ?>
                                    <div class="event-meta">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <strong>Location:</strong> <?php echo htmlspecialchars($event['event_location']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($event['event_description'])): ?>
                                    <div class="event-meta">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['event_description'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="action-buttons text-center">
                                <?php if (!empty($maps_link) && $rsvp_response === 'Accepted'): ?>
                                    <a href="<?php echo htmlspecialchars($maps_link); ?>" 
                                       target="_blank" class="btn btn-maps">
                                        <i class="fas fa-map-marked-alt me-2"></i>View on Google Maps
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($calendar_link)): ?>
                                    <a href="<?php echo htmlspecialchars($calendar_link); ?>" 
                                       class="btn btn-calendar">
                                        <i class="fas fa-calendar-plus me-2"></i>Add to Calendar
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($event['sheet_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($event['sheet_link']); ?>" 
                                       target="_blank" class="btn btn-sheet">
                                        <i class="fas fa-external-link-alt me-2"></i>View Event Details
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                        <?php else: ?>
                            <div class="text-center mb-4">
                                <h1>🎉</h1>
                                <h2>RSVP Response</h2>
                                <p class="text-muted">Please let us know if you can attend</p>
                            </div>
                            
                            <?php if (!empty($event['event_image'])): ?>
                                <div class="text-center mb-4">
                                    <img src="<?php echo htmlspecialchars($event['event_image']); ?>" 
                                         alt="Event Image" class="event-image">
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-details">
                                <h5 class="mb-3">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Event Details
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Event:</strong><br><?php echo htmlspecialchars($event['event_name']); ?></p>
                                        <p><strong>Date:</strong><br><?php echo htmlspecialchars($event['event_date']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if (!empty($event['event_location']) && isset($rsvp_response) && $rsvp_response === 'Accepted'): ?>
                                            <p><strong>Location:</strong><br><?php echo htmlspecialchars($event['event_location']); ?></p>
                                        <?php endif; ?>
                                        <p><strong>Guest:</strong><br><?php echo htmlspecialchars($guest_name); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="POST">
                                <div class="mb-4 text-center">
                                    <label class="form-label"><strong>Will you attend?</strong></label><br>
                                    <?php if ($existing_rsvp): ?>
                                        <?php
                                        // Fetch previous response from Google Sheets (if possible)
                                        // For now, just show a generic message
                                        ?>
                                        <div class="alert alert-info mt-3 mb-3">
                                            <?php
                                            // Try to show the previous response if available in local file
                                            $prev_response = $rsvps[$guest_name]['response'] ?? null;
                                            if ($prev_response === 'Accepted') {
                                                echo '<i class="fas fa-check-circle text-success me-2"></i>You have already <strong>accepted</strong> this invitation.';
                                            } elseif ($prev_response === 'Declined') {
                                                echo '<i class="fas fa-times-circle text-danger me-2"></i>You have already <strong>declined</strong> this invitation.';
                                            } else {
                                                echo 'You have already responded to this invitation.';
                                            }
                                            ?>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-lg me-2" disabled>Accept</button>
                                        <button type="submit" class="btn btn-danger btn-lg" disabled>Decline</button>
                                    <?php else: ?>
                                        <button type="submit" name="rsvp_response" value="Accepted" class="btn btn-success btn-lg me-2">Accept</button>
                                        <button type="submit" name="rsvp_response" value="Declined" class="btn btn-danger btn-lg">Decline</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                            
                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Need to change your response?</strong> Please contact the event organizer.
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 