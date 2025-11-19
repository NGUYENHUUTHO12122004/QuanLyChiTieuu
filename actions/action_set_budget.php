<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$budget = $_POST['budget_amount'];

$month = date("n");   // Tháng (1–12)
$year  = date("Y");   // Năm

// Kiểm tra xem ngân sách tháng này đã tồn tại chưa
$exists = $conn->query("
    SELECT * FROM budget 
    WHERE user_id = $user_id 
      AND month = $month 
      AND year = $year
");

if ($exists->num_rows > 0) {
    // Đã tồn tại → cập nhật
    $conn->query("
        UPDATE budget 
        SET amount = $budget 
        WHERE user_id = $user_id 
          AND month = $month 
          AND year = $year
    ");
} else {
    // Chưa có → thêm mới
    $conn->query("
        INSERT INTO budget (user_id, month, year, amount)
        VALUES ($user_id, $month, $year, $budget)
    ");
}

header("Location: ../dashboard.php");
exit;
