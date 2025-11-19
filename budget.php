<?php
// budget.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Nếu user gửi form
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    // Lấy month/year nếu user chọn (nếu không, mặc định tháng hiện tại)
    $month = isset($_POST['month']) ? (int)$_POST['month'] : (int)date('n');
    $year  = isset($_POST['year']) ? (int)$_POST['year'] : (int)date('Y');
    $amount = floatval($_POST['amount']);

    if ($amount < 0) {
        $message = "<p style='color:red;'>Ngân sách phải >= 0</p>";
    } else {
        // INSERT ... ON DUPLICATE KEY UPDATE safer with prepared stmt
        $stmt = $conn->prepare("
            INSERT INTO budget (user_id, month, year, amount)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE amount = VALUES(amount), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("iiid", $user_id, $month, $year, $amount);
        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Đã cập nhật ngân sách cho $month/$year</p>";
        } else {
            $message = "<p style='color:red;'>Lỗi: " . htmlspecialchars($stmt->error) . "</p>";
        }
        $stmt->close();
    }
}

// Lấy ngân sách đang có (mặc định tháng hiện tại)
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$selected_year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$stmt = $conn->prepare("SELECT amount FROM budget WHERE user_id = ? AND month = ? AND year = ?");
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$res = $stmt->get_result();
$current_budget = 0;
if ($row = $res->fetch_assoc()) {
    $current_budget = floatval($row['amount']);
}
$stmt->close();

// Tính tổng đã chi trong tháng đã chọn
$stmt2 = $conn->prepare("
    SELECT COALESCE(SUM(amount),0) AS total_spent
    FROM transactions
    WHERE user_id = ?
      AND MONTH(transaction_date) = ?
      AND YEAR(transaction_date) = ?
");
$stmt2->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt2->execute();
$res2 = $stmt2->get_result();
$total_spent = 0;
if ($r2 = $res2->fetch_assoc()) $total_spent = floatval($r2['total_spent']);
$stmt2->close();

$conn->close();
?>
<?php require 'header.php'; ?>
<main style="padding:20px">
    <h2>Ngân sách tháng</h2>
    <?php echo $message; ?>

    <form method="post" action="budget.php" style="max-width:500px;">
        <label>Chọn tháng / năm:</label><br>
        <select name="month" style="width:120px;">
            <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?php echo $m; ?>" <?php echo ($m==$selected_month)?'selected':''; ?>><?php echo $m; ?></option>
            <?php endfor; ?>
        </select>
        <select name="year" style="width:120px;">
            <?php
            $start = date('Y') - 2;
            $end = date('Y') + 2;
            for($y=$start;$y<=$end;$y++):
            ?>
                <option value="<?php echo $y; ?>" <?php echo ($y==$selected_year)?'selected':''; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
        </select>
        <br><br>

        <label>Ngân sách (VND):</label><br>
        <input type="number" name="amount" value="<?php echo htmlspecialchars($current_budget); ?>" min="0" required style="width:200px;padding:6px;">
        <br><br>
        <button type="submit">Lưu ngân sách</button>
    </form>

    <hr>

    <h3>Tổng quan cho <?php echo $selected_month . '/' . $selected_year; ?></h3>
    <p>Ngân sách: <strong><?php echo number_format($current_budget); ?> VND</strong></p>
    <p>Đã chi: <strong><?php echo number_format($total_spent); ?> VND</strong></p>
    <p>Còn lại: <strong><?php echo number_format($current_budget - $total_spent); ?> VND</strong></p>

    <?php
    // Show warning levels
    if ($current_budget > 0) {
        $percent = ($total_spent / $current_budget) * 100;
        if ($percent >= 100) {
            echo "<div style='padding:12px;background:#ffdddd;color:#b30000;border-left:5px solid red;border-radius:6px;'>⚠ Bạn đã vượt ngân sách tháng!</div>";
        } elseif ($percent >= 80) {
            echo "<div style='padding:12px;background:#fff3cd;color:#856404;border-left:5px solid #ffc107;border-radius:6px;'>⚠ Bạn đã sử dụng $percent% ngân sách tháng (>=80%)</div>";
        } elseif ($percent >= 50) {
            echo "<div style='padding:12px;background:#e7f3ff;color:#0b66c3;border-left:5px solid #66b0ff;border-radius:6px;'>ℹ Bạn đã sử dụng $percent% ngân sách tháng</div>";
        }
    } else {
        echo "<p style='color:#666;'>Chưa có ngân sách cho tháng này.</p>";
    }
    ?>
</main>
<?php require 'footer.php'; ?>
