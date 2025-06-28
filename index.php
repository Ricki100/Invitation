<?php
session_start();

// Clear previous session event data
unset($_SESSION['event_data']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = $_POST['event_name'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_location = $_POST['event_location'] ?? '';
    $event_description = $_POST['event_description'] ?? '';
    $guests_text = $_POST['guests'] ?? '';
    $event_image = '';

    // Validate inputs
    if (empty($event_name) || empty($event_date) || empty($guests_text)) {
        $error = "Please fill in all required fields.";
    } else {
        // Handle image upload
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['event_image']['size'] <= 8*1024*1024) {
                $file_name = uniqid('eventimg_') . '.' . $ext;
                $upload_path = 'uploads/' . $file_name;
                if (!file_exists('uploads')) mkdir('uploads', 0755, true);
                move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path);
                $event_image = $upload_path;
            } else {
                $error = "Invalid image file. Please upload a JPG, PNG, or GIF (max 8MB).";
            }
        }
        // Parse guest list
        $guests = array_filter(array_map('trim', explode("\n", $guests_text)));
        if (empty($guests)) {
            $error = "Please enter at least one guest name.";
        }
        if (!$error) {
            $_SESSION['event_data'] = [
                'event_name' => $event_name,
                'event_date' => $event_date,
                'event_location' => $event_location,
                'event_description' => $event_description,
                'event_image' => $event_image,
                'guests' => $guests
            ];
            header('Location: event_results.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event RSVP Generator - Create Beautiful Invitations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px 40px;
            margin-bottom: 40px;
            text-align: center;
            color: white;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 15px 40px;
            font-weight: 600;
        }
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
            padding: 15px 40px;
            font-weight: 600;
        }
        .btn-outline-light {
            border: 2px solid white;
            border-radius: 25px;
            padding: 15px 40px;
            font-weight: 600;
        }
        .btn-outline-light:hover {
            background: white;
            color: #667eea;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #667eea;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .upload-area:hover {
            border-color: #667eea;
        }
        .upload-area img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            margin-top: 10px;
        }
        .navbar {
            background: rgba(33, 37, 41, 0.95) !important;
            backdrop-filter: blur(10px);
        }
        .mode-selector {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
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
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="google_login.php">
                    <i class="fas fa-sign-in-alt me-1"></i>Sign In
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="display-4 mb-4">🎉 Create Beautiful Event Invitations</h1>
            <p class="lead mb-4">Generate unique RSVP links and QR codes for your guests in minutes</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-bolt feature-icon"></i>
                                    <h5>Quick Start</h5>
                                    <p class="text-muted">Create an event instantly without signing up</p>
                                    <button class="btn btn-primary" onclick="showQuickStart()">
                                        <i class="fas fa-rocket me-2"></i>Start Now
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-user feature-icon"></i>
                                    <h5>Full Account</h5>
                                    <p class="text-muted">Sign in with Google to manage multiple events</p>
                                    <a href="google_login.php" class="btn btn-success">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Start Form (Hidden by default) -->
        <div id="quickStartForm" style="display: none;">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2>Create Your Event</h2>
                        <p class="text-muted">Fill in the details below to generate RSVP invitations</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Event Name *</label>
                                    <input type="text" class="form-control" name="event_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Event Date *</label>
                                    <input type="date" class="form-control" name="event_date" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Event Location</label>
                                    <input type="text" class="form-control" name="event_location">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Event Description</label>
                                    <textarea class="form-control" name="event_description" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Guest List *</label>
                                    <textarea class="form-control" name="guests" rows="6" 
                                              placeholder="Enter guest names (one per line):&#10;John Smith&#10;Jane Doe&#10;Mike Johnson" required></textarea>
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
                                            JPG, PNG, GIF (max 8MB)
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
                                            <li class="mb-2">• Test your RSVP links before sending</li>
                                            <li>• Each guest gets a unique QR code</li>
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

        <!-- Features Section -->
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <i class="fas fa-qrcode feature-icon"></i>
                        <h5>QR Codes</h5>
                        <p class="text-muted">Each guest gets a unique QR code for easy mobile access</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <i class="fas fa-image feature-icon"></i>
                        <h5>Custom Images</h5>
                        <p class="text-muted">Upload beautiful images to make your invitations stand out</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <i class="fas fa-mobile-alt feature-icon"></i>
                        <h5>Mobile Friendly</h5>
                        <p class="text-muted">Perfect for sharing via text, email, or social media</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showQuickStart() {
            document.getElementById('quickStartForm').style.display = 'block';
            document.getElementById('quickStartForm').scrollIntoView({ behavior: 'smooth' });
        }

        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('eventImage');

        if (uploadArea && fileInput) {
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
        }
    </script>
</body>
</html> 