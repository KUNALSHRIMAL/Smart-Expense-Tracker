<?php
require_once dirname(__DIR__) . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || defined('API_REQUEST')) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Session expired. Please log in.']);
            exit;
        }
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function userId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}
