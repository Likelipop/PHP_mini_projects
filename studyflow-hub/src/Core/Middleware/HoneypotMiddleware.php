<?php

declare(strict_types=1);

namespace StudyFlow\Core\Middleware;

use StudyFlow\Core\Request;
use StudyFlow\Core\Response;

class HoneypotMiddleware
{
    private static string $honeypotField = 'website_verify';

    public static function getFieldName(): string
    {
        return self::$honeypotField;
    }

    public static function handle(): void
    {
        if (Request::getMethod() === 'POST') {
            $honeypotValue = Request::input(self::$honeypotField);
            
            if ($honeypotValue !== null && $honeypotValue !== '') {
                // Spam detected!
                Response::text(400, 'Bad Request: Spam detected.');
                exit;
            }
        }
    }
}
