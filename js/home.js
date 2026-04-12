function updateDashboardStats() {
    fetch('../php/api_thongke.php')
        .then(response => {
            if (!response.ok) throw new Error("Không tìm thấy file API");
            return response.json();
        })
        .then(data => {
            console.log("Dữ liệu nhận về:", data); // Kiểm tra xem dữ liệu có tới không
            
            document.getElementById('count-sv').innerText = data.sinhvien || 0;
            document.getElementById('count-lh').innerText = data.lophoc || 0;
            document.getElementById('count-gv').innerText = data.giangvien || 0;
            document.getElementById('count-mh').innerText = data.monhoc || 0;
        })
        .catch(error => {
            console.error('Lỗi thực tế:', error);
            alert("Lỗi tải số liệu: " + error.message);
        });
}

document.addEventListener("DOMContentLoaded", updateDashboardStats);