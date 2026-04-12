<?php
// Hiển thị lỗi khi debug (có thể tắt khi chạy thật)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";
header('Content-Type: application/json; charset=utf-8');

// ===== LẤY DỮ LIỆU =====
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirmpassword = trim($_POST['confirmpassword'] ?? '');

// ===== VALIDATE =====
if ($username === "" || $password === "" || $confirmpassword === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Vui lòng nhập đầy đủ thông tin!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($password !== $confirmpassword) {
    echo json_encode([
        "status" => "error",
        "message" => "Mật khẩu xác nhận không khớp!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== CHECK USERNAME TỒN TẠI =====
$checkSql = "SELECT id FROM taikhoan WHERE username = ?";
$stmt = mysqli_prepare($conn, $checkSql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Lỗi prepare SQL!"
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Username đã được sử dụng!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== PHÂN QUYỀN =====
$role = "sinhvien";

// ===== MÃ HÓA PASSWORD =====
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ===== INSERT =====
$sql = "INSERT INTO taikhoan (username, password, role) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Lỗi prepare INSERT!"
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "sss", $username, $hashedPassword, $role);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => "success",
        "message" => "Đăng ký thành công!"
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Lỗi CSDL: " . mysqli_error($conn)
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>