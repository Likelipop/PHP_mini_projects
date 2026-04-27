<?php

namespace App\Controllers;

use App\Support\Response;

class RegistrationController
{
    public function store(array $events, array $config): void
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? ($_SERVER['CONTENT_TYPE'] ?? '');

        if (!str_contains(strtolower($contentType), 'application/json')) {
            Response::json(415, [
                'error' => 'Unsupported Media Type',
                'message' => 'Content-Type must be application/json'
            ]);
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            Response::json(400, [
                'error' => 'Bad Request',
                'message' => 'Invalid JSON body'
            ]);
        }

        $eventId = $payload['event_id'] ?? null;
        $studentName = trim($payload['student_name'] ?? '');
        $email = trim($payload['email'] ?? '');
        $quantity = (int) ($payload['quantity'] ?? 0);

        if (!$eventId || $studentName === '' || $email === '' || $quantity <= 0) {
            Response::json(422, [
                'error' => 'Unprocessable Content',
                'message' => 'event_id, student_name, email, quantity are required and must be valid'
            ]);
        }

        if ($quantity > $config['app']['max_registrations_per_request']) {
            Response::json(422, [
                'error' => 'Unprocessable Content',
                'message' => 'Quantity exceeds allowed registration limit per request'
            ]);
        }

        $selectedEvent = null;
        foreach ($events as $event) {
            if ($event['id'] === (int) $eventId) {
                $selectedEvent = $event;
                break;
            }
        }

        if (!$selectedEvent) {
            Response::json(422, [
                'error' => 'Unprocessable Content',
                'message' => 'Selected event does not exist'
            ]);
        }

        if ($selectedEvent['seats_available'] < $quantity) {
            Response::json(422, [
                'error' => 'Unprocessable Content',
                'message' => 'Not enough seats available'
            ]);
        }

        $registrationId = time();

        Response::json(201, [
            'message' => 'Registration created successfully',
            'data' => [
                'registration_id' => $registrationId,
                'student_name' => $studentName,
                'email' => $email,
                'event_id' => (int) $eventId,
                'quantity' => $quantity
            ]
        ], [
            'Location' => '/registrations/' . $registrationId
        ]);
    }

    public function options(): void
    {
        http_response_code(204);
        header('Allow: POST, OPTIONS');
        exit;
    }
}
