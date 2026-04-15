document.addEventListener("DOMContentLoaded", function () {
    // 1. Gọi API lấy dữ liệu thống kê
    fetch('../php/api_thongke.php') 
        .then(res => {
            if (!res.ok) throw new Error("HTTP error: " + res.status);
            return res.json();
        })
        .then(data => {
            console.log("Dữ liệu nhận được:", data);

            // 2. Kiểm tra quyền truy cập (Session)
            if (data.status === "error") {
                alert("Phiên đăng nhập hết hạn!");
                window.location.href = "../php/login.php"; // Cập nhật đúng file login của bạn
                return;
            }

            // 3. Cập nhật các thẻ con số (Quick Stats)
            const updateText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.innerText = value ?? 0;
            };

            updateText('total-sv', data.total_sv);
            updateText('total-gv', data.total_gv);
            updateText('total-mh', data.total_mh);
            updateText('total-l',  data.total_l);    // Lớp hành chính (hiện 0 ở trang chủ)
            updateText('total-lhp', data.total_lhp); // Số môn có SV (hiện 0 ở thống kê)

            // 4. Hiển thị danh sách "Số SV theo môn"
            const listEl = document.getElementById('class-stats-list');
            if (listEl) {
                if (data.classes && data.classes.length > 0) {
                    listEl.innerHTML = data.classes.map(c => `
                        <div class="class-item" style="display:flex; justify-content:space-between; padding:12px; border-bottom:1px solid #f0f0f0;">
                            <span>${c.tenmon}</span>
                            <span class="badge" style="background:#e1f5fe; color:#03a9f4; padding:2px 8px; border-radius:10px;">
                                <b>${c.soluong} SV</b>
                            </span>
                        </div>
                    `).join('');
                } else {
                    listEl.innerHTML = "<p style='padding:15px; color:#999;'>Chưa có dữ liệu sinh viên theo môn.</p>";
                }
            }

            // 5. Vẽ biểu đồ tỷ lệ học lực
            const canvas = document.getElementById('gradeChart');
            if (canvas && data.grades) {
                const ctx = canvas.getContext('2d');
                
                // Hủy biểu đồ cũ nếu có (tránh lỗi đè chart khi load lại)
                if (window.myPieChart) window.myPieChart.destroy();

                window.myPieChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Giỏi', 'Khá', 'Trung bình', 'Yêu'],
                        datasets: [{
                            data: [
                                Number(data.grades.Gioi) || 0,
                                Number(data.grades.Kha) || 0,
                                Number(data.grades.TrungBinh) || 0,
                                Number(data.grades.Yeu) || 0
                            ],
                            backgroundColor: ['#2ecc71', '#3498db', '#f1c40f', '#e74c3c'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        })
        .catch(err => {
            console.error("Lỗi hệ thống:", err);
        });
});