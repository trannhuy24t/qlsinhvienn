<?php
// Bật buffer để tránh lỗi "Output already started"
ob_start(); 

include "config.php";
require('../fpdf.php'); 

$masv = $_GET['masv'] ?? '';

// Kiểm tra kết nối database
if (!$conn) {
    die("Kết nối thất bại");
}

$pdf = new FPDF();
$pdf->AddPage();

// ===== LOGO =====
// Dùng __DIR__ để đảm bảo đường dẫn tuyệt đối chính xác
$logo = __DIR__ . '/../images/logo.png';
if (file_exists($logo)) {
    $pdf->Image($logo, 10, 10, 30);
}

// ===== TITLE =====
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'BANG DIEM SINH VIEN',0,1,'C');
$pdf->Ln(10);

// ===== THÔNG TIN NGÀY XUẤT =====
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,10,'Ngay xuat: '.date("d/m/Y"),0,1,'R');

// ===== HEADER TABLE =====
$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,10,'Mon hoc',1,0,'C');
$pdf->Cell(30,10,'Tin chi',1,0,'C');
$pdf->Cell(30,10,'Diem',1,0,'C');
$pdf->Cell(30,10,'Xep loai',1,0,'C');
$pdf->Ln();

// ===== DATA =====
$pdf->SetFont('Arial','',12);
$sql = "SELECT mh.tenmon, mh.sotinchi, d.diemtong, d.xeploai 
        FROM diem d 
        JOIN lophocphan l ON d.malhp=l.malhp 
        JOIN monhoc mh ON l.mamon=mh.mamon 
        WHERE d.masv='$masv'";

$rs = mysqli_query($conn, $sql);

while($r = mysqli_fetch_assoc($rs)){
    // Lưu ý: Nếu tenmon có dấu, FPDF gốc sẽ lỗi hiển thị
    $pdf->Cell(60,10, $r['tenmon'], 1); 
    $pdf->Cell(30,10, $r['sotinchi'], 1, 0, 'C');
    $pdf->Cell(30,10, $r['diemtong'], 1, 0, 'C');
    $pdf->Cell(30,10, $r['xeploai'], 1, 0, 'C');
    $pdf->Ln();
}

// ===== CHỮ KÝ =====
$pdf->Ln(15);
$pdf->Cell(0,10,'Giang vien ky ten',0,1,'R');

// Xóa buffer rác trước khi xuất PDF
ob_end_clean(); 
$pdf->Output('I', 'BangDiem_'.$masv.'.pdf'); 
?>