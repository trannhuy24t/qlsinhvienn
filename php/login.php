<?php
session_start();
include "config.php";

header('Content-Type: application/json; charset=utf-8');

// ===== LẤY DỮ LIỆU =====
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// ===== VALIDATE =====
if ($username === "" || $password === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Vui lòng nhập đầy đủ username và mật khẩu!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== QUERY =====
$sql = "SELECT * FROM taikhoan WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);

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

// ===== CHECK USER =====
if ($result && mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);

    if (password_verify($password, $row['password'])) {
        $_SESSION['hoten'] = $row['username']; // Hoặc $row['hoten'] nếu bảng có cột này
        $_SESSION['user'] = [
            'id'       => $row['id'],
            'username' => $row['username'],
            'role'     => $row['role']
        ];

        // ===== LƯU SESSION =====
        $_SESSION['user'] = [
            'id'       => $row['id'],
            'username' => $row['username'],
            'role'     => $row['role']
        ];

        echo json_encode([
            "status" => "success",
            "message" => "Đăng nhập thành công!",
            "redirect" => "../php/home.php"
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Sai mật khẩu!"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ===== KHÔNG TỒN TẠI =====
echo json_encode([
    "status" => "error",
    "message" => "Tài khoản không tồn tại!"
], JSON_UNESCAPED_UNICODE);
exit;
?>