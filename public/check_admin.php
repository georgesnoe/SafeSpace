<?php

require_once __DIR__ . '/../config/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user']) || (($_SESSION['user']['role'] ?? '') !== 'admin')) {
    header('Location: login.php');
    exit;
}
