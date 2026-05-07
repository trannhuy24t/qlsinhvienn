<?php
session_start();

if (!isset($_SESSION['hoten'])) {
    header("Location: ../pages/dangnhap.html");
    exit();
}

include "../php/config.php";
/** @var mysqli $conn */

$ten_nguoi_dung = htmlspecialchars($_SESSION['hoten'], ENT_QUOTES, 'UTF-8');

function queryCount($conn, $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM `$table`");
    return $result ? (int)mysqli_fetch_assoc($result)['total'] : 0;
}

$count_sv = queryCount($conn, 'sinhvien');
$count_lh = queryCount($conn, 'lop');
$count_gv = queryCount($conn, 'giangvien');
$count_mh = queryCount($conn, 'monhoc');
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
                 <li><a href="../php/home.php"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li><a href="../pages/sinhVien.html" class="active"><i class="fas fa-user-graduate"></i> Sinh viên</a></li>
            <li><a href="../pages/lopHoc.html"><i class="fas fa-school"></i> Lớp học</a></li>
            <li><a href="../pages/monHoc.html"><i class="fas fa-book"></i> Môn học</a></li>
            <li><a href="../pages/giangVien.html"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</a></li>
            <li><a href="../pages/diem.html"><i class="fas fa-poll-h"></i> Bảng điểm</a></li>
            <li><a href="../pages/baoCao.html"><i class="fas fa-file-alt"></i> Báo cáo</a></li>
            <li><a href="../pages/thongKe.html"><i class="fas fa-chart-line"></i> Thống kê</a></li>
            
                
              <?php if(isset($_SESSION['hoten'])): ?>
    <li class="user-dropdown">
        <a href="#" class="user-toggle">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['hoten'], ENT_QUOTES, 'UTF-8'); ?></span>
            <i class="fas fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu">
            <li><a href="profile-giao-dien.php"><i class="fas fa-id-card"></i> Hồ sơ</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
        </ul>
    </li>
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