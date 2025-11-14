<?php
// edit_transaction.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
$current_user_id = $_SESSION['user_id'];
$transaction_id = $_GET['id'];

// Bảo mật: Kiểm tra ID
if (!is_numeric($transaction_id)) {
    die("ID không hợp lệ.");
}

// 1. Lấy thông tin giao dịch CŨ
$stmt = $conn->prepare("
    SELECT t.*, c.type AS category_type 
    FROM Transactions t 
    JOIN Categories c ON t.category_id = c.category_id
    WHERE t.transaction_id = ? AND t.user_id = ?
");
$stmt->bind_param("ii", $transaction_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Giao dịch không tồn tại hoặc không thuộc về user này
    die("Không tìm thấy giao dịch.");
}
$transaction = $result->fetch_assoc(); // $transaction chứa dữ liệu cũ

// 2. Lấy TẤT CẢ danh mục của user (cho dropdown)
$categories_result = $conn->query(
    "SELECT * FROM Categories WHERE user_id = $current_user_id"
);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Sửa Giao dịch</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    form {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    label {
        display: block;
        font-weight: bold;
        margin-top: 12px;
    }

    input[type="number"],
    input[type="date"],
    select,
    textarea {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }

    button {
        width: 100%;
        padding: 10px;
        margin-top: 15px;
        color: white;
        font-weight: bold;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        background-color: #007bff;
    }
    </style>
</head>

<body>
    <header>
        <h1>Sửa Giao dịch</h1>
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </header>

    <form action="actions/action_update_transaction.php" method="POST">
        <input type="hidden" name="transaction_id" value="<?php echo $transaction['transaction_id']; ?>">

        <label>Số tiền:</label>
        <input type="number" name="amount" value="<?php echo $transaction['amount']; ?>" required>

        <label>Ngày:</label>
        <input type="date" name="date" value="<?php echo $transaction['transaction_date']; ?>" required>

        <label>Danh mục:</label>
        <select name="category_id" required>
            <option value="">-- Chọn danh mục --</option>
            <?php
            if ($categories_result->num_rows > 0) {
                while ($row = $categories_result->fetch_assoc()) {
                    // Kiểm tra xem ID của danh mục này có khớp với ID đã lưu không
                    $selected = ($row['category_id'] == $transaction['category_id']) ? 'selected' : '';
                    echo "<option value='{$row['category_id']}' {$selected}>
                            {$row['name']} ({$row['type']})
                          </option>";
                }
            }
            ?>
        </select>

        <label>Ghi chú:</label>
        <textarea name="description"><?php echo htmlspecialchars($transaction['description']); ?></textarea>

        <button type="submit">Cập nhật Giao dịch</button>
    </form>
</body>

</html>