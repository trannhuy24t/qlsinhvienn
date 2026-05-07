<?php
session_start();
include "config.php";

/** @var mysqli $conn */ 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Đảm bảo luôn trả về JSON để JavaScript không bị lỗi "dữ liệu không hợp lệ"
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== 1. CHỨC NĂNG THÊM SINH VIÊN =====
if ($action == "add") {
    // Bảo mật dữ liệu đầu vào bằng mysqli_real_escape_string
    $masv = mysqli_real_escape_string($conn, $_POST['masv']);
    $hoten = mysqli_real_escape_string($conn, $_POST['hoten']);
    $ngaysinh = mysqli_real_escape_string($conn, $_POST['ngaysinh']);
    $gioitinh = mysqli_real_escape_string($conn, $_POST['gioitinh']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sodienthoai = mysqli_real_escape_string($conn, $_POST['sodienthoai']);
    $diachi = mysqli_real_escape_string($conn, $_POST['diachi']);
    $malop = mysqli_real_escape_string($conn, $_POST['malop']);

    // Kiểm tra trùng mã sinh viên trước khi thêm
    $check = mysqli_query($conn, "SELECT masv FROM sinhvien WHERE masv = '$masv'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(["status" => "error", "message" => "Mã sinh viên '$masv' đã tồn tại!"]);
        exit;
    }

    // Xử lý upload ảnh
    $anh = "";
    if (!empty($_FILES['anh']['name'])) {
        $anh = time() . "_" . $_FILES['anh']['name'];
        $target_dir = "../images/"; 
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        move_uploaded_file($_FILES['anh']['tmp_name'], $target_dir . $anh);
    }

    $sql = "INSERT INTO sinhvien (masv, hoten, ngaysinh, gioitinh, email, sodienthoai, diachi, malop, anh)
            VALUES ('$masv','$hoten','$ngaysinh','$gioitinh','$email','$sodienthoai','$diachi','$malop','$anh')";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}

// ===== 2. CHỨC NĂNG XÓA SINH VIÊN =====
if ($action == "delete") {
    $masv = mysqli_real_escape_string($conn, $_POST['masv']);

    // Xóa dữ liệu liên quan ở bảng điểm trước (nếu có)
    mysqli_query($conn, "DELETE FROM diem WHERE masv = '$masv'");

    $sql = "DELETE FROM sinhvien WHERE masv = '$masv'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}

// ===== 3. CHỨC NĂNG CẬP NHẬT SINH VIÊN =====
if ($action == "update") {
    $masv = mysqli_real_escape_string($conn, $_POST['masv']);
    $hoten = mysqli_real_escape_string($conn, $_POST['hoten']);
    $ngaysinh = mysqli_real_escape_string($conn, $_POST['ngaysinh']);
    $gioitinh = mysqli_real_escape_string($conn, $_POST['gioitinh']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sodienthoai = mysqli_real_escape_string($conn, $_POST['sodienthoai']);
    $diachi = mysqli_real_escape_string($conn, $_POST['diachi']);
    $malop = mysqli_real_escape_string($conn, $_POST['malop']);

    $sql_img = "";
    if (!empty($_FILES['anh']['name'])) {
        $anh = time() . "_" . $_FILES['anh']['name'];
        $target_dir = "../images/";
        if (move_uploaded_file($_FILES['anh']['tmp_name'], $target_dir . $anh)) {
            $sql_img = ", anh = '$anh'";
        }
    }

    $sql = "UPDATE sinhvien SET 
            hoten = '$hoten', ngaysinh = '$ngaysinh', gioitinh = '$gioitinh', 
            email = '$email', sodienthoai = '$sodienthoai', diachi = '$diachi', 
            malop = '$malop' $sql_img 
            WHERE masv = '$masv'";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}

// ===== 4. CHỨC NĂNG TÌM KIẾM / LỌC THEO LỚP =====
if ($action == "search") {
    $where = [];

    if (!empty($_POST['masv'])) {
        $where[] = "sv.masv LIKE '%" . mysqli_real_escape_string($conn, $_POST['masv']) . "%'";
    }
    if (!empty($_POST['hoten'])) {
        $where[] = "sv.hoten LIKE '%" . mysqli_real_escape_string($conn, $_POST['hoten']) . "%'";
    }
    // Fix lỗi lọc theo lớp học
    if (!empty($_POST['malop'])) {
        $where[] = "sv.malop = '" . mysqli_real_escape_string($conn, $_POST['malop']) . "'";
    }
    if (!empty($_POST['gioitinh'])) {
        $where[] = "sv.gioitinh = '" . mysqli_real_escape_string($conn, $_POST['gioitinh']) . "'";
    }
    if (!empty($_POST['from']) && !empty($_POST['to'])) {
        $where[] = "sv.ngaysinh BETWEEN '{$_POST['from']}' AND '{$_POST['to']}'";
    }

    $sql = "SELECT sv.*, l.tenlop FROM sinhvien sv LEFT JOIN lop l ON sv.malop = l.malop";
    if (count($where) > 0) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    echo json_encode($data);
    exit;
}

// ===== 5. MẶC ĐỊNH: LIẾT KÊ TẤT CẢ SINH VIÊN =====
$sql = "SELECT sv.*, l.tenlop FROM sinhvien sv LEFT JOIN lop l ON sv.malop = l.malop";
$result = mysqli_query($conn, $sql);
$data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}
echo json_encode($data);
exit;
?>