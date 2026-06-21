<?php
require_once __DIR__ . '/config.php';

function getDB(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        if ($conn->connect_error) {
            die('Error de conexión: ' . $conn->connect_error);
        }
    }
    return $conn;
}
