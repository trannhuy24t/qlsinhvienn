window.onload = function() {
    const formdangky = document.getElementById("formdangky");
    const alertError = document.getElementById("alertError");

    if (formdangky) {
        formdangky.addEventListener("submit", function (e) {
            e.preventDefault(); // Chặn việc load lại trang

            alertError.style.display = "none";

            const formData = new FormData(formdangky);

            fetch("../php/dangky.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json()) // Chuyển phản hồi từ PHP sang dạng JSON
            .then(data => {
                if (data.status === "error") {
                    // Nếu có lỗi, hiển thị thông báo lỗi ngay tại trang
                    alertError.innerText = data.message;
                    alertError.style.display = "block";
                } else {
                    // Nếu thành công, thông báo và chuyển hướng sang trang đăng nhập
                    alert("Đăng ký thành công!");
                    window.location.href = "dangnhap.html";
                }
            })
            .catch(err => {
                alertError.innerText = "Lỗi hệ thống không thể đăng ký!";
                alertError.style.display = "block";
                console.error(err);
            });
        });
    }
}