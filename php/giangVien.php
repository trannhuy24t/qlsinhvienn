<?php
session_start(); // RẤT QUAN TRỌNG: Phải có dòng này ở đầu file để đọc Session
include "config.php";
/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lấy thông tin từ Session
$admin_id = 'admin'; 
// Kiểm tra Admin dựa trên user_id
$isAdmin = (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $admin_id);
// Lấy role để hiển thị sidebar (Nếu không có role, ta dựa vào isAdmin)
$role = $_SESSION['role'] ?? ($isAdmin ? 'admin' : 'sinhvien'); 

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ================= XỬ LÝ LOGIC PHP =================

// 1. LIÊT KÊ LỚP HỌC PHẦN (Công khai)
if ($action == "listLHP") {
    header('Content-Type: application/json; charset=utf-8');
    $sql = "SELECT lhp.malhp, m.tenmon, IFNULL(lh.tenlop, 'Chưa gán lớp') as tenlop 
            FROM lophocphan lhp
            JOIN monhoc m ON lhp.mamon = m.mamon
            LEFT JOIN lop lh ON lhp.malop = lh.malop";
    $rs = mysqli_query($conn, $sql);
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

// 2. DANH SÁCH CHI TIẾT PHÂN CÔNG (Công khai - Để hiện dữ liệu bảng)
if ($action == "listAssign") {
    header('Content-Type: application/json; charset=utf-8');
    $sql = "SELECT p.id, g.hoten, lhp.malhp, m.tenmon, IFNULL(lh.tenlop, '---') as tenlop 
            FROM phancong p
            JOIN giangvien g ON p.magv = g.magv
            JOIN lophocphan lhp ON p.malhp = lhp.malhp
            JOIN monhoc m ON lhp.mamon = m.mamon
            LEFT JOIN lop lh ON lhp.malop = lh.malop";
    $rs = mysqli_query($conn, $sql);
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

// 3. CHẶN QUYỀN ADMIN CHO CÁC THAO TÁC SỬA ĐỔI
if (!$isAdmin && in_array($action, ['add', 'deleteAssign', 'assign_multiple', 'listGV'])) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Bạn không có quyền!"]);
    exit;
}

// Logic Add GV
if ($action == "add") {
    $stmt = $conn->prepare("INSERT INTO giangvien (magv, hoten, email, sodienthoai, chuyennganh) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $_POST['magv'], $_POST['hoten'], $_POST['email'], $_POST['sodienthoai'], $_POST['chuyennganh']);
    echo json_encode(["status" => $stmt->execute() ? "success" : "error"]);
    exit;
}

// Logic List GV
if ($action == "listGV") {
    $rs = mysqli_query($conn, "SELECT * FROM giangvien");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

// Logic Xóa phân công
if ($action == "deleteAssign") {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM phancong WHERE id=?");
    $stmt->bind_param("i", $id);
    echo json_encode(["status" => $stmt->execute() ? "success" : "error"]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý giảng viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/giangVien.css">
    <script>
        const IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    </script>
</head>
<body>

<header class="main-header">
    <h1>Hệ Thống Quản Lý<br>Sinh Viên</h1>
    <nav class="navbar">
        <ul>
            <li><a href="../php/home.php"><i class="fas fa-home"></i> Trang chủ</a></li>

            <?php if ($isAdmin || $role == "admin"): ?>
                <li><a href="sinhVien.php"><i class="fas fa-user-graduate"></i> Sinh viên</a></li>
                <li><a href="lopHoc.php"><i class="fas fa-school"></i> Lớp học</a></li>
                <li><a href="monHoc.php"><i class="fas fa-book"></i> Môn học</a></li>
                <li><a href="giangVien.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</a></li>
                <li><a href="diem.php"><i class="fas fa-poll-h"></i> Bảng điểm</a></li>
                <li><a href="lichhoc.php"><i class="fas fa-file-alt"></i> Lịch học</a></li>
                <li><a href="api_thongke.php"><i class="fas fa-chart-bar"></i> Thống kê</a></li>
            <?php elseif ($role == "giangvien"): ?>
                <li><a href="lopHoc.php"><i class="fas fa-school"></i> Lớp học</a></li>
                <li><a href="giangVien.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</a></li>
                <li><a href="diem.php"><i class="fas fa-poll-h"></i> Bảng điểm</a></li>
                <li><a href="lichhoc.php"><i class="fas fa-file-alt"></i> Lịch học</a></li>
            <?php else: ?>
                <li><a href="lopHoc.php"><i class="fas fa-school"></i> Lớp học</a></li>
                <li><a href="lichhoc.php"><i class="fas fa-calendar-alt"></i> Lịch học</a></li>
                <li><a href="diem.php"><i class="fas fa-poll-h"></i> Bảng điểm</a></li>
            <?php endif; ?>

            <li><a href="../pages/dangnhap.html"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
        </ul>
    </nav>
</header>

<main class="main-content-wrapper">
    <h2 class="page-title">👨‍🏫 Quản lý Giảng viên</h2>

    <?php if ($isAdmin): ?>
    <div class="grid">
        <div class="card">
            <h3><i class="fas fa-plus-circle"></i> Thêm giảng viên</h3>
            <form id="formGV" onsubmit="addGV(); return false;">
                <input id="magv" placeholder="Mã GV" required>
                <input id="hoten" placeholder="Họ tên" required>
                <button type="submit">Thêm</button>
            </form>
        </div>
        <div class="card">
            <h3><i class="fas fa-tasks"></i> Phân công giảng dạy</h3>
            <form id="formAssign" onsubmit="assignMultiple(); return false;">
                <select id="gvSelect" required><option value="">-- Chọn giảng viên --</option></select>
                <div id="lhpList" class="checkbox-container"></div>
                <button type="submit">Xác nhận</button>
            </form>
        </div>
    </div>
    <hr class="section-divider">
    <?php endif; ?>

    <div class="table-section">
        <?php if ($isAdmin): ?>
        <h3><i class="fas fa-list"></i> Danh sách giảng viên</h3>
        <div id="gvTableContainer" class="table-wrapper">
            <table>
                <thead><tr><th>Mã</th><th>Tên</th><th>Hành động</th></tr></thead>
                <tbody id="gvTable"></tbody>
            </table>
        </div>
        <?php endif; ?>

        <h3><i class="fas fa-clipboard-list"></i> Chi tiết phân công giảng dạy</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Giảng viên</th>
                        <th>Mã LHP</th>
                        <th>Môn giảng dạy</th>
                        <th>Lớp (Hành chính)</th>
                        <?php if ($isAdmin): ?><th>Hành động</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="pcTable"></tbody>
            </table>
        </div>
    </div>
</main>

<script src="../js/giangVien.js"></script>
</body>
</html>