<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['title']) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($data['title']) ?></h1>

    <ul>
        <li>APP_NAME: <?= htmlspecialchars($data['app_name']) ?></li>
        <li>ORGANIZER: <?= htmlspecialchars($data['organizer']) ?></li>
        <li>APP_ENV: <?= htmlspecialchars($data['app_env']) ?></li>
        <li>APP_DEBUG: <?= htmlspecialchars($data['app_debug']) ?></li>
    </ul>

    <h2>Workshop List</h2>
    <?php foreach ($data['events'] as $event): ?>
        <div style="margin-bottom: 16px; padding: 12px; border: 1px solid #ccc;">
            <p><strong>Title:</strong> <?= htmlspecialchars($event['title']) ?></p>
            <p><strong>Trainer:</strong> <?= htmlspecialchars($event['trainer']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($event['date']) ?></p>
            <p><strong>Seats Total:</strong> <?= htmlspecialchars((string)$event['seats_total']) ?></p>
            <p><strong>Seats Available:</strong> <?= htmlspecialchars((string)$event['seats_available']) ?></p>
            <p><strong>Status:</strong> <?= $event['seats_available'] > 0 ? 'Open' : 'Full' ?></p>
        </div>
    <?php endforeach; ?>

    <h2>API Endpoints</h2>
    <ul>
        <li>GET /events</li>
        <li>HEAD /events</li>
        <li>POST /registrations</li>
        <li>OPTIONS /registrations</li>
    </ul>
</body>
</html>