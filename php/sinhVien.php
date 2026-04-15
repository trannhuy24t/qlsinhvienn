<?php
session_start();
include "config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== ADD =====
if ($action == "add") {
    $masv = $_POST['masv'];
    $hoten = $_POST['hoten'];
    $ngaysinh = $_POST['ngaysinh'];
    $gioitinh = $_POST['gioitinh'];
    $email = $_POST['email'];
    $sodienthoai = $_POST['sodienthoai'];
    $diachi = $_POST['diachi'];
    $malop = $_POST['malop'];

// Sửa đoạn này trong sinhVien.php
$anh = "";
if (!empty($_FILES['anh']['name'])) {
    $anh = time() . "_" . $_FILES['anh']['name'];
    
    // Đảm bảo đường dẫn này đúng: từ file php nhảy ra ngoài rồi vào thư mục images
    $target_dir = "../images/"; 
    
    // Kiểm tra nếu thư mục chưa tồn tại thì tạo mới hoặc báo lỗi nhẹ nhàng
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    move_uploaded_file($_FILES['anh']['tmp_name'], $target_dir . $anh);
}

    $sql = "INSERT INTO sinhvien 
    (masv, hoten, ngaysinh, gioitinh, email, sodienthoai, diachi, malop, anh)
    VALUES 
    ('$masv','$hoten','$ngaysinh','$gioitinh','$email','$sodienthoai','$diachi','$malop','$anh')";

    if (!mysqli_query($conn, $sql)) {
        echo json_encode([
            "status" => "error",
            "message" => mysqli_error($conn)
        ]);
        exit;
    }

    echo json_encode(["status" => "success"]);
    exit;
}

// ===== DELETE =====
// Trình duyệt cần biết đây là JSON
// ===== DELETE =====
if ($action == "delete") {
    $masv = mysqli_real_escape_string($conn, $_POST['masv']);

    // 1. Xóa điểm của sinh viên này trước để gỡ ràng buộc
    mysqli_query($conn, "DELETE FROM diem WHERE masv = '$masv'");

    // 2. Sau đó mới xóa sinh viên
    $sql = "DELETE FROM sinhvien WHERE masv = '$masv'";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => mysqli_error($conn)
        ]);
    }
    exit;
}

// ===== SEARCH =====
if ($action == "search") {
    $where = [];

    if (!empty($_POST['masv'])) {
        $where[] = "masv LIKE '%{$_POST['masv']}%'";
    }
    if (!empty($_POST['hoten'])) {
        $where[] = "hoten LIKE '%{$_POST['hoten']}%'";
    }
    if (!empty($_POST['malop'])) {
        $where[] = "malop = '{$_POST['malop']}'";
    }
    if (!empty($_POST['gioitinh'])) {
        $where[] = "gioitinh = '{$_POST['gioitinh']}'";
    }
    if (!empty($_POST['from']) && !empty($_POST['to'])) {
        $where[] = "ngaysinh BETWEEN '{$_POST['from']}' AND '{$_POST['to']}'";
    }

    $sql = "SELECT * FROM sinhvien";
    if (count($where) > 0) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $result = mysqli_query($conn, $sql);
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}

// ===== LIST =====
$result = mysqli_query($conn, "SELECT * FROM sinhvien");
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
exit;
?>