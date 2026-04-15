<?php
session_start();
session_destroy(); // Xóa toàn bộ session
header("Location: ../pages/dangnhap.html"); // Chuyển hướng về trang đăng nhập
exit();
?>