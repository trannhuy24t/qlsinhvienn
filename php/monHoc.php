<?php
include "config.php";
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== MON HOC =====
if ($action == "addMon") {
    $sql = "INSERT INTO monhoc (mamon, tenmon, sotinchi, mota)
            VALUES ('{$_POST['mamon']}','{$_POST['tenmon']}',{$_POST['sotinchi']},'{$_POST['mota']}')";
    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

if ($action == "updateMon") {
    $sql = "UPDATE monhoc 
            SET tenmon='{$_POST['tenmon']}', sotinchi={$_POST['sotinchi']}, mota='{$_POST['mota']}'
            WHERE mamon='{$_POST['mamon']}'";
    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

if ($action == "deleteMon") {
    $sql = "DELETE FROM monhoc WHERE mamon='{$_POST['mamon']}'";
    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

// ===== HOC KY =====
if ($action == "addHK") {
    $sql = "INSERT INTO hocky (mahocky, tenhocky, namhoc)
            VALUES ('{$_POST['mahocky']}','{$_POST['tenhocky']}','{$_POST['namhoc']}')";
    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

// ===== LOP HOC PHAN =====
if ($action == "addLHP") {
    $sql = "INSERT INTO lophocphan (malhp, mamon, magv, mahocky)
            VALUES ('{$_POST['malhp']}','{$_POST['mamon']}','{$_POST['magv']}','{$_POST['mahocky']}')";
    echo json_encode(["status"=> mysqli_query($conn,$sql) ? "success":"error"]);
    exit;
}

// ===== LIST =====
if ($action == "listMon") {
    $rs = mysqli_query($conn,"SELECT * FROM monhoc");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

if ($action == "listHK") {
    $rs = mysqli_query($conn,"SELECT * FROM hocky");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

if ($action == "listLHP") {
    $rs = mysqli_query($conn,"
        SELECT l.*, m.tenmon, h.tenhocky, h.namhoc 
        FROM lophocphan l
        JOIN monhoc m ON l.mamon = m.mamon
        JOIN hocky h ON l.mahocky = h.mahocky
    ");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}
?>