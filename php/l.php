<?php
include "config.php";

$username = "nguyen van cuong";
$password = password_hash("123456", PASSWORD_DEFAULT);
$role = "giangvien";
$user_id = "GV001";

$check = $conn->prepare("SELECT * FROM taikhoan WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();

$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "Tài khoản giảng viên đã tồn tại";
} else {

    $sql = "INSERT INTO taikhoan (username, password, role, user_id)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $role, $user_id);

    if ($stmt->execute()) {
        echo "Tạo tài khoản giảng viên thành công";
    } else {
        echo "Lỗi: " . $stmt->error;
    }
}
?>