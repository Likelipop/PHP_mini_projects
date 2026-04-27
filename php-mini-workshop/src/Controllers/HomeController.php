<?php

namespace App\Controllers;

class HomeController
{
    public function index(array $config, array $events): array
    {
        return [
            'title' => 'Mini Workshop Registration App',
            'app_name' => $config['app']['name'],
            'organizer' => $config['app']['organizer'],
            'app_env' => $config['app']['env'],
            'app_debug' => $config['app']['debug'] ? 'true' : 'false',
            'events' => $events
        ];
    }
}