<?php
session_start();

$error = '';
$success = '';

// Get event ID from URL or form
$event_id = $_GET['event_id'] ?? $_POST['event_id'] ?? '';

if (empty($event_id)) {
    header('Location: index.php');
    exit;
}

// Load existing event data
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guests_text = $_POST['guests'] ?? '';
    
    // Handle guest file upload (CSV/Excel)
    $uploaded_guests = [];
    if (isset($_FILES['guests_file']) && $_FILES['guests_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['guests_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['guests_file']['name'], PATHINFO_EXTENSION));
        if ($file_ext === 'csv') {
            if (($handle = fopen($file_tmp, 'r')) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    if (!empty($data[0])) {
                        $uploaded_guests[] = trim($data[0]);
                    }
                }
                fclose($handle);
            }
        } elseif (in_array($file_ext, ['xls', 'xlsx'])) {
            // Excel support requires PhpSpreadsheet
            if (file_exists(__DIR__ . '/vendor/autoload.php')) {
                require_once __DIR__ . '/vendor/autoload.php';
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_tmp);
                $sheet = $spreadsheet->getActiveSheet();
                foreach ($sheet->getRowIterator() as $row) {
                    $cell = $sheet->getCell('A' . $row->getRowIndex());
                    $val = trim($cell->getValue());
                    if (!empty($val)) {
                        $uploaded_guests[] = $val;
                    }
                }
            }
        }
    }
    
    // Parse guest list from textarea
    $new_guests = array_filter(array_map('trim', explode("\n", $guests_text)));
    // Merge with uploaded guests and remove duplicates
    $new_guests = array_unique(array_merge($new_guests, $uploaded_guests));
    
    if (empty($new_guests)) {
        $error = "Please enter at least one guest name.";
    } else {
        // Add new guests to existing event
        $existing_guests = $event['guests'];
        $all_guests = array_unique(array_merge($existing_guests, $new_guests));
        
        // Update the event with new guests
        $events[$event_id]['guests'] = $all_guests;
        file_put_contents($events_file, json_encode($events, JSON_PRETTY_PRINT));
        
        $success = "Successfully added " . count($new_guests) . " new guests to the event!";
        
        // Redirect to results page after a short delay
        header('Location: event_results.php?event_id=' . $event_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Guests - <?php echo htmlspecialchars($event['event_name']); ?></title>
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
        .event-info {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <h2 class="mb-3">
                            <i class="fas fa-user-plus me-2 text-primary"></i>
                            Add Guests to Event
                        </h2>
                        <p class="text-muted">Add new guests to your existing event</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Event Information -->
                    <div class="event-info">
                        <h5 class="mb-3">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            Event Details
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Event:</strong><br><?php echo htmlspecialchars($event['event_name']); ?></p>
                                <p><strong>Date:</strong><br><?php echo htmlspecialchars($event['event_date']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Location:</strong><br><?php echo htmlspecialchars($event['event_location']); ?></p>
                                <p><strong>Current Guests:</strong><br><?php echo count($event['guests']); ?> guests</p>
                            </div>
                        </div>
                    </div>

                    <form action="add_guests.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="guests_file" class="form-label">Upload Guest List (CSV or Excel)</label>
                                    <input type="file" class="form-control" id="guests_file" name="guests_file" accept=".csv,.xls,.xlsx">
                                    <div class="form-text">
                                        <small>Upload a CSV or Excel file with one guest name per row. This will be merged with any names entered manually below.</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="guests" class="form-label">New Guest Names *</label>
                                    <textarea class="form-control" id="guests" name="guests" rows="8" 
                                              placeholder="Enter new guest names (one per line):&#10;John Smith&#10;Jane Doe&#10;Mike Johnson" required></textarea>
                                    <div class="form-text">
                                        <small>Enter one guest name per line. These will be added to your existing guest list.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle text-primary me-2"></i>Tips
                                        </h6>
                                        <ul class="list-unstyled small">
                                            <li class="mb-2">• Enter one guest name per line</li>
                                            <li class="mb-2">• Duplicate names will be ignored</li>
                                            <li class="mb-2">• You can upload a CSV file</li>
                                            <li>• New QR codes will be generated</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="event_results.php?event_id=<?php echo urlencode($event_id); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Event
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Guests
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 