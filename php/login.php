<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../php/config.php";
/** @var mysqli $conn */

header('Content-Type: application/json; charset=utf-8');

// ===== LẤY DỮ LIỆU =====
$ma = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// ===== KIỂM TRA RỖNG =====
if ($ma === "" || $password === "") {

    echo json_encode([
        "status"  => "error",
        "message" => "Vui lòng nhập đầy đủ Mã số và mật khẩu!"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ===== TÌM TÀI KHOẢN =====
$sql = "
    SELECT id, username, password, role, user_id
    FROM taikhoan
    WHERE user_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    echo json_encode([
        "status"  => "error",
        "message" => "Lỗi SQL: " . mysqli_error($conn)
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_bind_param($stmt, "s", $ma);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

// ===== KIỂM TRA TÀI KHOẢN =====
if ($result && mysqli_num_rows($result) === 1) {

    $row = mysqli_fetch_assoc($result);

    // ===== KIỂM TRA PASSWORD =====
    if (password_verify($password, $row['password'])) {

        session_regenerate_id(true);

        // ===== SESSION CHUNG =====
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role']    = $row['role'];
        $_SESSION['hoten']   = $row['username'];

        // ===== SINH VIÊN =====
        if ($row['role'] == 'sinhvien') {

            // FIX: user_id = masv trong bảng sinhvien
            $masv = $row['user_id'];

            $_SESSION['masv'] = $masv;

            // ===== LẤY HỌ TÊN THẬT + THÔNG TIN LỚP =====
            $sqlSV = "
                SELECT sv.hoten, sv.malop, l.tenlop
                FROM sinhvien sv
                LEFT JOIN lop l
                    ON sv.malop = l.malop
                WHERE sv.masv = ?
            ";

            $stmtSV = mysqli_prepare($conn, $sqlSV);

            if ($stmtSV) {

                mysqli_stmt_bind_param($stmtSV, "s", $masv);

                mysqli_stmt_execute($stmtSV);

                $resultSV = mysqli_stmt_get_result($stmtSV);

                if ($sv = mysqli_fetch_assoc($resultSV)) {

                    // FIX: lưu họ tên thật từ bảng sinhvien
                    $_SESSION['hoten']  = $sv['hoten'];
                    $_SESSION['malop']  = $sv['malop'];
                    $_SESSION['tenlop'] = $sv['tenlop'];

                } else {

                    $_SESSION['malop']  = '';
                    $_SESSION['tenlop'] = '';
                }

            } else {

                $_SESSION['malop']  = '';
                $_SESSION['tenlop'] = '';
            }
        }

        // ===== GIẢNG VIÊN =====
        if ($row['role'] == 'giangvien') {

            $_SESSION['magv'] = $row['user_id'];
        }

        // ===== ADMIN =====
        if ($row['role'] == 'admin') {

            $_SESSION['admin'] = $row['user_id'];
        }

        // ===== SUCCESS =====
        echo json_encode([
            "status"   => "success",
            "message"  => "Đăng nhập thành công!",
            "redirect" => "../php/home.php"
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
        "message" => "Mã số chưa được đăng ký!"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
?>