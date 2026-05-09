<?php
session_start();
include "config.php";

/** @var mysqli $conn */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* =====================================================
   API JSON
===================================================== */

if ($action != "") {

    header('Content-Type: application/json; charset=utf-8');

    // ===== LOAD =====
    if ($action == "load") {

        $sql = "
            SELECT
                sv.*,
                l.tenlop
            FROM sinhvien sv
            LEFT JOIN lop l
            ON sv.malop = l.malop
            ORDER BY sv.masv ASC
        ";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        echo json_encode($data);
        exit;
    }

    // ===== ADD =====
    if ($action == "add") {

        $masv = mysqli_real_escape_string($conn, $_POST['masv']);
        $hoten = mysqli_real_escape_string($conn, $_POST['hoten']);
        $ngaysinh = mysqli_real_escape_string($conn, $_POST['ngaysinh']);
        $gioitinh = mysqli_real_escape_string($conn, $_POST['gioitinh']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $sodienthoai = mysqli_real_escape_string($conn, $_POST['sodienthoai']);
        $diachi = mysqli_real_escape_string($conn, $_POST['diachi']);
        $malop = mysqli_real_escape_string($conn, $_POST['malop']);

        // CHECK TRÙNG
        $check = mysqli_query($conn,
            "SELECT * FROM sinhvien WHERE masv='$masv'"
        );

        if (mysqli_num_rows($check) > 0) {

            echo json_encode([
                "status" => "error",
                "message" => "Mã sinh viên đã tồn tại!"
            ]);

            exit;
        }

        // UPLOAD ẢNH
        $anh = "";

        if (!empty($_FILES['anh']['name'])) {

            $target_dir = "../images/";

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $anh = time() . "_" . basename($_FILES['anh']['name']);

            move_uploaded_file(
                $_FILES['anh']['tmp_name'],
                $target_dir . $anh
            );
        }

        $sql = "
            INSERT INTO sinhvien(
                masv,
                hoten,
                ngaysinh,
                gioitinh,
                email,
                sodienthoai,
                diachi,
                malop,
                anh
            )
            VALUES(
                '$masv',
                '$hoten',
                '$ngaysinh',
                '$gioitinh',
                '$email',
                '$sodienthoai',
                '$diachi',
                '$malop',
                '$anh'
            )
        ";

        if (mysqli_query($conn, $sql)) {

            echo json_encode([
                "status" => "success"
            ]);

        } else {

            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }

        exit;
    }

    // ===== DELETE =====
    if ($action == "delete") {

        $masv = mysqli_real_escape_string($conn, $_POST['masv']);

        mysqli_query($conn,
            "DELETE FROM diem WHERE masv='$masv'"
        );

        $sql = "DELETE FROM sinhvien WHERE masv='$masv'";

        if (mysqli_query($conn, $sql)) {

            echo json_encode([
                "status" => "success"
            ]);

        } else {

            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }

        exit;
    }

    // ===== UPDATE =====
    if ($action == "update") {

        $masv = mysqli_real_escape_string($conn, $_POST['masv']);
        $hoten = mysqli_real_escape_string($conn, $_POST['hoten']);
        $ngaysinh = mysqli_real_escape_string($conn, $_POST['ngaysinh']);
        $gioitinh = mysqli_real_escape_string($conn, $_POST['gioitinh']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $sodienthoai = mysqli_real_escape_string($conn, $_POST['sodienthoai']);
        $diachi = mysqli_real_escape_string($conn, $_POST['diachi']);
        $malop = mysqli_real_escape_string($conn, $_POST['malop']);

        $sql_img = "";

        if (!empty($_FILES['anh']['name'])) {

            $target_dir = "../images/";

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $anh = time() . "_" . basename($_FILES['anh']['name']);

            if (move_uploaded_file(
                $_FILES['anh']['tmp_name'],
                $target_dir . $anh
            )) {

                $sql_img = ", anh='$anh'";
            }
        }

        $sql = "
            UPDATE sinhvien SET
                hoten='$hoten',
                ngaysinh='$ngaysinh',
                gioitinh='$gioitinh',
                email='$email',
                sodienthoai='$sodienthoai',
                diachi='$diachi',
                malop='$malop'
                $sql_img
            WHERE masv='$masv'
        ";

        if (mysqli_query($conn, $sql)) {

            echo json_encode([
                "status" => "success"
            ]);

        } else {

            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }

        exit;
    }

    // ===== SEARCH =====
    if ($action == "search") {

        $where = [];

        if (!empty($_POST['masv'])) {
            $masv = mysqli_real_escape_string($conn, $_POST['masv']);
            $where[] = "sv.masv LIKE '%$masv%'";
        }

        if (!empty($_POST['hoten'])) {
            $hoten = mysqli_real_escape_string($conn, $_POST['hoten']);
            $where[] = "sv.hoten LIKE '%$hoten%'";
        }

        if (!empty($_POST['malop'])) {
            $malop = mysqli_real_escape_string($conn, $_POST['malop']);
            $where[] = "sv.malop='$malop'";
        }

        if (!empty($_POST['gioitinh'])) {
            $gioitinh = mysqli_real_escape_string($conn, $_POST['gioitinh']);
            $where[] = "sv.gioitinh='$gioitinh'";
        }

        $sql = "
            SELECT
                sv.*,
                l.tenlop
            FROM sinhvien sv
            LEFT JOIN lop l
            ON sv.malop = l.malop
        ";

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        echo json_encode($data);

        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Quản lý sinh viên</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet"
          href="../css/sinhVien.css">

</head>

<body>

<header class="main-header">

    <h1>
        Hệ Thống Quản Lý<br>
        Sinh Viên
    </h1>

    <nav class="navbar">

        <ul>

            <li><a href="../php/home.php">
                <i class="fas fa-home"></i> Trang chủ
            </a></li>

            <li><a href="../php/sinhVien.php" class="active">
                <i class="fas fa-user-graduate"></i> Sinh viên
            </a></li>

            <li><a href="../php/lopHoc.php">
                <i class="fas fa-school"></i> Lớp học
            </a></li>

            <li><a href="../php/monHoc.php">
                <i class="fas fa-book"></i> Môn học
            </a></li>

            <li><a href="../php/giangVien.php">
                <i class="fas fa-chalkboard-teacher"></i> Giảng viên
            </a></li>

            <li><a href="../php/diem.php">
                <i class="fas fa-poll-h"></i> Bảng điểm
            </a></li>

            <li><a href="../php/thongKe.php">
                <i class="fas fa-chart-line"></i> Thống kê
            </a></li>
            
             <li><a href="../php/lichhoc.php">
                <i class="fas fa-file-alt"></i> Lịch học
            </a></li>

            <li><a href="../php/logout.php">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a></li>

        </ul>

    </nav>

</header>

<main class="main-content">

    <h2 class="page-title">
        Quản lý sinh viên
    </h2>

    <!-- FORM -->

    <div class="card-form">

        <form id="formsinhvien"
              enctype="multipart/form-data"
              onsubmit="add(); return false;">

            <div class="input-row">

                <input type="text"
                       id="masv"
                       name="masv"
                       placeholder="Mã SV"
                       required>

                <input type="text"
                       id="hoten"
                       name="hoten"
                       placeholder="Họ tên"
                       required>

                <input type="date"
                       id="ngaysinh"
                       name="ngaysinh">

                <select id="gioitinh"
                        name="gioitinh">

                    <option value="">Giới tính</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>

                </select>

            </div>

            <div class="input-row">

                <input type="email"
                       id="email"
                       name="email"
                       placeholder="Email">

                <input type="text"
                       id="sodienthoai"
                       name="sodienthoai"
                       placeholder="SĐT">

                <input type="text"
                       id="diachi"
                       name="diachi"
                       placeholder="Địa chỉ">

                <input type="text"
                       id="malop"
                       name="malop"
                       placeholder="Mã lớp">

            </div>

            <div class="input-row action-row">

                <input type="file"
                       id="anh"
                       name="anh">

                <button type="submit"
                        id="btn-submit"
                        class="btn-save">

                    Thêm sinh viên

                </button>

            </div>

        </form>

    </div>

    <!-- SEARCH -->

    <div class="search-section">

        <h3>
            <i class="fas fa-search"></i>
            Tìm kiếm nhanh
        </h3>

        <div class="search-grid">

            <input id="s_masv"
                   placeholder="Mã SV">

            <input id="s_hoten"
                   placeholder="Họ tên">

            <input id="s_malop"
                   placeholder="Mã lớp">

            <select id="s_gioitinh">

                <option value="">Giới tính</option>
                <option value="Nam">Nam</option>
                <option value="Nữ">Nữ</option>

            </select>

            <button onclick="search()"
                    class="btn-search">

                Tìm kiếm

            </button>

        </div>

    </div>

    <!-- TABLE -->

    <div class="table-wrapper">

        <table>

            <thead>

                <tr>

                    <th>Mã SV</th>
                    <th>Họ tên</th>
                    <th>Mã lớp</th>
                    <th>Tên lớp</th>
                    <th>Giới tính</th>
                    <th>Ảnh</th>
                    <th>Hành động</th>

                </tr>

            </thead>

            <tbody id="table"></tbody>

        </table>

    </div>

</main>

<script src="../js/sinhVien.js"></script>

</body>
</html>