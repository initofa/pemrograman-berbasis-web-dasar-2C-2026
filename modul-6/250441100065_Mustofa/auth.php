<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['nisn']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
 
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirectIfNotAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: index.php');
        exit();
    }
}
?>