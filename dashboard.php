<?php
// require_once 'config.php';

// Load all events from generic file
$events_file = "data/events.json";
$events = [];
if (file_exists($events_file)) {
    $events = json_decode(file_get_contents($events_file), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Event RSVP Generator</title>
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
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .event-card {
            transition: transform 0.2s;
        }
        .event-card:hover {
            transform: translateY(-5px);
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: border-color 0.3s;
        }
        .upload-area:hover {
            border-color: #667eea;
        }
        .upload-area.dragover {
            border-color: #667eea;
            background: #e8f2ff;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-check me-2"></i>Event RSVP Generator
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <h2 class="mb-1">Welcome!</h2>
                        <p class="text-muted mb-0">Manage your events and RSVP invitations</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-plus-circle text-primary me-2"></i>Create New Event
                        </h5>
                        <p class="card-text text-muted mb-3">Start a new event with custom invitations and image uploads</p>
                        <a href="create_event.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Event
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-image text-success me-2"></i>Upload Event Image
                        </h5>
                        <p class="card-text text-muted mb-3">Add a beautiful image to your event invitations</p>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload me-2"></i>Upload Image
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>Your Events
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($events)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No events yet</h5>
                                <p class="text-muted">Create your first event to get started!</p>
                                <a href="create_event.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create Your First Event
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($events as $event_id => $event): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card event-card h-100">
                                            <?php if (!empty($event['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                                                     class="card-img-top" alt="Event Image" style="height: 200px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h6>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo htmlspecialchars($event['date']); ?>
                                                </p>
                                                <?php if (!empty($event['location'])): ?>
                                                    <p class="card-text text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?php echo htmlspecialchars($event['location']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <?php echo count($event['guests']); ?> guests invited
                                                    </small>
                                                </p>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="d-flex justify-content-between">
                                                    <a href="view_event.php?id=<?php echo $event_id; ?>" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                    <a href="edit_event.php?id=<?php echo $event_id; ?>" 
                                                       class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Image Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Event Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="upload_image.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h5>Drop your image here</h5>
                            <p class="text-muted">or click to browse</p>
                            <input type="file" name="event_image" id="eventImage" class="d-none" 
                                   accept=".jpg,.jpeg,.png,.gif" required>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                Supported formats: JPG, PNG, GIF (max 5MB)
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Image</button>
                    </div>
                </form>
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
                    <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 10px;">
                    <h5 class="mt-3">${file.name}</h5>
                    <p class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                `;
            };
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html> 