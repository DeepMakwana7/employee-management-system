<?php

require_once 'config.php';

// Get employee ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Delete employee
try {
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php?success=1");
    exit();
} catch(PDOException $e) {
    header("Location: index.php?error=1");
    exit();
}
?>