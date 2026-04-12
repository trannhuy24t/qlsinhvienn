window.onload = function() {
    const formlogin = document.getElementById("formlogin");
    const alertError = document.getElementById("alertError");

    if (formlogin) {
        formlogin.addEventListener("submit", function (e) {
            e.preventDefault(); // Chặn chuyển trang
            alertError.style.display = "none";

            const formData = new FormData(formlogin);

            fetch("../php/login.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "error") {
                    alertError.innerText = data.message;
                    alertError.style.display = "block";
                } else {
                    window.location.href = data.redirect; // Chuyển trang chủ
                }
            })
            .catch(err => {
                console.error("Lỗi:", err);
                alertError.innerText = "Lỗi kết nối server!";
                alertError.style.display = "block";
            });
        });
    }
};