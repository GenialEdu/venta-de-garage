<?php
require_once __DIR__ . '/db.php';

function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION[ADMIN_SESSION_KEY]) || $_SESSION[ADMIN_SESSION_KEY] !== true) {
        header('Location: login.php');
        exit;
    }
}

function tryLogin(string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'admin_password' LIMIT 1");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $hash = $row['setting_value'] ?? '';

    if (password_verify($password, $hash)) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[ADMIN_SESSION_KEY] = true;
        return true;
    }
    return false;
}

function logout(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION[ADMIN_SESSION_KEY]);
    session_destroy();
}

function generateCaptcha(): array {
    $a = rand(1, 9);
    $b = rand(1, 9);
    $answer = $a + $b;
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION[CAPTCHA_SESSION_KEY] = $answer;
    return ['question' => "¿Cuánto es $a + $b?", 'answer' => $answer];
}

function verifyCaptcha(string $userAnswer): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $correct = $_SESSION[CAPTCHA_SESSION_KEY] ?? null;
    unset($_SESSION[CAPTCHA_SESSION_KEY]);
    return $correct !== null && (int)$userAnswer === (int)$correct;
}
