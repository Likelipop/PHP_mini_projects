<?php

declare(strict_types=1);

namespace StudyFlow\Core;

use PDO;
use PDOException;
use StudyFlow\Core\Response;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '5433'; // Default to external port for local fallback
            $db   = getenv('DB_NAME') ?: 'studyflow_db';
            $user = getenv('DB_USER') ?: 'studyflow_user';
            $pass = getenv('DB_PASS') ?: 'studyflow_pass';

            $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            
            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                // Log the real error to a file if needed, but do NOT show to user (TC15 Requirement)
                Response::text(500, 'Database Connection Failed: Hệ thống đang bảo trì hoặc mất kết nối CSDL. Vui lòng thử lại sau.');
                exit;
            }
        }

        return self::$instance;
    }
}
