<?php
/**
 * Creates (once) and returns the PDO database connection.
 * PDO + prepared statements protect us from SQL injection.
 */
require_once __DIR__ . '/../config/config.php';

function get_pdo(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        $message = 'Database connection failed. Start MySQL in XAMPP and import database/shopping_cart.sql.';
        if (defined('JSON_RESPONSE')) {
            // AJAX scripts expect JSON
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            echo '<h3 style="font-family:sans-serif;color:#b00">' . htmlspecialchars($message) . '</h3>';
        }
        exit;
    }
    return $pdo;
}
