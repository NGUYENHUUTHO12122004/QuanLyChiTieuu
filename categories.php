<?php
// categories.php
session_start(); // Bắt đầu session


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Kết nối DB
require 'db_connect.php'; 
$current_user_id = $_SESSION['user_id'];


$message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $message = "<p style='color:green;'>Thao tác thành công!</p>";
    } else if ($_GET['status'] == 'error') {
        $message = "<p style='color:red;'>Có lỗi xảy ra.</p>";
    }
}


$income_categories_result = $conn->query(
    "SELECT * FROM Categories WHERE user_id = $current_user_id AND type = 'income'"
);
$expense_categories_result = $conn->query(
    "SELECT * FROM Categories WHERE user_id = $current_user_id AND type = 'expense'"
);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Quản lý Danh mục</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    .container {
        display: flex;
        gap: 20px;
    }

    .col {
        flex: 1;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    th {
        background-color: #f2f2f2;
    }

    .delete-btn {
        color: red;
        text-decoration: none;
    }
    </style>
</head>

<body>
    <header>
        <h1>Quản lý Danh mục</h1>
        <p>Chào, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <nav>
            <a href="dashboard.php">Bảng điều khiển</a> |
            <a href="actions/action_logout.php">Đăng xuất</a>
        </nav>
    </header>

    <main>
        <?php echo $message; ?>

        <section class="add-category">
            <h2>Thêm Danh mục mới</h2>
            <form action="actions/action_add_category.php" method="POST">
                <label>Tên danh mục:</label>
                <input type="text" name="name" placeholder="Ví dụ: Ăn trưa" required>

                <label>Loại danh mục:</label>
                <select name="type" required>
                    <option value="expense">Chi tiêu (Expense)</option>
                    <option value="income">Thu nhập (Income)</option>
                </select>

                <button type="submit">Thêm mới</button>
            </form>
        </section>

        <hr>

        <section class="category-list">
            <div class="container">
                <div class="col">
                    <h3>Danh mục Chi tiêu</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($expense_categories_result->num_rows > 0) {
                                while ($row = $expense_categories_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td><a class='delete-btn' href='actions/action_delete_category.php?id={$row['category_id']}' onclick='return confirm(\"Bạn có chắc chắn muốn xóa?\")'>Xóa</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2'>Chưa có danh mục chi tiêu.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col">
                    <h3>Danh mục Thu nhập</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($income_categories_result->num_rows > 0) {
                                while ($row = $income_categories_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td><a class='delete-btn' href='actions/action_delete_category.php?id={$row['category_id']}' onclick='return confirm(\"Bạn có chắc chắn muốn xóa?\")'>Xóa</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2'>Chưa có danh mục thu nhập.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>

</html>

<?php
$conn->close();
?>