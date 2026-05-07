document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formdangky");
    const alertError = document.getElementById("alertError");

    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // reset lỗi
        alertError.style.display = "none";
        alertError.innerText = "";

        // lấy dữ liệu
        const ma = document.getElementById("ma").value.trim();
        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value.trim();
        const confirm = document.getElementById("confirmpassword").value.trim();

        // ===== VALIDATE FRONTEND =====
        if (!ma || !username || !password || !confirm) {
            showError("Vui lòng nhập đầy đủ thông tin!");
            return;
        }

        if (password.length < 6) {
            showError("Mật khẩu phải từ 6 ký tự trở lên!");
            return;
        }

        if (password !== confirm) {
            showError("Mật khẩu xác nhận không khớp!");
            return;
        }

        try {
            const formData = new FormData(form);

            const res = await fetch("../php/dangky.php", {
                method: "POST",
                body: formData
            });

            const data = await res.json();

            if (data.status === "error") {
                showError(data.message);
            } else {
                alert("Đăng ký thành công!");
                window.location.href = "dangnhap.html";
            }

        } catch (err) {
            showError("Lỗi hệ thống, vui lòng thử lại!");
            console.error(err);
        }
    });

    function showError(message) {
        alertError.innerText = message;
        alertError.style.display = "block";
    }
});