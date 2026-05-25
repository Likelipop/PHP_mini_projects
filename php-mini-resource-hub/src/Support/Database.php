<?php

declare(strict_types=1);

namespace Support;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'db';
            $port = getenv('DB_PORT') ?: '5432';
            $db   = getenv('DB_NAME') ?: 'hub_db';
            $user = getenv('DB_USER') ?: 'hub_user';
            $pass = getenv('DB_PASS') ?: 'hub_pass';

            $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            
            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                Response::text(500, 'Database Connection Failed: ' . $e->getMessage());
                exit;
            }
        }

        return self::$instance;
    }
}
