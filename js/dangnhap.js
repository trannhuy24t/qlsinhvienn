// Dùng DOMContentLoaded để chắc chắn HTML đã load xong hoàn toàn
document.addEventListener("DOMContentLoaded", function() {
    const formlogin = document.getElementById("formlogin");
    const alertError = document.getElementById("alertError");

    // Test xem file JS đã thông chưa
    console.log("JS đã kết nối thành công!");

    if (formlogin) {
        formlogin.addEventListener("submit", function (e) {
            e.preventDefault(); 
            
            console.log("Đang gửi yêu cầu đăng nhập...");

            // 1. Reset trạng thái thông báo lỗi
            alertError.style.display = "none";
            alertError.innerText = "";

            const formData = new FormData(formlogin);

            // 2. Gửi dữ liệu bằng fetch
            // Hãy kiểm tra kỹ đường dẫn: ../php/login.php
            fetch("../php/login.php", {
                method: "POST",
                body: formData
            })
            .then(res => {
                // Kiểm tra xem server có trả về mã 200 không
                if (!res.ok) {
                    throw new Error("Lỗi Server: " + res.status);
                }
                return res.json();
            })
            .then(data => {
                console.log("Kết quả từ PHP:", data);
                if (data.status === "error") {
                    alertError.innerText = data.message;
                    alertError.style.display = "block";
                } else if (data.status === "success") {
                    // Chuyển hướng nếu thành công
                    window.location.href = data.redirect; 
                }
            })
            .catch(err => {
                console.error("Lỗi Fetch:", err);
                alertError.innerText = "Lỗi kết nối hoặc lỗi cú pháp PHP!";
                alertError.style.display = "block";
            });
        });
    } else {
        console.error("Không tìm thấy formlogin! Hãy kiểm tra lại ID trong file HTML.");
    }
});