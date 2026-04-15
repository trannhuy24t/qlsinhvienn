<?php
session_start();
include "../php/config.php"; // Đảm bảo đường dẫn tới file config đúng

// 1. Kiểm tra đăng nhập và lấy tên người dùng
$ten_nguoi_dung = isset($_SESSION['hoten']) ? $_SESSION['hoten'] : 'Khách';

// 2. Truy vấn số lượng trực tiếp từ Database để hiển thị ngay khi load trang
$count_sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sinhvien"))['total'] ?? 0;
$count_lh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lop"))['total'] ?? 0;
$count_gv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM giangvien"))['total'] ?? 0;
$count_mh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM monhoc"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - QLSinhVien</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css">
    <style>
        /* CSS bổ sung để căn chỉnh icon và chữ Xin chào */
        .welcome-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 30px 0;
        }
        .welcome-section i {
            font-size: 35px;
            color: #007bff;
        }
        .welcome-section h2 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }
    </style>
</head>
<body>
<header class="main-header">
    <div class="container">
        <h1>Hệ Thống Quản Lý Sinh Viên</h1>
        <nav class="navbar">
            <ul>
                <li><a href="home.php" class="active">Trang chủ</a></li>
                <li><a href="../pages/sinhVien.html">Sinh viên</a></li>
                <li><a href="../pages/lopHoc.html">Lớp học</a></li>
                <li><a href="../pages/monHoc.html">Môn học</a></li>
                <li><a href="../pages/giangVien.html">Giảng viên</a></li>
                <li><a href="../pages/thongKe.html">Thống kê</a></li>
                <li><a href="../pages/diem.html">Bảng điểm</a></li>
                
                <?php if(isset($_SESSION['hoten'])): ?>
                    <li class="user-item">
                        <a href="#" title="Tài khoản: <?php echo $_SESSION['hoten']; ?>">
                            <i class="fas fa-user"></i>
                        </a>
                    </li>
                    <li><a href="logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="../pages/dangnhap.html">Đăng nhập</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="welcome-section">
        <i class="fas fa-user-circle"></i>
        <h2>Xin chào, <span id="username"><?php echo $ten_nguoi_dung; ?></span></h2>
    </div>

    <div class="card-container">
        <div class="card">
            <h3>Sinh viên</h3>
            <div class="count" id="count-sv"><?php echo $count_sv; ?></div>
        </div>
        <div class="card">
            <h3>Lớp học</h3>
            <div class="count" id="count-lh"><?php echo $count_lh; ?></div>
        </div>
        <div class="card">
            <h3>Giảng viên</h3>
            <div class="count" id="count-gv"><?php echo $count_gv; ?></div>
        </div>
        <div class="card">
            <h3>Môn học</h3>
            <div class="count" id="count-mh"><?php echo $count_mh; ?></div>
        </div>
    </div>
</main>

<script src="../js/home.js"></script>
</body>
</html>