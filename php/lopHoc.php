<?php
session_start();

include "config.php";
/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$role = $_SESSION['role'] ?? '';
$malop_sv = $_SESSION['malop'] ?? '';

// FIX: lấy MASV từ user_id
$masv = $_SESSION['user_id'] ?? '';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/*
|--------------------------------------------------------------------------
| LẤY THÔNG TIN LỚP CỦA SINH VIÊN
|--------------------------------------------------------------------------
*/

$tenlop_sv = '';

if ($role == "sinhvien") {

    $stmtClass = $conn->prepare("
        SELECT lop.malop, lop.tenlop
        FROM sinhvien
        INNER JOIN lop
            ON sinhvien.malop = lop.malop
        WHERE sinhvien.masv = ?
    ");

    $stmtClass->bind_param("s", $masv);

    $stmtClass->execute();

    $resultClass = $stmtClass->get_result();

    if ($rowClass = $resultClass->fetch_assoc()) {

        $malop_sv = $rowClass['malop'];
        $tenlop_sv = $rowClass['tenlop'];

    } else {

        $malop_sv = '';
        $tenlop_sv = 'Không tìm thấy lớp';
    }
}

/*
|--------------------------------------------------------------------------
| ADD
|--------------------------------------------------------------------------
*/

if ($action == "add") {

    header('Content-Type: application/json; charset=utf-8');

    if ($role != "admin") {

        echo json_encode([
            "status" => "error",
            "message" => "Bạn không có quyền thêm lớp"
        ]);

        exit;
    }

    $malop = trim($_POST['malop']);
    $tenlop = trim($_POST['tenlop']);
    $khoa = trim($_POST['khoa']);

    $stmt = $conn->prepare("
        INSERT INTO lop (malop, tenlop, khoa)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param(
        "sss",
        $malop,
        $tenlop,
        $khoa
    );

    if ($stmt->execute()) {

        echo json_encode([
            "status" => "success"
        ]);

    } else {

        echo json_encode([
            "status" => "error",
            "message" => $stmt->error
        ]);
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

if ($action == "delete") {

    header('Content-Type: application/json; charset=utf-8');

    if ($role != "admin") {

        echo json_encode([
            "status" => "error",
            "message" => "Bạn không có quyền xóa lớp"
        ]);

        exit;
    }

    $malop = $_POST['malop'];

    $stmt = $conn->prepare("
        DELETE FROM lop
        WHERE malop = ?
    ");

    $stmt->bind_param("s", $malop);

    if ($stmt->execute()) {

        echo json_encode([
            "status" => "success"
        ]);

    } else {

        echo json_encode([
            "status" => "error",
            "message" => $stmt->error
        ]);
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

if ($action == "update") {

    header('Content-Type: application/json; charset=utf-8');

    if ($role != "admin") {

        echo json_encode([
            "status" => "error",
            "message" => "Bạn không có quyền sửa lớp"
        ]);

        exit;
    }

    $malop = trim($_POST['malop']);
    $tenlop = trim($_POST['tenlop']);
    $khoa = trim($_POST['khoa']);

    $stmt = $conn->prepare("
        UPDATE lop
        SET tenlop = ?, khoa = ?
        WHERE malop = ?
    ");

    $stmt->bind_param(
        "sss",
        $tenlop,
        $khoa,
        $malop
    );

    if ($stmt->execute()) {

        echo json_encode([
            "status" => "success"
        ]);

    } else {

        echo json_encode([
            "status" => "error",
            "message" => $stmt->error
        ]);
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| LIST LỚP
|--------------------------------------------------------------------------
*/

if ($action == "list") {

    header('Content-Type: application/json; charset=utf-8');

    // SINH VIÊN KHÔNG XEM DANH SÁCH LỚP
    if ($role == "sinhvien") {

        echo json_encode([]);
        exit;
    }

    $sql = "
        SELECT *
        FROM lop
        ORDER BY malop
    ";

    $result = mysqli_query($conn, $sql);

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $data[] = $row;
    }

    echo json_encode($data);

    exit;
}

/*
|--------------------------------------------------------------------------
| LOAD SINH VIÊN
|--------------------------------------------------------------------------
*/

if ($action == "listSV") {

    header('Content-Type: application/json; charset=utf-8');

    $malop = $_GET['malop'] ?? '';

    // SINH VIÊN CHỈ XEM LỚP CỦA MÌNH
    if ($role == "sinhvien") {

        $malop = $malop_sv;
    }

    $stmt = $conn->prepare("
        SELECT masv, hoten
        FROM sinhvien
        WHERE malop = ?
        ORDER BY hoten
    ");

    $stmt->bind_param("s", $malop);

    $stmt->execute();

    $result = $stmt->get_result();

    $data = [];

    while ($row = $result->fetch_assoc()) {

        $data[] = $row;
    }

    echo json_encode($data);

    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Quản lý lớp học</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet"
          href="../css/lopHoc.css">

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
                    <a href="lichhoc.php">
                        <i class="fas fa-file-alt"></i>
                        Lịch học
                    </a>
                </li>
                <li>
                    <a href="api_thongke.php">
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
                    <a href="lichhoc.php">
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

    <h2 class="page-title">
        🏫 Quản lý lớp học
    </h2>

    <!-- FORM ADMIN -->
    <?php if ($role == "admin") { ?>

    <div class="card-form">

        <form id="formLop"
              onsubmit="addLop(); return false;">

            <div class="input-row">

                <input id="malop"
                       placeholder="Mã lớp"
                       required>

                <input id="tenlop"
                       placeholder="Tên lớp"
                       required>

                <input id="khoa"
                       placeholder="Khóa"
                       required>

                <button type="submit"
                        id="btnSubmit"
                        class="btn-add">

                    Thêm lớp

                </button>

            </div>

        </form>

    </div>

    <?php } ?>

    <!-- ADMIN + GIẢNG VIÊN -->
    <?php if ($role == "admin") { ?>

    <div class="table-wrapper">

        <table>

            <thead>

                <tr>

                    <th>Mã lớp</th>
                    <th>Tên lớp</th>
                    <th>Khóa</th>

                    <?php if ($role == "admin") { ?>
                        <th>Hành động</th>
                    <?php } ?>

                </tr>

            </thead>

            <tbody id="table"></tbody>

        </table>

    </div>

    <?php } ?>

    <!-- GIẢNG VIÊN + SINH VIÊN -->
    <?php if ($role == "giangvien" || $role == "sinhvien") { ?>

    <hr class="section-divider">

    <div class="search-section">

        <h3>
            <i class="fas fa-users"></i>

            <?php if ($role == "sinhvien") { ?>
                Danh sách sinh viên lớp của bạn
            <?php } else { ?>
                Sinh viên trong lớp
            <?php } ?>

        </h3>

        <div class="select-box">

            <!-- GIẢNG VIÊN -->
            <?php if ($role == "giangvien") { ?>

                <label for="chonlop">
                    Chọn lớp:
                </label>

                <select id="chonlop"
                        onchange="loadSV()">
                </select>

            <?php } ?>

            <!-- SINH VIÊN -->
            <?php if ($role == "sinhvien") { ?>

                <label>
                    Lớp của bạn:
                </label>

                <input type="text"
                       value="<?= htmlspecialchars($malop_sv . ' - ' . $tenlop_sv) ?>"
                       readonly
                       class="input-readonly">

            <?php } ?>

        </div>

    </div>

    <div class="table-wrapper">

        <table>

            <thead>

                <tr>
                    <th>Mã SV</th>
                    <th>Họ tên</th>
                </tr>

            </thead>

            <tbody id="svTable"></tbody>

        </table>

    </div>

    <?php } ?>

</main>

<script>
    const USER_ROLE = "<?= $role ?>";
    const USER_MALOP = "<?= $malop_sv ?>";
</script>

<script src="../js/lopHoc.js"></script>

</body>
</html>