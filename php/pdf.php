<?php
ob_start();

session_start();
include "config.php";
require('../fpdf.php');

/** @var mysqli $conn */

if (!$conn) {
    die("Kết nối thất bại database");
}

// =========================
// LẤY USER LOGIN
// =========================
$masv = $_SESSION['masv'] ?? '';
$hoten = $_SESSION['hoten'] ?? '';

if (empty($masv)) {
    die("Không tìm thấy user đăng nhập");
}

// =========================
// TẠO PDF
// =========================
$pdf = new FPDF();
$pdf->AddPage();

// ===== LOGO =====
$logo = __DIR__ . '/../images/logo.png';
if (file_exists($logo)) {
    $pdf->Image($logo, 10, 10, 30);
}

// ===== TITLE =====
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'BANG DIEM SINH VIEN',0,1,'C');

// ===== TÊN SINH VIÊN =====
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,10,'Sinh vien: '.$hoten,0,1,'C');

$pdf->Ln(5);

// ===== NGÀY XUẤT =====
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,10,'Ngay xuat: '.date("d/m/Y"),0,1,'R');

$pdf->Ln(5);

// ===== TABLE HEADER =====
$pdf->SetFont('Arial','B',12);
$pdf->Cell(70,10,'Mon hoc',1,0,'C');
$pdf->Cell(30,10,'Tin chi',1,0,'C');
$pdf->Cell(30,10,'Diem',1,0,'C');
$pdf->Cell(30,10,'Xep loai',1,1,'C');

// ===== QUERY =====
$sql = "
    SELECT mh.tenmon, mh.sotinchi, d.diemtong, d.xeploai
    FROM diem d
    JOIN lophocphan l ON d.malhp = l.malhp
    JOIN monhoc mh ON l.mamon = mh.mamon
    WHERE d.masv = '$masv'
";

$rs = mysqli_query($conn, $sql);

// CHECK QUERY
if (!$rs) {
    die("Query lỗi: " . mysqli_error($conn));
}

// ===== DATA =====
$pdf->SetFont('Arial','',12);

if (mysqli_num_rows($rs) == 0) {

    $pdf->Cell(0,10,'Khong co du lieu diem',1,1,'C');

} else {

    while ($r = mysqli_fetch_assoc($rs)) {

        $pdf->Cell(70,10,$r['tenmon'],1);
        $pdf->Cell(30,10,$r['sotinchi'],1,0,'C');
        $pdf->Cell(30,10,$r['diemtong'],1,0,'C');
        $pdf->Cell(30,10,$r['xeploai'],1,1,'C');
    }
}

// ===== FOOTER =====
$pdf->Ln(10);
$pdf->Cell(0,10,'Giang vien ky ten',0,1,'R');

// CLEAN BUFFER
ob_end_clean();

$pdf->Output('I', 'BangDiem_'.$masv.'.pdf');
?>