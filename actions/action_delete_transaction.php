<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../db_connect.php';

$user_id = $_SESSION['user_id'];
$transaction_id = $_GET['id'];

// Kiểm tra xem ID có phải số không
if (!is_numeric($transaction_id)) {
    header("Location: ../dashboard.php");
    exit;
}

// Xóa giao dịch CHỈ KHI nó thuộc về user đang đăng nhập
$stmt = $conn->prepare("DELETE FROM Transactions WHERE transaction_id = ? AND user_id = ?");
$stmt->bind_param("ii", $transaction_id, $user_id);

if ($stmt->execute()) {

    header("Location: ../dashboard.php");
    exit;
} else {
    // Thất bại
    echo "Có lỗi xảy ra, không thể xóa.";
}

$stmt->close();
$conn->close();
?>