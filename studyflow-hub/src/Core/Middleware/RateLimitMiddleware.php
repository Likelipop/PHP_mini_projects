<?php

declare(strict_types=1);

namespace StudyFlow\Core\Middleware;

use StudyFlow\Core\Session;
use StudyFlow\Core\Response;

class RateLimitMiddleware
{
    public static function handle(int $limit = 60, int $window = 60): void
    {
        Session::start();
        $now = time();
        
        $requests = Session::get('_rate_limit_requests', []);
        
        // Filter out requests that are outside the time window
        $requests = array_filter($requests, function ($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });
        
        if (count($requests) >= $limit) {
            Response::text(429, '429 Too Many Requests. Please slow down.');
            exit;
        }
        
        $requests[] = $now;
        Session::set('_rate_limit_requests', $requests);
    }
}
