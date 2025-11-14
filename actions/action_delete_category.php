<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../db_connect.php';

$user_id = $_SESSION['user_id'];
$category_id = $_GET['id']; 


if (!is_numeric($category_id)) {
    header("Location: ../categories.php?status=error");
    exit;
}




$stmt = $conn->prepare("DELETE FROM Categories WHERE category_id = ? AND user_id = ?");
$stmt->bind_param("ii", $category_id, $user_id);

$status = 'error';
if ($stmt->execute()) {
    
    $status = 'success';
}


$stmt->close();
$conn->close();


header("Location: ../categories.php?status=" . $status);
exit;
?>