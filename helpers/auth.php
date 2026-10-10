<?php
// helpers/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth() {
    if (!isset($_SESSION['user'])) {
        header("Location: /inventory_app/public/login.php");
        exit;
    }
}