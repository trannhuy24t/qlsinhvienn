<?php
session_start();

include "config.php";
/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 0);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* ======================================
   API THỐNG KÊ
====================================== */

if ($action == "load") {

    header('Content-Type: application/json; charset=utf-8');

    try {

        // ===== TỔNG SINH VIÊN =====
        $total_sv = mysqli_fetch_assoc(
            mysqli_query($conn, "
                SELECT COUNT(*) as total
                FROM sinhvien
            ")
        )['total'] ?? 0;

        // ===== TỔNG GIẢNG VIÊN =====
        $total_gv = mysqli_fetch_assoc(
            mysqli_query($conn, "
                SELECT COUNT(*) as total
                FROM giangvien
            ")
        )['total'] ?? 0;

        // ===== TỔNG MÔN HỌC =====
        $total_mh = mysqli_fetch_assoc(
            mysqli_query($conn, "
                SELECT COUNT(*) as total
                FROM monhoc
            ")
        )['total'] ?? 0;

        // ===== TỔNG LỚP =====
        $total_l = mysqli_fetch_assoc(
            mysqli_query($conn, "
                SELECT COUNT(*) as total
                FROM lop
            ")
        )['total'] ?? 0;

        // ===== SỐ MÔN CÓ SINH VIÊN =====
        $total_lhp = mysqli_fetch_assoc(
            mysqli_query($conn, "
                SELECT COUNT(DISTINCT l.mamon) as total
                FROM diem d
                JOIN lophocphan l
                    ON d.malhp = l.malhp
            ")
        )['total'] ?? 0;

        // ===== THỐNG KÊ SINH VIÊN THEO MÔN =====
        $class_query = mysqli_query($conn, "
            SELECT
                m.tenmon,
                COUNT(DISTINCT d.masv) as soluong
            FROM diem d
            JOIN lophocphan l
                ON d.malhp = l.malhp
            JOIN monhoc m
                ON l.mamon = m.mamon
            GROUP BY m.mamon
            ORDER BY soluong DESC
        ");

        $class_data = [];

        while ($row = mysqli_fetch_assoc($class_query)) {
            $class_data[] = $row;
        }

        // ===== THỐNG KÊ HỌC LỰC =====
        $grade_data = mysqli_fetch_assoc(
            mysqli_query($conn, "
                SELECT
                    SUM(CASE WHEN diemtong >= 8.5 THEN 1 ELSE 0 END) as Gioi,
                    SUM(CASE WHEN diemtong >= 7 AND diemtong < 8.5 THEN 1 ELSE 0 END) as Kha,
                    SUM(CASE WHEN diemtong >= 5 AND diemtong < 7 THEN 1 ELSE 0 END) as TrungBinh,
                    SUM(CASE WHEN diemtong < 5 THEN 1 ELSE 0 END) as Yeu
                FROM diem
            ")
        );

        echo json_encode([
            "status" => "success",

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

        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <title>
        Thống kê - Quản lý sinh viên
    </title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet"
          href="../css/thongke.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<header class="main-header">

    <h1>
        Hệ Thống Quản Lý Sinh Viên
    </h1>

    <nav class="navbar">

        <ul>

            <li>
                <a href="../php/home.php">
                    <i class="fas fa-home"></i>
                    Trang chủ
                </a>
            </li>

            <li>
                <a href="sinhVien.php">
                    <i class="fas fa-user-graduate"></i>
                    Sinh viên
                </a>
            </li>

            <li>
                <a href="lopHoc.php">
                    <i class="fas fa-school"></i>
                    Lớp học
                </a>
            </li>

            <li>
                <a href="monHoc.php">
                    <i class="fas fa-book-open"></i>
                    Môn học
                </a>
            </li>

            <li>
                <a href="giangVien.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Giảng viên
                </a>
            </li>

            <li>
                <a href="diem.php">
                    <i class="fas fa-marker"></i>
                    Bảng điểm
                </a>
            </li>

            <li>
                <a href="lichhoc.php">
                    <i class="fas fa-file-alt"></i>
                    Lịch học
                </a>
            </li>

            <li>
                <a href="thongKe.php" class="active">
                    <i class="fas fa-chart-bar"></i>
                    Thống kê
                </a>
            </li>

            <li>
                <a href="../php/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng xuất
                </a>
            </li>

        </ul>

    </nav>

</header>

<main class="container">

    <h1 class="page-title">
        📊 Thống kê hệ thống
    </h1>

    <!-- QUICK STATS -->

    <div class="quick-stats">

        <div class="stat-box">

            <i class="fas fa-users"></i>

            <div class="stat-info">

                <p>Tổng sinh viên</p>

                <span id="total-sv">0</span>

            </div>

        </div>

        <div class="stat-box">

            <i class="fas fa-chalkboard-teacher"></i>

            <div class="stat-info">

                <p>Tổng giảng viên</p>

                <span id="total-gv">0</span>

            </div>

        </div>

        <div class="stat-box">

            <i class="fas fa-book"></i>

            <div class="stat-info">

                <p>Môn học</p>

                <span id="total-mh">0</span>

            </div>

        </div>

        <div class="stat-box">

            <i class="fas fa-graduation-cap"></i>

            <div class="stat-info">

                <p>Số môn có SV</p>

                <span id="total-lh">0</span>

            </div>

        </div>

    </div>

    <!-- GRID -->

    <div class="stats-grid">

        <!-- DANH SÁCH -->

        <div class="stats-card">

            <h3>
                <i class="fas fa-list-ol"></i>
                Số SV theo môn
            </h3>

            <div id="class-stats-list"
                 class="scrollable-list">

                <p style="text-align:center;color:#999;">
                    Đang tải dữ liệu...
                </p>

            </div>

        </div>

        <!-- CHART -->

        <div class="stats-card">

            <h3>
                <i class="fas fa-chart-pie"></i>
                Tỷ lệ học lực
            </h3>

            <div class="chart-wrapper">

                <canvas id="gradeChart"></canvas>

            </div>

        </div>

    </div>

</main>

<script src="../js/thongke.js"></script>

</body>
</html>