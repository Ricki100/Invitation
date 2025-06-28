<?php
session_start();
$event = $_SESSION['event_data'] ?? null;
if (!$event) {
    echo '<h2>No event in session. Please create an event first.</h2>';
    exit;
}
$event_id = md5($event['event_name'] . $event['event_date']);
$rsvps_file = 'data/rsvps_' . $event_id . '.json';
$rsvps = file_exists($rsvps_file) ? json_decode(file_get_contents($rsvps_file), true) : [];
$guests = $event['guests'];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSVP Responses - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#222;min-height:100vh;}.card{border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.1);}</style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card p-4">
                <h2 class="mb-3 text-center">RSVP Responses</h2>
                <h4 class="mb-4 text-center"><?php echo htmlspecialchars($event['event_name']); ?></h4>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Guest</th>
                            <th>Response</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guests as $guest): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($guest); ?></td>
                            <td><?php echo isset($rsvps[$guest]) ? htmlspecialchars($rsvps[$guest]['response']) : '<span class="text-muted">No response</span>'; ?></td>
                            <td><?php echo isset($rsvps[$guest]) ? htmlspecialchars($rsvps[$guest]['timestamp']) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary">Create New Event</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 