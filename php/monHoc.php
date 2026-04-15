<?php
include "config.php";
header('Content-Type: application/json; charset=utf-8');

// Bật báo lỗi để dễ debug nếu có vấn đề phát sinh
error_reporting(E_ALL);
ini_set('display_errors', 0); // Đặt là 0 để không làm hỏng định dạng JSON khi có lỗi nhỏ

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== MON HOC =====
if ($action == "addMon") {
    $mamon = mysqli_real_escape_string($conn, $_POST['mamon']);
    $tenmon = mysqli_real_escape_string($conn, $_POST['tenmon']);
    $sotinchi = (int)$_POST['sotinchi'];
    $mota = mysqli_real_escape_string($conn, $_POST['mota']);

    $sql = "INSERT INTO monhoc (mamon, tenmon, sotinchi, mota)
            VALUES ('$mamon','$tenmon',$sotinchi,'$mota')";
    
    echo json_encode(["status" => mysqli_query($conn, $sql) ? "success" : "error", "message" => mysqli_error($conn)]);
    exit;
}

if ($action == "updateMon") {
    $mamon = mysqli_real_escape_string($conn, $_POST['mamon']);
    $tenmon = mysqli_real_escape_string($conn, $_POST['tenmon']);
    $sotinchi = (int)$_POST['sotinchi'];
    $mota = mysqli_real_escape_string($conn, $_POST['mota']);

    $sql = "UPDATE monhoc 
            SET tenmon='$tenmon', sotinchi=$sotinchi, mota='$mota'
            WHERE mamon='$mamon'";
            
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}

if ($action == "deleteMon") {
    $mamon = mysqli_real_escape_string($conn, $_POST['mamon']);
    $sql = "DELETE FROM monhoc WHERE mamon='$mamon'";
    echo json_encode(["status" => mysqli_query($conn, $sql) ? "success" : "error", "message" => mysqli_error($conn)]);
    exit;
}

// ===== HOC KY =====
// ===== HOC KY =====
if ($action == "addHK") {
    $mahocky = $_POST['mahocky'];
    $tenhocky = $_POST['tenhocky'];
    $namhoc = $_POST['namhoc'];

    // 1. Kiểm tra xem mã học kỳ này đã tồn tại chưa
    $checkQuery = "SELECT * FROM hocky WHERE mahocky = '$mahocky'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Nếu đã tồn tại, trả về lỗi cho JavaScript
        echo json_encode(["status" => "error", "message" => "Mã học kỳ này đã tồn tại!"]);
    } else {
        // 2. Nếu chưa có thì mới tiến hành thêm
        $sql = "INSERT INTO hocky (mahocky, tenhocky, namhoc)
                VALUES ('$mahocky','$tenhocky','$namhoc')";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }
    exit;
}

// Thêm chức năng Xóa Học Kỳ (để fix lỗi xóa bị mất hết)
if ($action == "deleteHK") {
    $mahocky = mysqli_real_escape_string($conn, $_POST['mahocky']);
    $sql = "DELETE FROM hocky WHERE mahocky='$mahocky'";
    echo json_encode(["status" => mysqli_query($conn, $sql) ? "success" : "error", "message" => mysqli_error($conn)]);
    exit;
}
// ===== UPDATE MON HOC =====
// if ($action == "updateMon") {
//     $mamon = $_POST['mamon'];
//     $tenmon = $_POST['tenmon'];
//     $sotinchi = $_POST['sotinchi'];
//     $mota = $_POST['mota'];

//     $sql = "UPDATE monhoc SET tenmon='$tenmon', sotinchi=$sotinchi, mota='$mota' WHERE mamon='$mamon'";
//     echo json_encode(["status" => mysqli_query($conn, $sql) ? "success" : "error"]);
//     exit;
// }

// ===== UPDATE HOC KY =====
if ($action == "updateHK") {
    $mahocky = $_POST['mahocky'];
    $tenhocky = $_POST['tenhocky'];
    $namhoc = $_POST['namhoc'];

    $sql = "UPDATE hocky SET tenhocky='$tenhocky', namhoc='$namhoc' WHERE mahocky='$mahocky'";
    echo json_encode(["status" => mysqli_query($conn, $sql) ? "success" : "error"]);
    exit;
}

// ===== LOP HOC PHAN =====
if ($action == "addLHP") {
    $malhp = mysqli_real_escape_string($conn, $_POST['malhp']);
    $mamon = mysqli_real_escape_string($conn, $_POST['mamon']);
    $magv = mysqli_real_escape_string($conn, $_POST['magv']);
    $mahocky = mysqli_real_escape_string($conn, $_POST['mahocky']);

    $sql = "INSERT INTO lophocphan (malhp, mamon, magv, mahocky)
            VALUES ('$malhp','$mamon','$magv','$mahocky')";
    echo json_encode(["status" => mysqli_query($conn, $sql) ? "success" : "error", "message" => mysqli_error($conn)]);
    exit;
}
// ===== XÓA LỚP HỌC PHẦN =====
if ($action == "deleteLHP") {
    $malhp = mysqli_real_escape_string($conn, $_POST['malhp']);
    $sql = "DELETE FROM lophocphan WHERE malhp='$malhp'";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}

// ===== CẬP NHẬT LỚP HỌC PHẦN =====
if ($action == "updateLHP") {
    $malhp = mysqli_real_escape_string($conn, $_POST['malhp']);
    $mamon = mysqli_real_escape_string($conn, $_POST['mamon']);
    $magv = mysqli_real_escape_string($conn, $_POST['magv']);
    $mahocky = mysqli_real_escape_string($conn, $_POST['mahocky']);

    $sql = "UPDATE lophocphan 
            SET mamon='$mamon', magv='$magv', mahocky='$mahocky' 
            WHERE malhp='$malhp'";
            
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
    exit;
}
// ===== LIST =====
if ($action == "listMon") {
    $rs = mysqli_query($conn, "SELECT * FROM monhoc");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

if ($action == "listHK") {
    // Sử dụng DISTINCT để tránh lặp dữ liệu học kỳ trên giao diện
    $rs = mysqli_query($conn, "SELECT DISTINCT mahocky, tenhocky, namhoc FROM hocky");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

if ($action == "listLHP") {
    $rs = mysqli_query($conn, "
        SELECT l.*, m.tenmon, h.tenhocky, h.namhoc 
        FROM lophocphan l
        JOIN monhoc m ON l.mamon = m.mamon
        JOIN hocky h ON l.mahocky = h.mahocky
    ");
    $data = mysqli_fetch_all($rs, MYSQLI_ASSOC);
    echo json_encode($data ? $data : []); // Trả về mảng rỗng thay vì null để tránh lỗi JS
    exit;
}
?>