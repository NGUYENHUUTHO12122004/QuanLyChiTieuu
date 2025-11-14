<?php

session_start();
require '../db_connect.php';

$email = $_POST['email'];
$password = $_POST['password'];

// 1. Tìm user bằng email
$stmt = $conn->prepare("SELECT user_id, username, password_hash FROM Users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    // 2. Lấy thông tin user
    $user = $result->fetch_assoc();

    // 3. So sánh mật khẩu đã băm
    if (password_verify($password, $user['password_hash'])) {
        
        // 4. Lưu thông tin vào SESSION
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        // 5. Chuyển hướng đến trang dashboard
        header("Location: ../dashboard.php");
        exit;
    } else {
      
        echo "Sai mật khẩu!";
    }
} else {
 
    echo "Không tìm thấy email!";
}

$stmt->close();
$conn->close();
?>