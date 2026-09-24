<?php

function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname=' . DB_NAME
         . ';charset=' . DB_CHARSET;

    try {

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [

            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return $pdo;

    } catch (PDOException $e) {
        if (function_exists('epLog')) {
            epLog('Database connection failed: ' . $e->getMessage());
        } else {
            error_log('Database connection failed: ' . $e->getMessage());
        }
        throw new RuntimeException('Database connection failed. Please check your XAMPP MySQL service.', 0, $e);
    }
}
