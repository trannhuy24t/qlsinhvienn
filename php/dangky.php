<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config.php";
header('Content-Type: application/json; charset=utf-8');

// ===== LẤY DỮ LIỆU TỪ FORM =====
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirmpassword = trim($_POST['confirmpassword'] ?? '');
$ma = trim($_POST['ma'] ?? ''); // Mã SV hoặc GV nhập từ ô "Mã SV / GV"

// ===== KIỂM TRA TRỐNG =====
if ($username === "" || $password === "" || $confirmpassword === "" || $ma === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Vui lòng nhập đầy đủ thông tin!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== KIỂM TRA KHỚP MẬT KHẨU =====
if ($password !== $confirmpassword) {
    echo json_encode([
        "status" => "error",
        "message" => "Mật khẩu xác nhận không khớp!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== 1. KIỂM TRA USERNAME ĐÃ TỒN TẠI CHƯA =====
$checkSql = "SELECT id FROM taikhoan WHERE username = ?";
$stmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Tên đăng nhập đã tồn tại!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== 2. XÁC THỰC MÃ SV/GV VÀ LẤY VAI TRÒ =====
$role = "";
$user_id_value = ""; // Biến này sẽ lưu mã SV hoặc mã GV

// Kiểm tra trong bảng sinhvien
$sql_sv = "SELECT masv FROM sinhvien WHERE masv = ?";
$stmt_sv = mysqli_prepare($conn, $sql_sv);
mysqli_stmt_bind_param($stmt_sv, "s", $ma);
mysqli_stmt_execute($stmt_sv);
$result_sv = mysqli_stmt_get_result($stmt_sv);

if ($result_sv && mysqli_num_rows($result_sv) > 0) {
    $row = mysqli_fetch_assoc($result_sv);
    $role = "sinhvien";
    $user_id_value = $row['masv'];
} else {
    // Nếu không phải SV, kiểm tra trong bảng giangvien
    $sql_gv = "SELECT magv FROM giangvien WHERE magv = ?";
    $stmt_gv = mysqli_prepare($conn, $sql_gv);
    mysqli_stmt_bind_param($stmt_gv, "s", $ma);
    mysqli_stmt_execute($stmt_gv);
    $result_gv = mysqli_stmt_get_result($stmt_gv);

    if ($result_gv && mysqli_num_rows($result_gv) > 0) {
        $row = mysqli_fetch_assoc($result_gv);
        $role = "giangvien";
        $user_id_value = $row['magv'];
    }
}

// Nếu mã không tồn tại ở cả 2 bảng
if ($user_id_value === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Mã sinh viên hoặc giảng viên không tồn tại trong hệ thống!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== 3. KIỂM TRA MÃ NÀY ĐÃ ĐƯỢC ĐĂNG KÝ TÀI KHOẢN CHƯA =====
$checkUser = "SELECT id FROM taikhoan WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $checkUser);
mysqli_stmt_bind_param($stmt, "s", $user_id_value); // "s" vì user_id giờ là chuỗi (masv/magv)
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Mã này đã có tài khoản rồi!"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== 4. MÃ HÓA MẬT KHẨU VÀ LƯU VÀO DATABASE =====
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO taikhoan (username, password, role, user_id) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
// Truyền vào 4 chuỗi (string): username, password, role, user_id
mysqli_stmt_bind_param($stmt, "ssss", $username, $hashedPassword, $role, $user_id_value);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "status" => "success",
        "message" => "Đăng ký thành công!"
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Lỗi hệ thống khi lưu: " . mysqli_error($conn)
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>