<?php
session_start(); 
include "config.php";
/** @var mysqli $conn */

// Bật báo lỗi để dễ debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ================= 1. KIỂM TRA QUYỀN TRUY CẬP =================

$user_id = $_SESSION['user_id'] ?? '';
$role = strtolower($_SESSION['role'] ?? ''); // Chuyển về chữ thường để dễ so sánh

/**
 * FIX QUYỀN: 
 * Một người là Admin nếu role là 'admin' HOẶC user_id là 'admin'
 */
$isAdmin = ($role === 'admin' || $user_id === 'admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ================= 2. XỬ LÝ LOGIC API (JSON) =================

// Các hành động yêu cầu quyền Admin
$adminActions = ['add', 'deleteAssign', 'assign_multiple', 'listGV', 'deleteGV'];

if (in_array($action, $adminActions)) {
    if (!$isAdmin) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "status" => "error", 
            "message" => "Bạn không có quyền thực hiện thao tác này!"
        ]);
        exit;
    }
}

// 1. LIÊT KÊ LỚP HỌC PHẦN
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

// 2. DANH SÁCH CHI TIẾT PHÂN CÔNG
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

// 3. THÊM GIẢNG VIÊN (Chỉ Admin)
if ($action == "add") {
    // Xóa sạch bộ đệm để tránh các ký tự lạ làm hỏng JSON
    ob_clean(); 
    header('Content-Type: application/json; charset=utf-8');

    // Lấy dữ liệu từ POST
    $magv = $_POST['magv'] ?? '';
    $hoten = $_POST['hoten'] ?? '';

    if (empty($magv) || empty($hoten)) {
        echo json_encode(["status" => "error", "message" => "Thiếu dữ liệu đầu vào"]);
        exit;
    }

    // Câu lệnh SQL "2 trong 1": Thêm nếu mới, Cập nhật nếu đã tồn tại mã GV
    $sql = "INSERT INTO giangvien (magv, hoten) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE hoten = VALUES(hoten)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $magv, $hoten);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
    exit;
}
// 4. LẤY DANH SÁCH GV (Dùng cho Select Box và Bảng)
if ($action == "listGV") {
    header('Content-Type: application/json; charset=utf-8');
    $rs = mysqli_query($conn, "SELECT * FROM giangvien");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

// 5. PHÂN CÔNG HÀNG LOẠT (Chỉ Admin)
if ($action == "assign_multiple") {
    header('Content-Type: application/json; charset=utf-8');
    $magv = $_POST['magv'];
    $lhps = $_POST['lhps'] ?? []; // Mảng các mã LHP
    $success = true;
    foreach($lhps as $malhp) {
        $stmt = $conn->prepare("INSERT INTO phancong (magv, malhp) VALUES (?, ?)");
        $stmt->bind_param("ss", $magv, $malhp);
        if(!$stmt->execute()) $success = false;
    }
    echo json_encode(["status" => $success ? "success" : "error"]);
    exit;
}

// 6. XÓA PHÂN CÔNG (Chỉ Admin)
if ($action == "deleteAssign") {
    header('Content-Type: application/json; charset=utf-8');
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
    <title>Quản lý Giảng viên | Hệ thống</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/giangVien.css">
    <script>
        // Truyền trạng thái admin sang file JS
        const IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    </script>
</head>
<body>

<header class="main-header">
    <h1>Hệ Thống Quản Lý<br>Sinh Viên</h1>
    <nav class="navbar">
        <ul>
            <li><a href="../php/home.php"><i class="fas fa-home"></i> Trang chủ</a></li>

            <?php if ($isAdmin): ?>
                <!-- Menu đầy đủ cho ADMIN -->
                <li><a href="sinhVien.php"><i class="fas fa-user-graduate"></i> Sinh viên</a></li>
                <li><a href="lopHoc.php"><i class="fas fa-school"></i> Lớp học</a></li>
                <li><a href="monHoc.php"><i class="fas fa-book"></i> Môn học</a></li>
                <li><a href="giangVien.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</a></li>
                <li><a href="diem.php"><i class="fas fa-poll-h"></i> Bảng điểm</a></li>
                <li><a href="lichhoc.php"><i class="fas fa-file-alt"></i> Lịch học</a></li>
                <li><a href="api_thongke.php"><i class="fas fa-chart-bar"></i> Thống kê</a></li>
            <?php elseif ($role == "giangvien"): ?>
                <!-- Menu cho GIẢNG VIÊN -->
                <li><a href="lopHoc.php"><i class="fas fa-school"></i> Lớp học</a></li>
                <li><a href="giangVien.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</a></li>
                <li><a href="diem.php"><i class="fas fa-poll-h"></i> Bảng điểm</a></li>
                <li><a href="lichhoc.php"><i class="fas fa-file-alt"></i> Lịch học</a></li>
            <?php else: ?>
                <!-- Menu cho SINH VIÊN -->
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
        <!-- FORM THÊM GV (CHỈ HIỆN VỚI ADMIN) -->
        <div class="card">
            <h3><i class="fas fa-plus-circle"></i> Thêm giảng viên</h3>
            <form id="formGV" onsubmit="addGV(); return false;">
                <input id="magv" placeholder="Mã GV (VD: GV001)" required>
                <input id="hoten" placeholder="Họ tên giảng viên" required>
                <button type="submit">Thêm mới</button>
            </form>
        </div>

        <!-- FORM PHÂN CÔNG (CHỈ HIỆN VỚI ADMIN) -->
        <div class="card">
            <h3><i class="fas fa-tasks"></i> Phân công giảng dạy</h3>
            <form id="formAssign" onsubmit="assignMultiple(); return false;">
                <select id="gvSelect" required>
                    <option value="">-- Chọn giảng viên --</option>
                </select>
                <div id="lhpList" class="checkbox-container">
                    <!-- Danh sách LHP sẽ load bằng JS -->
                    <p style="font-size: 0.8em; color: #666;">Đang tải danh sách lớp học phần...</p>
                </div>
                <button type="submit">Xác nhận phân công</button>
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
                <thead>
                    <tr>
                        <th>Mã GV</th>
                        <th>Họ tên</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
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
                        <?php if ($isAdmin): ?>
                            <th>Hành động</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="pcTable">
                    <!-- Dữ liệu đổ từ JS -->
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="../js/giangVien.js"></script>
</body>
</html>