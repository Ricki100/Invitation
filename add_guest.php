<?php
// add_guest.php - Add guests to an existing event by event ID
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = trim($_POST['event_id'] ?? '');
    $guests_text = trim($_POST['guests'] ?? '');

    if (empty($event_id) || empty($guests_text)) {
        $error = 'Please enter both the Event ID and at least one guest name.';
    } else {
        $events_file = 'data/events.json';
        if (!file_exists($events_file)) {
            $error = 'Events file not found.';
        } else {
            $events = json_decode(file_get_contents($events_file), true) ?? [];
            if (!isset($events[$event_id])) {
                $error = 'Event ID not found.';
            } else {
                $new_guests = array_filter(array_map('trim', explode("\n", $guests_text)));
                $existing_guests = $events[$event_id]['guests'] ?? [];
                $merged_guests = array_unique(array_merge($existing_guests, $new_guests));
                $added_count = count($merged_guests) - count($existing_guests);
                if ($added_count === 0) {
                    $error = 'No new guests were added (they may already exist).';
                } else {
                    $events[$event_id]['guests'] = $merged_guests;
                    file_put_contents($events_file, json_encode($events, JSON_PRETTY_PRINT));
                    $success = "Successfully added $added_count new guest(s) to the event.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Guests to Event</title>
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
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .navbar {
            background: rgba(33, 37, 41, 0.95) !important;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-check me-2"></i>Event RSVP Generator
            </a>
        </div>
    </nav>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Add Guests to Event</h2>
                        <p class="text-center text-muted mb-4">Enter the Event ID and guest names to add them to your event.</p>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                        <?php elseif (!empty($success)): ?>
                            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        <form action="add_guest.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="event_id" class="form-label">Event ID</label>
                                <input type="text" class="form-control" id="event_id" name="event_id" placeholder="e.g. 686c2a60b10c2" required>
                                <div class="form-text"><small>Find your Event ID in the event results or events.json file.</small></div>
                            </div>
                            <div class="mb-3">
                                <label for="guests" class="form-label">Guest Names</label>
                                <textarea class="form-control" id="guests" name="guests" rows="6" placeholder="Enter guest names, one per line" required></textarea>
                                <div class="form-text"><small>Each guest will get a unique RSVP link. Duplicates are ignored.</small></div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Add Guests
                                </button>
                            </div>
                        </form>
                        <div class="text-center mt-4">
                            <a href="event_results.php?event_id=" class="btn btn-link">View Event Results</a>
                            <span class="text-muted">|</span>
                            <a href="index.php" class="btn btn-link">Create New Event</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 