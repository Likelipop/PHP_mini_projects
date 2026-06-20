<?php

declare(strict_types=1);

namespace Controllers;

use Support\Response;

class HomeController
{


    public function index(): void
    {
        Response::view('home', ['title' => 'Student Learning Resource Hub']);
    }
}
