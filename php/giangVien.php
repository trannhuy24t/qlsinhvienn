<?php
include "config.php";
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ================= GIẢNG VIÊN =================

// ===== ADD =====
if ($action == "add") {
    $stmt = $conn->prepare("INSERT INTO giangvien (magv, hoten, email, sodienthoai, chuyennganh) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss",
        $_POST['magv'],
        $_POST['hoten'],
        $_POST['email'],
        $_POST['sodienthoai'],
        $_POST['chuyennganh']
    );

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","error"=>$stmt->error]);
    }
    exit;
}

// ===== UPDATE =====
if ($action == "update") {
    $stmt = $conn->prepare("UPDATE giangvien 
        SET hoten=?, email=?, sodienthoai=?, chuyennganh=? 
        WHERE magv=?");

    $stmt->bind_param("sssss",
        $_POST['hoten'],
        $_POST['email'],
        $_POST['sodienthoai'],
        $_POST['chuyennganh'],
        $_POST['magv']
    );

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","error"=>$stmt->error]);
    }
    exit;
}

// ===== DELETE GIẢNG VIÊN (Xóa cả hồ sơ và các phân công liên quan) =====
if ($action == "delete") {
    $magv = $_POST['magv'];

    // Xóa phân công trước để tránh lỗi khóa ngoại
    $stmt1 = $conn->prepare("DELETE FROM phancong WHERE magv=?");
    $stmt1->bind_param("s", $magv);
    $stmt1->execute();

    // Xóa giảng viên
    $stmt2 = $conn->prepare("DELETE FROM giangvien WHERE magv=?");
    $stmt2->bind_param("s", $magv);

    if ($stmt2->execute()) {
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","error"=>$stmt2->error]);
    }
    exit;
}

// ================= PHÂN CÔNG =================

// ===== ASSIGN (Thêm mới phân công) =====
if ($action == "assign") {
    $stmt = $conn->prepare("INSERT INTO phancong (magv, mamon) VALUES (?, ?)");
    $stmt->bind_param("ss",
        $_POST['magv'],
        $_POST['mamon']
    );

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","error"=>$stmt->error]);
    }
    exit;
}

// ===== UPDATE PHÂN CÔNG (Sửa dòng phân công cụ thể) =====
if ($action == "updateAssign") {
    // Giả sử bảng phancong có cột 'id' là khóa chính
    $id = $_POST['id']; 
    $magv = $_POST['magv'];
    $mamon = $_POST['mamon'];

    $stmt = $conn->prepare("UPDATE phancong SET magv=?, mamon=? WHERE id=?");
    $stmt->bind_param("ssi", $magv, $mamon, $id);

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","error"=>$stmt->error]);
    }
    exit;
}

// ===== DELETE PHÂN CÔNG (Chỉ xóa dòng phân công, giữ lại giảng viên) =====
if ($action == "deleteAssign") {
    $id = $_POST['id']; // Xóa theo ID của bảng phân công

    $stmt = $conn->prepare("DELETE FROM phancong WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","error"=>$stmt->error]);
    }
    exit;
}

// ================= LIST =================

// ===== LIST GV =====
if ($action == "listGV") {
    $rs = mysqli_query($conn,"SELECT * FROM giangvien");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

// ===== LIST MÔN =====
if ($action == "listMon") {
    $rs = mysqli_query($conn,"SELECT * FROM monhoc");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

// ===== LIST PHÂN CÔNG =====
if ($action == "listAssign") {
    // Lấy thêm cột id của bảng phancong để làm căn cứ Sửa/Xóa sau này
    $rs = mysqli_query($conn,"
        SELECT p.id, p.magv, p.mamon, g.hoten, g.chuyennganh, m.tenmon
        FROM phancong p
        JOIN giangvien g ON p.magv = g.magv
        JOIN monhoc m ON p.mamon = m.mamon
    ");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

// ===== DEFAULT =====
echo json_encode(["status"=>"invalid action"]);
?>