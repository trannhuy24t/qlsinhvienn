<?php
session_start();
include "config.php";

// 1. Kiểm tra đăng nhập và lấy tên người dùng
$ten_nguoi_dung = isset($_SESSION['hoten']) ? $_SESSION['hoten'] : 'Khách';

// 2. Truy vấn số lượng từ Database
$count_sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sinhvien"))['total'];
$count_lh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lophocphan"))['total'];
$count_gv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM giangvien"))['total'];
$count_mh = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM monhoc"))['total'];
?>