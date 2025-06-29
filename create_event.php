<?php
// require_once 'config.php';

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sheet_link = $_POST['sheet_link'] ?? '';
    $form_link = $_POST['form_link'] ?? '';
    $event_name = $_POST['event_name'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_location = $_POST['event_location'] ?? '';
    $guests_text = $_POST['guests'] ?? '';
    $event_description = $_POST['event_description'] ?? '';
    
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
    
    // Validate inputs
    if (empty($sheet_link) || empty($event_name) || empty($event_date) || empty($guests_text)) {
        $error = "All required fields must be filled.";
    } else {
        // Parse guest list
        $guests = array_filter(array_map('trim', explode("\n", $guests_text)));
        // Merge with uploaded guests and remove duplicates
        $guests = array_unique(array_merge($guests, $uploaded_guests));
        
        if (empty($guests)) {
            $error = "Please enter at least one guest name.";
        } else {
            // Handle image upload
            $event_image = '';
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                if (isValidImageFile($_FILES['event_image'])) {
                    $file_extension = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));
                    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
                    $upload_path = UPLOAD_DIR . $file_name;
                    
                    if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                        $event_image = $upload_path;
                    } else {
                        $error = "Failed to upload image. Please try again.";
                    }
                } else {
                    $error = "Invalid image file. Please upload JPG, PNG, or GIF (max 5MB).";
                }
            }
            
            if (empty($error)) {
                // Create event data
                $event_id = uniqid();
                $event_data = [
                    'id' => $event_id,
                    'name' => $event_name,
                    'date' => $event_date,
                    'location' => $event_location,
                    'description' => $event_description,
                    'image' => $event_image,
                    'sheet_link' => $sheet_link,
                    'form_link' => $form_link,
                    'guests' => $guests,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Save event to generic events file
                $events_file = "data/events.json";
                $events = [];
                if (file_exists($events_file)) {
                    $events = json_decode(file_get_contents($events_file), true) ?? [];
                }
                
                $events[$event_id] = $event_data;
                file_put_contents($events_file, json_encode($events, JSON_PRETTY_PRINT));
                
                // Redirect to results page
                header('Location: event_results.php?event_id=' . $event_id);
                exit;
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
    <title>Create Event - RSVP Generator</title>
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
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: border-color 0.3s;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #667eea;
        }
        .upload-area.dragover {
            border-color: #667eea;
            background: #e8f2ff;
        }
        .navbar {
            background: rgba(33, 37, 41, 0.95) !important;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-check me-2"></i>Event RSVP Generator
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-8">
                <div class="card">
                    <div class="card-body p-5">
                        <h1 class="text-center mb-4">🎉 Create New Event</h1>
                        <p class="text-center text-muted mb-4">Set up your event with custom invitations and images</p>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="create_event.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="sheet_link" class="form-label">Google Sheet Link *</label>
                                        <input type="url" class="form-control" id="sheet_link" name="sheet_link" 
                                               placeholder="https://docs.google.com/spreadsheets/d/..." required>
                                        <div class="form-text">
                                            <small>Share your Google Sheet and paste the link here. RSVPs will be stored in this sheet.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="form_link" class="form-label">Google Form Link (Optional)</label>
                                        <input type="url" class="form-control" id="form_link" name="form_link" 
                                               placeholder="https://docs.google.com/forms/d/...">
                                        <div class="form-text">
                                            <small>If provided, guests will be redirected to your Google Form instead of using the built-in RSVP form.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="event_name" class="form-label">Event Name *</label>
                                        <input type="text" class="form-control" id="event_name" name="event_name" 
                                               placeholder="e.g., John & Sarah's Wedding" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="event_date" class="form-label">Event Date *</label>
                                                <input type="date" class="form-control" id="event_date" name="event_date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="event_location" class="form-label">Event Location</label>
                                                <input type="text" class="form-control" id="event_location" name="event_location" 
                                                       placeholder="e.g., Central Park, New York">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="event_description" class="form-label">Event Description</label>
                                        <textarea class="form-control" id="event_description" name="event_description" 
                                                  rows="3" placeholder="Tell your guests about the event..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="guests" class="form-label">Guest List *</label>
                                        <textarea class="form-control" id="guests" name="guests" rows="8" 
                                                  placeholder="Enter guest names (one per line):\nJohn Smith\nJane Doe\nMike Johnson" required></textarea>
                                        <div class="form-text">
                                            <small>Enter one guest name per line. Each guest will get a unique RSVP link.</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Event Image (Optional)</label>
                                        <div class="upload-area" id="uploadArea">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                                            <h6>Drop image here</h6>
                                            <p class="text-muted small">or click to browse</p>
                                            <input type="file" name="event_image" id="eventImage" class="d-none" 
                                                   accept=".jpg,.jpeg,.png,.gif">
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                JPG, PNG, GIF (max 5MB)
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-info-circle text-primary me-2"></i>Tips
                                            </h6>
                                            <ul class="list-unstyled small">
                                                <li class="mb-2">• Use a high-quality image for better invitations</li>
                                                <li class="mb-2">• Keep guest names simple and clear</li>
                                                <li class="mb-2">• Make sure your Google Sheet is shared</li>
                                                <li>• Test your RSVP links before sending</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-magic me-2"></i>Generate RSVP Invitations
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('eventImage');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateUploadArea(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                updateUploadArea(e.target.files[0]);
            }
        });

        function updateUploadArea(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                uploadArea.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 10px;">
                    <h6 class="mt-3">${file.name}</h6>
                    <p class="text-muted small">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                `;
            };
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html> 