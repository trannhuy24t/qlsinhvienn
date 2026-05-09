<?php
mysqli_report(MYSQLI_REPORT_OFF);
error_reporting(E_ALL);
ini_set('display_errors', 0);

ob_start();
session_start();
include "config.php";
/** @var mysqli $conn */

// Lấy userid từ session (ví dụ: '74DCTT21458')
// SỬA DÒNG NÀY: Từ 'userid' thành 'user_id' để khớp với file đăng nhập
$userid = trim($_SESSION['user_id'] ?? ''); 
$role   = trim($_SESSION['role'] ?? '');
$hoten  = $_SESSION['hoten'] ?? ''; // Lấy thêm họ tên để hiển thị

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action != '') {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    if ($action == 'load') {
        $where = "1=1";

        if ($role === "giangvien") {
            $where .= " AND magv = '" . mysqli_real_escape_string($conn, $userid) . "'";
        } 
        elseif ($role === "sinhvien") {
            // Bước 1: Tìm malop từ bảng sinhvien bằng masv
            $sqlSv = "SELECT malop FROM sinhvien WHERE masv = '" . mysqli_real_escape_string($conn, $userid) . "' LIMIT 1";
            $rsSv = mysqli_query($conn, $sqlSv);
            $sv = ($rsSv && mysqli_num_rows($rsSv) > 0) ? mysqli_fetch_assoc($rsSv) : null;

            if ($sv && !empty($sv['malop'])) {
                // Dùng trim() để loại bỏ khoảng trắng thừa từ Database nếu có
                $maLop = trim($sv['malop']); 
                $where .= " AND malop = '" . mysqli_real_escape_string($conn, $maLop) . "'";
            } else {
                // Nếu không tìm thấy sinh viên, trả về lỗi để dễ Debug
                echo json_encode([
                    "status" => "error", 
                    "message" => "Không tìm thấy lớp cho sinh viên: " . $userid
                ]);
                exit;
            }
        }

        $sql = "SELECT * FROM lichhoc WHERE $where ORDER BY ngayhoc ASC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        exit;
    }

    // Các hành động Admin (Giữ nguyên như cũ nhưng thêm check $role)
    if (in_array($action, ['add', 'update', 'delete']) && $role !== 'admin') {
        echo json_encode(["status" => "error", "message" => "Không có quyền"]);
        exit;
    }

    if ($action == "add") {
        $sql = "INSERT INTO lichhoc (malich, mamon, magv, malop, phonghoc, thu, tietbatdau, tietketthuc, ngayhoc, giobatdau, gioketthuc, ghichu)
                VALUES (
                '" . mysqli_real_escape_string($conn, $_POST['malich']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['mamon']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['magv']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['malop']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['phonghoc']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['thu']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['tietbatdau']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['tietketthuc']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['ngayhoc']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['giobatdau']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['gioketthuc']) . "',
                '" . mysqli_real_escape_string($conn, $_POST['ghichu']) . "')";
        $ok = mysqli_query($conn, $sql);
        echo json_encode(["status" => $ok ? "success" : "error", "message" => mysqli_error($conn)]);
        exit;
    }
    // ... (Giữ nguyên Update và Delete) ...
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch học</title>
    <link rel="stylesheet" href="../css/lichHoc.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body data-role="<?= $role ?>">
 <header class="main-header">

    <h1>
        Hệ Thống Quản Lý<br>
        Sinh Viên
    </h1>

    <nav class="navbar">

        <ul>

            <li>
                <a href="../php/home.php">
                    <i class="fas fa-home"></i>
                    Trang chủ
                </a>
            </li>

            <!-- ADMIN -->
            <?php if ($role == "admin") { ?>

                <li>
                    <a href="sinhVien.php">
                        <i class="fas fa-user-graduate"></i>
                        Sinh viên
                    </a>
                </li>

                <li>
                    <a href="lopHoc.php" class="active">
                        <i class="fas fa-school"></i>
                        Lớp học
                    </a>
                </li>

                <li>
                    <a href="monHoc.php">
                        <i class="fas fa-book"></i>
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
                        <i class="fas fa-poll-h"></i>
                        Bảng điểm
                    </a>
                </li>
                <li>
                    <a href="lichhoc.php" class="active">
                        <i class="fas fa-file-alt"></i>
                        Lịch học
                    </a>
                </li>
                <li><a href="api_thongke.php">
                        <i class="fas fa-chart-bar"></i>
                        Thống kê
                    </a>
                </li>
                    

            <?php } ?>

            <!-- GIẢNG VIÊN -->
            <?php if ($role == "giangvien") { ?>

                <li>
                    <a href="lopHoc.php" class="active">
                        <i class="fas fa-school"></i>
                        Lớp học
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
                        <i class="fas fa-poll-h"></i>
                        Bảng điểm
                    </a>
                </li>
                <li>
                    <a href="lichhoc.php" class="active">
                        <i class="fas fa-file-alt"></i>
                        Lịch học
                    </a>
                </li>

            <?php } ?>

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

            <li>
                <a href="../pages/dangnhap.html">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng xuất
                </a>
            </li>

        </ul>

    </nav>

</header>

    <main class="main-content">
        <h2>Quản Lý Lịch Học (Xin chào: <?= $userid ?>)</h2>
        
        <?php if ($role == "admin"): ?>
        <div class="card-form">
            <form id="formLichHoc">
                <input id="malich" name="malich" placeholder="Mã lịch">
                <input id="mamon" name="mamon" placeholder="Mã môn">
                <input id="magv" name="magv" placeholder="Mã GV">
                <input id="malop" name="malop" placeholder="Mã lớp">
                <input id="phonghoc" name="phonghoc" placeholder="Phòng">
                <input id="thu" name="thu" placeholder="Thứ">
                <input id="tietbatdau" name="tietbatdau" placeholder="Tiết BD">
                <input id="tietketthuc" name="tietketthuc" placeholder="Tiết KT">
                <input type="date" id="ngayhoc" name="ngayhoc">
                <input type="time" id="giobatdau" name="giobatdau">
                <input type="time" id="gioketthuc" name="gioketthuc">
                <input id="ghichu" name="ghichu" placeholder="Ghi chú">
                <button type="submit">Thêm lịch</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mã lịch</th><th>Mã môn</th><th>Mã GV</th><th>Mã lớp</th>
                        <th>Phòng</th><th>Thứ</th><th>Tiết BD</th><th>Tiết KT</th>
                        <th>Ngày</th><th>Giờ</th><th>Ghi chú</th>
                        <?php if ($role == "admin") echo "<th>Hành động</th>"; ?>
                    </tr>
                </thead>
                <tbody id="table"></tbody>
            </table>
        </div>
    </main>
    <script>const ROLE = "<?= $role ?>";</script>
    <script src="../js/lichhoc.js"></script>
</body>
</html>