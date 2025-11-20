<?php
header('Content-Type: application/json; charset=utf-8');
require 'currency.php';

if (!isset($_GET['amount'])) {
    echo json_encode(["ok" => false, "error" => "Thiếu amount"]);
    exit;
}

$amount = floatval($_GET['amount']);
if ($amount <= 0) {
    echo json_encode(["ok" => false, "error" => "Số tiền không hợp lệ"]);
    exit;
}

$usd = vnd_to_usd($amount);

echo json_encode([
    "ok" => true,
    "vnd" => $amount,
    "usd" => round($usd, 2)
]);
exit;
