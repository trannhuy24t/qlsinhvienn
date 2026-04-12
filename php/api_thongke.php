<?php
include "config.php";
header('Content-Type: application/json');

$sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM sinhvien"))['t'];
$lh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM lophocphan"))['t'];
$gv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM giangvien"))['t'];
$mh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM monhoc"))['t'];

echo json_encode([
    "sinhvien" => $sv,
    "lophoc" => $lh,
    "giangvien" => $gv,
    "monhoc" => $mh
]);
?>