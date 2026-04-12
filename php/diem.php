<?php
include "config.php";
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== TÍNH XẾP LOẠI =====
function xepLoai($diem){
    if ($diem >= 8.5) return "A";
    if ($diem >= 7) return "B";
    if ($diem >= 5.5) return "C";
    if ($diem >= 4) return "D";
    return "F";
}

// ===== LƯU ĐIỂM =====
if ($action == "save") {

    $masv = $_POST['masv'];
    $malhp = $_POST['malhp'];
    $cc = $_POST['diemchuyencan'];
    $gk = $_POST['diemgiuaky'];
    $ck = $_POST['diemcuoiky'];

    // 👉 TÍNH ĐIỂM TỔNG
    $tong = round($cc*0.1 + $gk*0.3 + $ck*0.6, 2);

    // 👉 XẾP LOẠI
    $xeploai = xepLoai($tong);

    $check = mysqli_query($conn,"SELECT * FROM diem WHERE masv='$masv' AND malhp='$malhp'");

    if (mysqli_num_rows($check) > 0) {
        $sql = "UPDATE diem SET 
            diemchuyencan='$cc',
            diemgiuaky='$gk',
            diemcuoiky='$ck',
            diemtong='$tong',
            xeploai='$xeploai'
            WHERE masv='$masv' AND malhp='$malhp'";
    } else {
        $sql = "INSERT INTO diem 
        (masv, malhp, diemchuyencan, diemgiuaky, diemcuoiky, diemtong, xeploai)
        VALUES 
        ('$masv','$malhp','$cc','$gk','$ck','$tong','$xeploai')";
    }

    echo json_encode([
        "status" => mysqli_query($conn,$sql) ? "success" : "error"
    ]);
    exit;
}

// ===== LOAD ĐIỂM =====
if ($action == "list") {
    $rs = mysqli_query($conn,"
        SELECT d.*, sv.hoten, mh.tenmon
        FROM diem d
        JOIN sinhvien sv ON d.masv = sv.masv
        JOIN lophocphan l ON d.malhp = l.malhp
        JOIN monhoc mh ON l.mamon = mh.mamon
    ");

    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

// ===== GPA =====
if ($action == "gpa") {
    $masv = $_GET['masv'];

    $rs = mysqli_query($conn,"
        SELECT d.diemtong, mh.sotinchi
        FROM diem d
        JOIN lophocphan l ON d.malhp = l.malhp
        JOIN monhoc mh ON l.mamon = mh.mamon
        WHERE d.masv = '$masv'
    ");

    $tong = 0;
    $tinchi = 0;

    while ($r = mysqli_fetch_assoc($rs)) {
        $tong += $r['diemtong'] * $r['sotinchi'];
        $tinchi += $r['sotinchi'];
    }

    $gpa = $tinchi > 0 ? round($tong/$tinchi,2) : 0;

    echo json_encode(["gpa"=>$gpa]);
    exit;
}
// ===== BẢNG ĐIỂM TỔNG HỢP =====
if ($action == "bangdiem") {

    $masv = $_GET['masv'];

    $rs = mysqli_query($conn,"
        SELECT mh.tenmon, mh.sotinchi, d.diemtong, d.xeploai
        FROM diem d
        JOIN lophocphan l ON d.malhp = l.malhp
        JOIN monhoc mh ON l.mamon = mh.mamon
        WHERE d.masv = '$masv'
    ");

    $data = [];
    $tongtinchi = 0;
    $tongdiem = 0;

    while ($r = mysqli_fetch_assoc($rs)) {
        $data[] = $r;

        $tongtinchi += $r['sotinchi'];
        $tongdiem += $r['diemtong'] * $r['sotinchi'];
    }

    $gpa = $tongtinchi > 0 ? round($tongdiem / $tongtinchi, 2) : 0;

    // 👉 XẾP LOẠI HỌC LỰC
    $hocluc = "Yếu";
    if ($gpa >= 8.5) $hocluc = "Xuất sắc";
    else if ($gpa >= 7) $hocluc = "Giỏi";
    else if ($gpa >= 5.5) $hocluc = "Khá";
    else if ($gpa >= 4) $hocluc = "Trung bình";

    echo json_encode([
        "monhoc" => $data,
        "tongtinchi" => $tongtinchi,
        "gpa" => $gpa,
        "hocluc" => $hocluc
    ]);
    exit;
}
?>