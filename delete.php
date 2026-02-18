<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Only admin can delete
if ($_SESSION['role'] != 'admin') {
    die("Access denied. Only admin can delete posts.");
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: dashboard.php");
exit();
?>
