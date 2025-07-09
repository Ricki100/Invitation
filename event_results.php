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
$event_image = $event['event_image'];

function generateQRCode($url) {
    return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($url);
}

// Get the base URL
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP Links - <?php echo htmlspecialchars($event['event_name']); ?></title>
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
        .event-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            object-fit: cover;
        }
        .qr-code {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .guest-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .guest-card:hover {
            transform: translateY(-5px);
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
        .btn-copy {
            background: linear-gradient(45deg, #6c757d, #495057);
            border: none;
            border-radius: 25px;
            color: white;
        }
        .event-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .guest-name {
            font-size: 1.2em;
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
        }
        .url-input {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            font-family: monospace;
            font-size: 0.9em;
        }
        .url-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .stats-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
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
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body p-5">
                        <!-- Event Header -->
                        <div class="event-header">
                            <h1 class="mb-3">🎉 RSVP Invitations Generated!</h1>
                            <p class="lead text-muted mb-4">Share these unique links with your guests</p>
                            
                            <?php if ($event_image): ?>
                                <div class="text-center mb-4">
                                    <img src="<?php echo htmlspecialchars($event_image); ?>" class="event-image" alt="Event Image">
                                </div>
                            <?php endif; ?>
                            
                            <h3 class="mb-2"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                            <p class="mb-2">
                                <i class="fas fa-calendar me-2 text-primary"></i>
                                <?php echo htmlspecialchars($event['event_date']); ?>
                                <?php if ($event['event_location']): ?>
                                    <i class="fas fa-map-marker-alt ms-3 me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($event['event_location']); ?>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($event['event_description'])): ?>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Stats Card -->
                        <div class="stats-card">
                            <h4 class="mb-2">
                                <i class="fas fa-users me-2"></i>
                                <?php echo count($guests); ?> Guests Invited
                            </h4>
                            <p class="mb-0">Each guest has a unique RSVP link and QR code</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mb-5">
                            <a href="download_csv.php?event_id=<?php echo urlencode($event_id); ?>" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-download me-2"></i>Download CSV
                            </a>
                            <a href="download_qr_zip.php?event_id=<?php echo urlencode($event_id); ?>" class="btn btn-info btn-lg me-3">
                                <i class="fas fa-qrcode me-2"></i>Download QR Codes (ZIP)
                            </a>
                            <a href="index.php" class="btn btn-success btn-lg">
                                <i class="fas fa-plus me-2"></i>Create Another Event
                            </a>
                        </div>

                        <!-- Guest Links -->
                        <div class="row">
                            <?php foreach ($guests as $guest):
                                $guest_url = BASE_URL . 'rsvp.php?event_id=' . urlencode($event_id) . '&guest=' . urlencode($guest);
                                ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="guest-card">
                                        <div class="text-center mb-3">
                                            <div class="guest-name"><?php echo htmlspecialchars($guest); ?></div>
                                        </div>
                                        
                                        <div class="text-center mb-3">
                                            <img src="<?php echo generateQRCode($guest_url); ?>" class="qr-code" alt="QR Code">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <input type="text" class="form-control url-input" 
                                                   value="<?php echo htmlspecialchars($guest_url); ?>" 
                                                   readonly id="url-<?php echo md5($guest); ?>">
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-copy" 
                                                    onclick="copyToClipboard('url-<?php echo md5($guest); ?>')">
                                                <i class="fas fa-copy me-2"></i>Copy Link
                                            </button>
                                            <a href="<?php echo htmlspecialchars($guest_url); ?>" 
                                               class="btn btn-success" target="_blank">
                                                <i class="fas fa-external-link-alt me-2"></i>Test Link
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                
                // Show success message
                const button = element.nextElementSibling;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
                button.classList.remove('btn-copy');
                button.classList.add('btn-success');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-copy');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
            }
        }
    </script>
</body>
</html> 