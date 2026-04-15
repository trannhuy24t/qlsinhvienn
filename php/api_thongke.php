<?php
session_start();
include "config.php";
header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Tổng sinh viên
    $total_sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM sinhvien"))['t'] ?? 0;

    // 2. Tổng giảng viên
    $total_gv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM giangvien"))['t'] ?? 0;

    // 3. Tổng môn học (tất cả các môn hiện có)
    $total_mh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM monhoc"))['t'] ?? 0;

    // 4. Tổng Lớp học hành chính (Hiển thị cho Trang chủ)
    // Thay 'lop' bằng 'lophoc' nếu database của bạn dùng tên đó
    $total_l = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM lop"))['t'] ?? 0;

    // 5. Số môn CÓ SINH VIÊN (Dành cho ô Thống kê)
    $sql_mh_sv = "SELECT COUNT(DISTINCT l.mamon) as t FROM diem d JOIN lophocphan l ON d.malhp = l.malhp";
    $total_lhp = mysqli_fetch_assoc(mysqli_query($conn, $sql_mh_sv))['t'] ?? 0;

    // 6. Chi tiết số SV theo môn (Để hiện chữ C++ - 2 SV)
    $sql_class = "SELECT m.tenmon, COUNT(DISTINCT d.masv) as soluong
                  FROM diem d
                  JOIN lophocphan l ON d.malhp = l.malhp
                  JOIN monhoc m ON l.mamon = m.mamon
                  GROUP BY m.mamon";
    $res_class = mysqli_query($conn, $sql_class);
    $class_data = [];
    while ($row = mysqli_fetch_assoc($res_class)) { $class_data[] = $row; }

    // 7. Thống kê học lực
    $grade_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
        SUM(CASE WHEN diemtong >= 8.5 THEN 1 ELSE 0 END) as Gioi,
        SUM(CASE WHEN diemtong >= 7.0 AND diemtong < 8.5 THEN 1 ELSE 0 END) as Kha,
        SUM(CASE WHEN diemtong >= 5.0 AND diemtong < 7.0 THEN 1 ELSE 0 END) as TrungBinh,
        SUM(CASE WHEN diemtong < 5.0 THEN 1 ELSE 0 END) as Yeu
        FROM diem"));

    echo json_encode([
        "total_sv" => (int)$total_sv,
        "total_gv" => (int)$total_gv,
        "total_mh" => (int)$total_mh,
        "total_l" => (int)$total_l,
        "total_lhp" => (int)$total_lhp,
        "classes" => $class_data,
        "grades" => [
            "Gioi" => (int)($grade_data['Gioi'] ?? 0),
            "Kha" => (int)($grade_data['Kha'] ?? 0),
            "TrungBinh" => (int)($grade_data['TrungBinh'] ?? 0),
            "Yeu" => (int)($grade_data['Yeu'] ?? 0)
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>