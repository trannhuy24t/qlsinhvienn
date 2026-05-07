<?php
session_start();
// Hiển thị lỗi để debug (Xóa dòng này khi đưa lên chạy thật)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../php/config.php";
/** @var mysqli $conn */ // Thêm dòng này để IDE nhận diện $conn là mysqli

header('Content-Type: application/json; charset=utf-8');

// 1. LẤY DỮ LIỆU
$ma = trim($_POST['username'] ?? ''); 
$password = trim($_POST['password'] ?? '');

// 2. KIỂM TRA TRỐNG
if ($ma === "" || $password === "") {
    echo json_encode([
        "status"  => "error",
        "message" => "Vui lòng nhập đầy đủ Mã số và mật khẩu!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. TRUY VẤN
// Lưu ý: user_id phải khớp với kiểu dữ liệu VARCHAR trong DB
$sql  = "SELECT * FROM taikhoan WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        "status"  => "error",
        "message" => "Lỗi CSDL: " . mysqli_error($conn) 
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $ma);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 4. KIỂM TRA TÀI KHOẢN
if ($result && mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);

    // Kiểm tra mật khẩu (Sử dụng hash)
    // Trong file login.php, phần xử lý đăng nhập thành công
if (password_verify($password, $row['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['role']    = $row['role'];
    $_SESSION['hoten']   = $row['username']; // THÊM DÒNG NÀY để home.php nhận diện được
    
    echo json_encode([
        "status"   => "success",
        "message"  => "Đăng nhập thành công!",
        "redirect" => "../php/home.php" // Sửa lại đường dẫn redirect cho chuẩn
    ], JSON_UNESCAPED_UNICODE);
    exit;
} else {
        echo json_encode([
            "status"  => "error",
            "message" => "Mật khẩu không chính xác!"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Mã số này chưa được đăng ký tài khoản!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>