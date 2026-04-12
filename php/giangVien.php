<?php
include "config.php";
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== GIẢNG VIÊN =====
if ($action == "add") {
    $sql = "INSERT INTO giangvien (magv, hoten, email, sodienthoai, chuyennganh)
            VALUES (
                '{$_POST['magv']}',
                '{$_POST['hoten']}',
                '{$_POST['email']}',
                '{$_POST['sodienthoai']}',
                '{$_POST['chuyennganh']}'
            )";

    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

if ($action == "update") {
    $sql = "UPDATE giangvien 
            SET hoten='{$_POST['hoten']}',
                email='{$_POST['email']}',
                sodienthoai='{$_POST['sodienthoai']}',
                chuyennganh='{$_POST['chuyennganh']}'
            WHERE magv='{$_POST['magv']}'";

    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

if ($action == "delete") {
    $sql = "DELETE FROM giangvien WHERE magv='{$_POST['magv']}'";
    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

// ===== PHÂN CÔNG =====
if ($action == "assign") {
    $sql = "INSERT INTO phancong (magv, mamon)
            VALUES ('{$_POST['magv']}','{$_POST['mamon']}')";
    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

// ===== LIST =====
if ($action == "listGV") {
    $rs = mysqli_query($conn,"SELECT * FROM giangvien");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

if ($action == "listMon") {
    $rs = mysqli_query($conn,"SELECT * FROM monhoc");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

if ($action == "listAssign") {
    $rs = mysqli_query($conn,"
        SELECT p.*, g.hoten, g.chuyennganh, m.tenmon
        FROM phancong p
        JOIN giangvien g ON p.magv = g.magv
        JOIN monhoc m ON p.mamon = m.mamon
    ");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}
?>