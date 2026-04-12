<?php
include "config.php";
header('Content-Type: application/json');

// ===== ACTION =====
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== ADD =====
if ($action == "add") {
    $malop = $_POST['malop'];
    $tenlop = $_POST['tenlop'];
    $khoa = $_POST['khoa'];

    $sql = "INSERT INTO lop (malop, tenlop, khoa)
            VALUES ('$malop','$tenlop','$khoa')";

    if (!mysqli_query($conn, $sql)) {
        echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
        exit;
    }

    echo json_encode(["status"=>"success"]);
    exit;
}

// ===== DELETE =====
if ($action == "delete") {
    $malop = $_POST['malop'];

    $sql = "DELETE FROM lop WHERE malop='$malop'";

    if (!mysqli_query($conn, $sql)) {
        echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
        exit;
    }

    echo json_encode(["status"=>"success"]);
    exit;
}

// ===== UPDATE =====
if ($action == "update") {
    $malop = $_POST['malop'];
    $tenlop = $_POST['tenlop'];
    $khoa = $_POST['khoa'];

    $sql = "UPDATE lop 
            SET tenlop='$tenlop', khoa='$khoa'
            WHERE malop='$malop'";

    if (!mysqli_query($conn, $sql)) {
        echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
        exit;
    }

    echo json_encode(["status"=>"success"]);
    exit;
}

// ===== LIST =====
$result = mysqli_query($conn, "SELECT * FROM lop");
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);