<?php

namespace App\Controllers;

use App\Support\Response;

class EventController
{
    public function index(array $events): void
    {
        Response::json(200, [
            'message' => 'Workshop list',
            'data' => $events
        ]);
    }

    public function head(): void
    {
        http_response_code(200);
        header('Content-Type: application/json; charset=UTF-8');
        exit;
    }
}