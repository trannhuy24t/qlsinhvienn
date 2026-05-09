```php
<?php
session_start();

if (!isset($_SESSION['hoten'])) {
    header("Location: ../pages/dangnhap.html");
    exit();
}

include "../php/config.php";
/** @var mysqli $conn */

$ten_nguoi_dung = htmlspecialchars($_SESSION['hoten'], ENT_QUOTES, 'UTF-8');
$role = $_SESSION['role'] ?? 'sinhvien';

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

                <!-- Trang chủ -->
                <li>
                    <a href="../php/home.php">
                        <i class="fas fa-home"></i> Trang chủ
                    </a>
                </li>

                <!-- ADMIN -->
                <?php if($role == 'admin'): ?>

                    <li>
                        <a href="../php/sinhVien.php">
                            <i class="fas fa-user-graduate"></i> Sinh viên
                        </a>
                    </li>

                    <li>
                        <a href="../php/lopHoc.php">
                            <i class="fas fa-school"></i> Lớp học
                        </a>
                    </li>

                    <li>
                        <a href="../php/monHoc.php">
                            <i class="fas fa-book"></i> Môn học
                        </a>
                    </li>

                    <li>
                        <a href="../php/giangVien.php">
                            <i class="fas fa-chalkboard-teacher"></i> Giảng viên
                        </a>
                    </li>

                    <li>
                        <a href="../php/diem.php">
                            <i class="fas fa-poll-h"></i> Bảng điểm
                        </a>
                    </li>

                    <li>
                        <a href="../php/lichhoc.php">
                            <i class="fas fa-file-alt"></i> Lịch học
                        </a>
                    </li>

                    <li>
                        <a href="../php/api_thongke.php">
                            <i class="fas fa-chart-line"></i> Thống kê
                        </a>
                    </li>

                <?php endif; ?>


                <!-- GIẢNG VIÊN -->
                <?php if($role == 'giangvien'): ?>

                    <li>
                        <a href="../php/lopHoc.php">
                            <i class="fas fa-school"></i> Lớp học
                        </a>
                    </li>

                   <li>
                        <a href="../php/giangVien.php">
                            <i class="fas fa-chalkboard-teacher"></i> Giảng viên
                        </a>
                    </li>

                    <li>
                        <a href="../php/diem.php">
                            <i class="fas fa-poll-h"></i> Bảng điểm
                        </a>
                    </li>
                      <li>
                        <a href="../php/lichhoc.php">
                            <i class="fas fa-file-alt"></i> Lịch học
                        </a>
                    </li>

                <?php endif; ?>


                <!-- SINH VIÊN -->
                <?php if($role == 'sinhvien'): ?>

                     <li>
                        <a href="../php/lopHoc.php">
                            <i class="fas fa-school"></i> Lớp học
                        </a>
                    </li>
                    <li>
                        <a href="../php/lichhoc.php">
                            <i class="fas fa-calendar-alt"></i> Lịch học
                        </a>
                    </li>

                    <li>
                        <a href="../php/diem.php">
                            <i class="fas fa-poll-h"></i> Bảng điểm
                        </a>
                    </li>

                <?php endif;  ?>


                <!-- USER -->
                <li class="user-dropdown">

                    <a href="#" class="user-toggle">
                        <i class="fas fa-user-circle"></i>

                        <span>
                            <?php echo $ten_nguoi_dung; ?>
                        </span>

                        <i class="fas fa-caret-down"></i>
                    </a>

                    <ul class="dropdown-menu">

                        <li>
                            <a href="profile-giao-dien.php">
                                <i class="fas fa-id-card"></i> Hồ sơ
                            </a>
                        </li>

                        <li>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        </li>

                    </ul>

                </li>

            </ul>

        </nav>

    </div>
</header>


<main>

    <div class="welcome-section">
        <i class="fas fa-user-circle"></i>

        <h2>
            Xin chào,
            <span id="username">
                <?php echo $ten_nguoi_dung; ?>
            </span>
        </h2>
    </div>

    <div class="card-container">

        <?php if($role == 'admin'): ?>

            <div class="card">
                <h3>Sinh viên</h3>
                <div class="count">
                    <?php echo $count_sv; ?>
                </div>
            </div>

            <div class="card">
                <h3>Lớp học</h3>
                <div class="count">
                    <?php echo $count_lh; ?>
                </div>
            </div>

            <div class="card">
                <h3>Giảng viên</h3>
                <div class="count">
                    <?php echo $count_gv; ?>
                </div>
            </div>

            <div class="card">
                <h3>Môn học</h3>
                <div class="count">
                    <?php echo $count_mh; ?>
                </div>
            </div>

        <?php endif; ?>


        <?php if($role == 'giangvien'): ?>

            <div class="card">
                <h3>Lớp học</h3>
                <div class="count">
                    <?php echo $count_lh; ?>
                </div>
            </div>

            <div class="card">
                <h3>Môn học</h3>
                <div class="count">
                    <?php echo $count_mh; ?>
                </div>
            </div>

        <?php endif; ?>


        <?php if($role == 'sinhvien'): ?>

            <div class="card">
                <h3>Môn học</h3>
                <div class="count">
                    <?php echo $count_mh; ?>
                </div>
            </div>

        <?php endif; ?>

    </div>

</main>

<script src="../js/home.js"></script>

</body>
</html>
