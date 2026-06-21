<?php

declare(strict_types=1);

namespace StudyFlow\Controllers;

use StudyFlow\Core\Session;
use StudyFlow\Core\Response;

class BaseController
{
    public function __construct()
    {
        // Harden sessions on every controller load
        Session::start();
        Session::checkSessionTimeout();
        Session::checkSessionContext();
    }

    protected function render(string $view, array $data = [], int $status = 200): void
    {
        Response::view($view, $data, $status);
    }

    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }

    protected function json(int $status, array $data): void
    {
        Response::json($status, $data);
    }
}
