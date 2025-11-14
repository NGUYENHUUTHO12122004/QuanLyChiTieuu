<?php
// actions/action_update_transaction.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require '../db_connect.php';

// 1. Lấy dữ liệu từ form
$user_id = $_SESSION['user_id'];
$transaction_id = $_POST['transaction_id'];
$amount = $_POST['amount'];
$transaction_date = $_POST['date'];
$category_id = $_POST['category_id'];
$description = $_POST['description'];

// 2. Kiểm tra dữ liệu
if (!is_numeric($transaction_id) || !is_numeric($category_id) || $amount <= 0) {
    die("Dữ liệu không hợp lệ.");
}

// Bảo mật: Thêm "AND user_id = ?" để đảm bảo user này chỉ update được giao dịch của chính họ
$stmt = $conn->prepare("
    UPDATE Transactions 
    SET amount = ?, transaction_date = ?, category_id = ?, description = ?
    WHERE transaction_id = ? AND user_id = ?
");

// "dsisii" -> double, string, integer, string, integer, integer
$stmt->bind_param("dsisii", $amount, $transaction_date, $category_id, $description, $transaction_id, $user_id);

// 4. Thực thi và chuyển hướng
if ($stmt->execute()) {
    // Cập nhật thành công, quay về dashboard
    header("Location: ../dashboard.php");
    exit;
} else {
    // Thất bại
    echo "Lỗi: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>