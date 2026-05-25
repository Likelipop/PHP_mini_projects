<?php

declare(strict_types=1);

namespace Controllers;

use Support\Response;
use Support\Database;
use Support\Storage;
use Exception;

class HealthController
{
    public function index(): void
    {
        $dbStatus = false;
        $minioStatus = false;

        try {
            Database::getConnection()->query('SELECT 1');
            $dbStatus = true;
        } catch (Exception $e) {}

        try {
            Storage::getClient()->listBuckets();
            $minioStatus = true;
        } catch (Exception $e) {}

        Response::json(200, [
            'status' => 'ok',
            'database' => $dbStatus ? 'reachable' : 'unreachable',
            'minio' => $minioStatus ? 'reachable' : 'unreachable'
        ]);
    }
}
