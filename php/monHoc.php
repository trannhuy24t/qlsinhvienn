<?php
session_start();
include "config.php";
/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 0);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* =========================
   MON HOC
========================= */

// ADD MON
if ($action == "addMon") {

    header('Content-Type: application/json; charset=utf-8');

    $stmt = $conn->prepare("
        INSERT INTO monhoc
        (mamon, tenmon, sotinchi, mota)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssis",
        $_POST['mamon'],
        $_POST['tenmon'],
        $_POST['sotinchi'],
        $_POST['mota']
    );

    echo json_encode([
        "status" => $stmt->execute() ? "success" : "error",
        "message" => $stmt->error
    ]);

    exit;
}

// UPDATE MON
if ($action == "updateMon") {

    header('Content-Type: application/json; charset=utf-8');

    $stmt = $conn->prepare("
        UPDATE monhoc
        SET tenmon=?, sotinchi=?, mota=?
        WHERE mamon=?
    ");

    $stmt->bind_param(
        "siss",
        $_POST['tenmon'],
        $_POST['sotinchi'],
        $_POST['mota'],
        $_POST['mamon']
    );

    echo json_encode([
        "status" => $stmt->execute() ? "success" : "error",
        "message" => $stmt->error
    ]);

    exit;
}

// DELETE MON
if ($action == "deleteMon") {

    header('Content-Type: application/json; charset=utf-8');

    $stmt = $conn->prepare("
        DELETE FROM monhoc
        WHERE mamon=?
    ");

    $stmt->bind_param("s", $_POST['mamon']);

    echo json_encode([
        "status" => $stmt->execute() ? "success" : "error",
        "message" => $stmt->error
    ]);

    exit;
}

/* =========================
   HOC KY
========================= */

// ADD HK
if ($action == "addHK") {

    header('Content-Type: application/json; charset=utf-8');

    $check = $conn->prepare("
        SELECT mahocky
        FROM hocky
        WHERE mahocky=?
    ");

    $check->bind_param("s", $_POST['mahocky']);
    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows > 0) {

        echo json_encode([
            "status" => "error",
            "message" => "Mã học kỳ đã tồn tại!"
        ]);

        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO hocky
        (mahocky, tenhocky, namhoc)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param(
        "sss",
        $_POST['mahocky'],
        $_POST['tenhocky'],
        $_POST['namhoc']
    );

    echo json_encode([
        "status" => $stmt->execute() ? "success" : "error",
        "message" => $stmt->error
    ]);

    exit;
}

// UPDATE HK
if ($action == "updateHK") {

    header('Content-Type: application/json; charset=utf-8');

    $stmt = $conn->prepare("
        UPDATE hocky
        SET tenhocky=?, namhoc=?
        WHERE mahocky=?
    ");

    $stmt->bind_param(
        "sss",
        $_POST['tenhocky'],
        $_POST['namhoc'],
        $_POST['mahocky']
    );

    echo json_encode([
        "status" => $stmt->execute() ? "success" : "error",
        "message" => $stmt->error
    ]);

    exit;
}

// DELETE HK
if ($action == "deleteHK") {

    header('Content-Type: application/json; charset=utf-8');

    $stmt = $conn->prepare("
        DELETE FROM hocky
        WHERE mahocky=?
    ");

    $stmt->bind_param("s", $_POST['mahocky']);

    echo json_encode([
        "status" => $stmt->execute() ? "success" : "error",
        "message" => $stmt->error
    ]);

    exit;
}

/* =========================
   LOP HOC PHAN
========================= */
if ($action == "listLop") {
    header('Content-Type: application/json; charset=utf-8');
    $rs = mysqli_query($conn, "SELECT malop, tenlop FROM lop ORDER BY tenlop");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}
// ADD LHP
if ($action == "addLHP") {
    header('Content-Type: application/json; charset=utf-8');
    $stmt = $conn->prepare("
        INSERT INTO lophocphan
        (malhp, mamon, magv, mahocky, malop) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssss", // Thêm một chữ 's'
        $_POST['malhp'],
        $_POST['mamon'],
        $_POST['magv'],
        $_POST['mahocky'],
        $_POST['malop'] // Nhận thêm malop
    );
    echo json_encode(["status" => $stmt->execute() ? "success" : "error", "message" => $stmt->error]);
    exit;
}

// UPDATE LHP
if ($action == "updateLHP") {
    header('Content-Type: application/json; charset=utf-8');
    $stmt = $conn->prepare("
        UPDATE lophocphan
        SET mamon=?, magv=?, mahocky=?, malop=?
        WHERE malhp=?
    ");
    $stmt->bind_param(
        "sssss",
        $_POST['mamon'],
        $_POST['magv'],
        $_POST['mahocky'],
        $_POST['malop'],
        $_POST['malhp']
    );
    echo json_encode(["status" => $stmt->execute() ? "success" : "error", "message" => $stmt->error]);
    exit;
}

// DELETE LHP
if ($action == "deleteLHP") {

    header('Content-Type: application/json; charset=utf-8');

    $stmt = $conn->prepare("
        DELETE FROM lophocphan
        WHERE malhp=?
    ");

    $stmt->bind_param("s", $_POST['malhp']);

    echo json_encode([
        "status" => $stmt->execute() ? "success" : "error",
        "message" => $stmt->error
    ]);

    exit;
}

/* =========================
   LIST
========================= */

// LIST MON
if ($action == "listMon") {

    header('Content-Type: application/json; charset=utf-8');

    $rs = mysqli_query($conn,"
        SELECT *
        FROM monhoc
        ORDER BY mamon
    ");

    echo json_encode(
        mysqli_fetch_all($rs, MYSQLI_ASSOC)
    );

    exit;
}

// LIST HK
if ($action == "listHK") {

    header('Content-Type: application/json; charset=utf-8');

    $rs = mysqli_query($conn,"
        SELECT DISTINCT
            mahocky,
            tenhocky,
            namhoc
        FROM hocky
        ORDER BY mahocky
    ");

    echo json_encode(
        mysqli_fetch_all($rs, MYSQLI_ASSOC)
    );

    exit;
}

// LIST LHP
if ($action == "listLHP") {
    header('Content-Type: application/json; charset=utf-8');
    $rs = mysqli_query($conn,"
        SELECT
            l.*,
            m.tenmon,
            h.tenhocky,
            h.namhoc,
            IFNULL(lh.tenlop, 'Chưa gán') as tenlop
        FROM lophocphan l
        JOIN monhoc m ON l.mamon = m.mamon
        JOIN hocky h ON l.mahocky = h.mahocky
        LEFT JOIN lop lh ON l.malop = lh.malop
        ORDER BY l.malhp
    ");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC) ?: []);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <title>Quản lý môn học</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet"
          href="../css/monHoc.css">

</head>

<body>

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
                <a href="monHoc.php" class="active">
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
                <a href="api_thongKe.php">
                    <i class="fas fa-chart-line"></i>
                    Thống kê
                </a>
            </li>

            <li>
                <a href="../pages/dangnhap.html">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng xuất
                </a>
            </li>

        </ul>

    </nav>

</header>

<main class="main-content-wrapper">

    <h2 class="page-title">
        📚 Quản lý Môn học & Học kỳ
    </h2>

    <div class="grid-container">

        <!-- MON HOC -->
        <div class="card">

            <h3>
                <i class="fas fa-plus-circle"></i>
                Thêm/Sửa môn
            </h3>

            <form id="formMon" onsubmit="addMon(); return false;">

                <input id="mamon"
                       placeholder="Mã môn"
                       required>

                <input id="tenmon"
                       placeholder="Tên môn"
                       required>

                <input id="sotinchi"
                       type="number"
                       placeholder="Số tín chỉ"
                       required>

                <input id="mota"
                       placeholder="Mô tả">

                <button type="submit"
                        id="btnSubmitMon"
                        class="btn-add">

                    Thêm môn

                </button>

            </form>

        </div>

        <!-- HOC KY -->
        <div class="card">

            <h3>
                <i class="fas fa-calendar-plus"></i>
                Thêm/Sửa học kỳ
            </h3>

            <form id="formHK" onsubmit="addHK(); return false;">

                <input id="mahocky"
                       placeholder="Mã HK"
                       required>

                <input id="tenhocky"
                       placeholder="Tên học kỳ"
                       required>

                <input id="namhoc"
                       placeholder="Năm học"
                       required>

                <button type="submit"
                        id="btnSubmitHK"
                        class="btn-add">

                    Thêm học kỳ

                </button>

            </form>

        </div>

        <!-- LOP HOC PHAN -->
       <!-- LOP HOC PHAN -->
<div class="card">
    <h3><i class="fas fa-door-open"></i> Mở lớp học phần</h3>
    <form id="formLHP" onsubmit="addLHP(); return false;">
        <input id="malhp" placeholder="Mã lớp học phần" required>
        <input id="magv" placeholder="Mã giảng viên" required>

        <label>Môn học:</label>
        <select id="mamonSelect" required></select>

        <label>Học kỳ:</label>
        <select id="hkSelect" required></select>

        <!-- THÊM PHẦN NÀY -->
        <label>Dành cho lớp (Hành chính):</label>
        <select id="lopSelect" required>
            <option value="">-- Chọn lớp --</option>
        </select>

        <button type="submit" id="btnSubmitLHP" class="btn-add">Mở lớp</button>
    </form>
</div>

    </div>

    <hr class="section-divider">

    <!-- TABLE MON -->
    <div class="table-section">

        <h3>
            <i class="fas fa-list"></i>
            Danh sách môn học
        </h3>

        <div class="table-wrapper">

            <table>

                <thead>

                <tr>
                    <th>Mã</th>
                    <th>Tên</th>
                    <th>TC</th>
                    <th>Mô tả</th>
                    <th>Hành động</th>
                </tr>

                </thead>

                <tbody id="monTable"></tbody>

            </table>

        </div>

        <!-- TABLE HK -->

        <h3>
            <i class="fas fa-list-alt"></i>
            Danh sách học kỳ
        </h3>

        <div class="table-wrapper">

            <table>

                <thead>

                <tr>
                    <th>Mã HK</th>
                    <th>Tên HK</th>
                    <th>Năm học</th>
                    <th>Hành động</th>
                </tr>

                </thead>

                <tbody id="hkTable"></tbody>

            </table>

        </div>

        <!-- TABLE LHP -->

        <h3>
            <i class="fas fa-university"></i>
            Danh sách lớp học phần
        </h3>

        <div class="table-wrapper">

            <table>

                <thead>

                <tr>
                    <th>Mã LHP</th>
                    <th>Tên môn</th>
                    <th>Mã GV</th>
                    <th>Học kỳ</th>
                    <th>Năm học</th>
                    <th>Lớp</th>
                    <th>Hành động</th>
                </tr>

                </thead>

                <tbody id="lhpTable"></tbody>

            </table>

        </div>

    </div>

</main>

<script src="../js/monHoc.js"></script>

</body>
</html>