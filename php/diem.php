<?php
session_start();

include "config.php";
/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$role = $_SESSION['role'] ?? '';
$masv_login = $_SESSION['masv'] ?? '';
$hoten_login = $_SESSION['hoten'] ?? '';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/*
|------------------------------------------------------------------
| LẤY DANH SÁCH SINH VIÊN & LỚP HỌC PHẦN (Dùng cho Admin/GV)
|------------------------------------------------------------------
*/
if ($action == "get_students") {
    header('Content-Type: application/json');
    $rs = mysqli_query($conn, "SELECT masv, hoten FROM sinhvien");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

if ($action == "get_classes") {
    header('Content-Type: application/json');
    $rs = mysqli_query($conn, "SELECT l.malhp, mh.tenmon FROM lophocphan l JOIN monhoc mh ON l.mamon = mh.mamon");
    echo json_encode(mysqli_fetch_all($rs, MYSQLI_ASSOC));
    exit;
}

/*
|------------------------------------------------------------------
| TÍNH XẾP LOẠI
|------------------------------------------------------------------
*/
function xepLoai($diem){

    if ($diem >= 8.5) return "A";
    if ($diem >= 7) return "B";
    if ($diem >= 5.5) return "C";
    if ($diem >= 4) return "D";

    return "F";
}

/*
|------------------------------------------------------------------
| SAVE ĐIỂM
|------------------------------------------------------------------
*/
if ($action == "save") {

    header('Content-Type: application/json');

    // CHỈ ADMIN + GIẢNG VIÊN
    if ($role != "admin" && $role != "giangvien") {

        echo json_encode([
            "status" => "error",
            "message" => "Bạn không có quyền nhập điểm"
        ]);

        exit;
    }

    $masv = $_POST['masv'];
    $malhp = $_POST['malhp'];

    $cc = $_POST['diemchuyencan'];
    $gk = $_POST['diemgiuaky'];
    $ck = $_POST['diemcuoiky'];

    // VALIDATE
    if (
        $cc < 0 || $cc > 10 ||
        $gk < 0 || $gk > 10 ||
        $ck < 0 || $ck > 10
    ) {

        echo json_encode([
            "status" => "error",
            "message" => "Điểm phải từ 0 đến 10"
        ]);

        exit;
    }

    // TÍNH ĐIỂM
    $tong = round(
        $cc * 0.1 +
        $gk * 0.3 +
        $ck * 0.6,
        2
    );

    $xeploai = xepLoai($tong);

    // CHECK TỒN TẠI
    $check = mysqli_query(
        $conn,
        "SELECT * FROM diem
         WHERE masv='$masv'
         AND malhp='$malhp'"
    );

    // UPDATE
    if (mysqli_num_rows($check) > 0) {

        $sql = "
            UPDATE diem
            SET
                diemchuyencan='$cc',
                diemgiuaky='$gk',
                diemcuoiky='$ck',
                diemtong='$tong',
                xeploai='$xeploai'
            WHERE masv='$masv'
            AND malhp='$malhp'
        ";

    } else {

        // INSERT
        $sql = "
            INSERT INTO diem
            (
                masv,
                malhp,
                diemchuyencan,
                diemgiuaky,
                diemcuoiky,
                diemtong,
                xeploai
            )
            VALUES
            (
                '$masv',
                '$malhp',
                '$cc',
                '$gk',
                '$ck',
                '$tong',
                '$xeploai'
            )
        ";
    }

    echo json_encode([
        "status" =>
            mysqli_query($conn, $sql)
            ? "success"
            : "error"
    ]);

    exit;
}

/*
|------------------------------------------------------------------
| LOAD ĐIỂM
|------------------------------------------------------------------
*/
if ($action == "list") {

    header('Content-Type: application/json');

    // SINH VIÊN CHỈ XEM ĐIỂM CỦA MÌNH
    if ($role == "sinhvien") {

        $sql = "
            SELECT
                d.*,
                sv.hoten,
                mh.tenmon
            FROM diem d
            JOIN sinhvien sv
                ON d.masv = sv.masv
            JOIN lophocphan l
                ON d.malhp = l.malhp
            JOIN monhoc mh
                ON l.mamon = mh.mamon
            WHERE d.masv = '$masv_login'
        ";

    } else {

        $sql = "
            SELECT
                d.*,
                sv.hoten,
                mh.tenmon
            FROM diem d
            JOIN sinhvien sv
                ON d.masv = sv.masv
            JOIN lophocphan l
                ON d.malhp = l.malhp
            JOIN monhoc mh
                ON l.mamon = mh.mamon
        ";
    }

    $rs = mysqli_query($conn, $sql);

    echo json_encode(
        mysqli_fetch_all($rs, MYSQLI_ASSOC)
    );

    exit;
}

/*
|------------------------------------------------------------------
| GPA
|------------------------------------------------------------------
*/
if ($action == "gpa") {

    header('Content-Type: application/json');

    $masv = $_GET['masv'];

    // SINH VIÊN CHỈ XEM GPA CỦA MÌNH
    if ($role == "sinhvien") {

        $masv = $masv_login;
    }

    $rs = mysqli_query($conn,"
        SELECT
            d.diemtong,
            mh.sotinchi
        FROM diem d
        JOIN lophocphan l
            ON d.malhp = l.malhp
        JOIN monhoc mh
            ON l.mamon = mh.mamon
        WHERE d.masv = '$masv'
    ");

    $tong = 0;
    $tinchi = 0;

    while ($r = mysqli_fetch_assoc($rs)) {

        $tong +=
            $r['diemtong']
            * $r['sotinchi'];

        $tinchi += $r['sotinchi'];
    }

    $gpa = $tinchi > 0
        ? round($tong / $tinchi, 2)
        : 0;

    echo json_encode([
        "gpa" => $gpa
    ]);

    exit;
}

/*
|------------------------------------------------------------------
| BẢNG ĐIỂM
|------------------------------------------------------------------
*/
if ($action == "bangdiem") {

    header('Content-Type: application/json');

    $masv = $_GET['masv'] ?? '';

    // SINH VIÊN CHỈ XEM CỦA MÌNH
    if ($role == "sinhvien") {

        $masv = $masv_login;
    }

    $rs = mysqli_query($conn,"
        SELECT
            mh.tenmon,
            mh.sotinchi,
            d.diemtong,
            d.xeploai
        FROM diem d
        JOIN lophocphan l
            ON d.malhp = l.malhp
        JOIN monhoc mh
            ON l.mamon = mh.mamon
        WHERE d.masv = '$masv'
    ");

    $data = [];

    $tongtinchi = 0;
    $tongdiem = 0;

    while ($r = mysqli_fetch_assoc($rs)) {

        $data[] = $r;

        $tongtinchi += $r['sotinchi'];

        $tongdiem +=
            $r['diemtong']
            * $r['sotinchi'];
    }

    $gpa = $tongtinchi > 0
        ? round($tongdiem / $tongtinchi, 2)
        : 0;

    // HỌC LỰC
    $hocluc = "Yếu";

    if ($gpa >= 8.5) {
        $hocluc = "Xuất sắc";
    }
    elseif ($gpa >= 7) {
        $hocluc = "Giỏi";
    }
    elseif ($gpa >= 5.5) {
        $hocluc = "Khá";
    }
    elseif ($gpa >= 4) {
        $hocluc = "Trung bình";
    }

    echo json_encode([
        "monhoc" => $data,
        "tongtinchi" => $tongtinchi,
        "gpa" => $gpa,
        "hocluc" => $hocluc
    ]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <title>Quản lý điểm</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet"
          href="../css/diem.css">

</head>

<body
    data-role="<?= $role ?>"
    data-masv="<?= $masv_login ?>"
    data-hoten="<?= $hoten_login ?>">

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
                        <a href="../php/giangVien.php">
                            <i class="fas fa-chalkboard-teacher"></i> Giảng viên
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
                        <a href="../php/giangVien.php">
                            <i class="fas fa-chalkboard-teacher"></i> Giảng viên
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

<main class="container">

    <h1 class="page-title">
        📊 Quản lý điểm
    </h1>

    <!-- FORM NHẬP ĐIỂM -->
    <?php if ($role == "admin" || $role == "giangvien") { ?>

    <section class="card">

        <h3>
            <i class="fas fa-plus-circle"></i>
            Nhập điểm sinh viên
        </h3>

        <form onsubmit="save(); return false;"
              class="grid-form">

            <div class="input-group">

                <label>Sinh viên</label>

                <select id="sv"></select>

            </div>

            <div class="input-group">

                <label>Lớp học phần</label>

                <select id="lhp"></select>

            </div>

            <div class="input-group">

                <label>Chuyên cần</label>

                <input id="cc"
                       type="number"
                       step="0.1">

            </div>

            <div class="input-group">

                <label>Giữa kỳ</label>

                <input id="gk"
                       type="number"
                       step="0.1">

            </div>

            <div class="input-group">

                <label>Cuối kỳ</label>

                <input id="ck"
                       type="number"
                       step="0.1">

            </div>

            <button type="submit"
                    class="btn-save">

                Lưu điểm

            </button>

        </form>

    </section>

    <!-- DANH SÁCH ĐIỂM -->
    <section class="card">

        <h3>
            <i class="fas fa-list-alt"></i>
            Danh sách điểm
        </h3>

        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>Mã SV</th>
                        <th>Họ tên</th>
                        <th>Môn</th>
                        <th>CC</th>
                        <th>GK</th>
                        <th>CK</th>
                        <th>Tổng</th>
                        <th>Xếp loại</th>

                    </tr>

                </thead>

                <tbody id="table"></tbody>

            </table>

        </div>

    </section>

    <?php } ?>

    <!-- BẢNG ĐIỂM -->
    <section class="card">

        <h3>
            <i class="fas fa-graduation-cap"></i>
            Tổng kết bảng điểm
        </h3>

        <div class="action-bar">

            <!-- ADMIN + GIẢNG VIÊN -->
            <?php if ($role == "admin" || $role == "giangvien") { ?>

                <select id="svTong"
                        style="max-width:300px;">
                </select>

            <?php } ?>

            <!-- SINH VIÊN -->
            <?php if ($role == "sinhvien") { ?>

                <input type="text"
                       value="<?= $hoten_login ?>"
                       readonly
                       class="input-readonly">

                <!-- SELECT ẨN -->
                <select id="svTong" hidden>
                    <option value="<?= $masv_login ?>">
                        <?= $hoten_login ?>
                    </option>
                </select>

            <?php } ?>

            <button onclick="loadBangDiem()"
                    class="btn-primary">

                Xem bảng điểm

            </button>
            <!-- 🔥 NÚT XUẤT PDF -->
<button onclick="xuatPDF()" class="btn-primary">
    <i class="fas fa-file-pdf"></i> Xuất PDF
</button>

        </div>

        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>Môn</th>
                        <th>Tín chỉ</th>
                        <th>Điểm</th>
                        <th>Xếp loại</th>

                    </tr>

                </thead>

                <tbody id="bangdiem"></tbody>

            </table>

        </div>

        <div class="result-summary">

            <h3 id="tongket"></h3>

        </div>

    </section>

</main>

<script>
    const USER_ROLE = "<?= $role ?>";
    const USER_MASV = "<?= $masv_login ?>";
</script>

<script src="../js/diem.js"></script>

</body>
</html>