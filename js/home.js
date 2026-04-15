function updateDashboardStats() {
    fetch('../php/api_thongke.php')
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new TypeError("Phản hồi từ Server không phải là JSON!");
            }
            if (!response.ok) throw new Error("Lỗi kết nối API: " + response.status);
            return response.json();
        })
        .then(data => {
            console.log("Dữ liệu nhận được:", data); // Dòng này để bạn F12 lên kiểm tra

            const elSv = document.getElementById('count-sv');
            const elL = document.getElementById('count-l');
            const elGv = document.getElementById('count-gv');
            const elMh = document.getElementById('count-mh');

            // FIX: Đổi tên biến cho khớp với file PHP (total_sv, total_l...)
            if (elSv) elSv.innerText = data.total_sv ?? 0;
            if (elL) elL.innerText = data.total_l ?? 0;
            if (elGv) elGv.innerText = data.total_gv ?? 0;
            if (elMh) elMh.innerText = data.total_mh ?? 0;
        })
        .catch(error => {
            console.error('Lỗi chi tiết:', error);
        });
}

// Gọi hàm khi trang load xong
document.addEventListener("DOMContentLoaded", updateDashboardStats);

function syncLoginStatus() {
    // Gọi một API nhỏ để kiểm tra xem session còn sống không
    fetch('../php/api_thongke.php')
        .then(res => res.json())
        .then(data => {
            const navUl = document.querySelector(".navbar ul");
            if (!navUl) return;

            // Tìm thẻ <a> có chữ "Đăng nhập"
            const loginLink = Array.from(navUl.querySelectorAll("a"))
                                   .find(a => a.innerText.includes("Đăng nhập"));
        });
}

// Chạy hàm này mỗi khi load bất kỳ trang nào
document.addEventListener("DOMContentLoaded", syncLoginStatus);
// Bổ sung: Tự động cập nhật mỗi 30 giây (nếu cần số liệu thời gian thực)
// setInterval(updateDashboardStats, 30000);