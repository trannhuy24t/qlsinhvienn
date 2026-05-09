<?php
session_start();
include "../php/config.php"; 

/** @var mysqli $conn */

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/dangnhap.html");
    exit();
}

$user_id = $_SESSION['user_id']; // Ví dụ: '74DCTT21457'
$role = $_SESSION['role'];     // 'sinhvien' hoặc 'giangvien'

// 2. Lấy dữ liệu theo đúng cấu trúc Database
// 2. Lấy dữ liệu theo đúng cấu trúc Database
if ($role == "sinhvien") {
    // Kiểm tra lại bảng 'sinhvien' xem có cột 'ngaysinh' chưa, nếu chưa có cũng phải xóa đi
    $sql = "SELECT hoten, ngaysinh, malop, masv FROM sinhvien WHERE masv = '$user_id'";
} else {
    // BỎ 'ngaysinh' vì database hiện tại của bạn không có cột này
    $sql = "SELECT hoten, chuyennganh, magv FROM giangvien WHERE magv = '$user_id'";
}

$result = mysqli_query($conn, $sql);

// Kiểm tra nếu có dữ liệu
if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
} else {
    die("Không tìm thấy thông tin hồ sơ cho mã: " . htmlspecialchars($user_id));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân | Hệ thống Quản lý</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>

<div class="profile-card">
    <h2>Hồ sơ của bạn</h2>

    <div class="avatar-container">
        <?php if(!empty($avatar)): ?>
            <img src="<?php echo $avatar; ?>" alt="Avatar">
        <?php else: ?>
            <div class="default-avatar">
                <i class="fas fa-user"></i>
            </div>
        <?php endif; ?>
    </div>

    <div class="info-list">
        <div class="info-item">
            <i class="fas fa-id-card"></i>
            <span class="info-label">Họ tên</span>
            <span class="info-value"><?php echo htmlspecialchars($data['hoten']); ?></span>
        </div>

      <div class="info-item">
    <i class="fas fa-calendar-day"></i>
    <span class="info-label">Ngày sinh</span>
    <span class="info-value">
        <?php echo isset($data['ngaysinh']) ? date("d/m/Y", strtotime($data['ngaysinh'])) : "Chưa cập nhật"; ?>
    </span>
</div>

<div class="info-item">
    <i class="fas fa-birthday-cake"></i>
    <span class="info-label">Tuổi</span>
    <span class="info-value">
        <?php 
        if (isset($data['ngaysinh'])) {
            echo date("Y") - date("Y", strtotime($data['ngaysinh'])) . " tuổi";
        } else {
            echo "N/A";
        }
        ?>
    </span>
</div>

        <?php if($role == "sinhvien"): ?>
            <div class="info-item">
                <i class="fas fa-fingerprint"></i>
                <span class="info-label">Mã SV</span>
                <span class="info-value"><?php echo htmlspecialchars($data['masv']); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-graduation-cap"></i>
                <span class="info-label">Lớp</span>
                <span class="info-value"><?php echo htmlspecialchars($data['malop']); ?></span>
            </div>
        <?php else: ?>
            <div class="info-item">
                <i class="fas fa-user-tie"></i>
                <span class="info-label">Mã GV</span>
                <span class="info-value"><?php echo htmlspecialchars($data['magv']); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-book"></i>
                <span class="info-label">Chuyên ngành</span>
                <span class="info-value"><?php echo htmlspecialchars($data['chuyennganh']); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <a href="../php/home.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại trang chủ
    </a>
</div>

</body>
</html>