document.addEventListener("DOMContentLoaded", function () {

    // ===== LOAD API =====
    fetch("../php/api_thongKe.php?action=load")
        .then(res => {
            if (!res.ok) {
                throw new Error("HTTP Error: " + res.status);
            }
            return res.json();
        })

        .then(data => {
            console.log("DATA:", data);

            // ===== KIỂM TRA LỖI =====
            if (data.status === "error") {
                alert(data.message || "Có lỗi xảy ra");
                return;
            }

            // ===== QUICK STATS =====
            setText("total-sv", data.total_sv);
            setText("total-gv", data.total_gv);
            setText("total-mh", data.total_mh);

            // HTML của bạn đang dùng total-lh
            setText("total-lh", data.total_lhp);

            // ===== DANH SÁCH MÔN =====
            renderClassList(data.classes);

            // ===== BIỂU ĐỒ =====
            renderChart(data.grades);
        })

        .catch(err => {
            console.error("Lỗi fetch:", err);
        });

});


// ===== UPDATE TEXT =====
function setText(id, value) {
    const el = document.getElementById(id);

    if (el) {
        el.innerText = value ?? 0;
    }
}


// ===== RENDER DANH SÁCH MÔN =====
function renderClassList(classes) {

    const listEl = document.getElementById("class-stats-list");

    if (!listEl) return;

    if (!classes || classes.length === 0) {

        listEl.innerHTML = `
            <p style="padding:15px;color:#999">
                Chưa có dữ liệu
            </p>
        `;

        return;
    }

    let html = "";

    classes.forEach(c => {

        html += `
            <div class="class-item"
                style="
                    display:flex;
                    justify-content:space-between;
                    padding:12px;
                    border-bottom:1px solid #eee;
                ">

                <span>${c.tenmon}</span>

                <span
                    style="
                        background:#e3f2fd;
                        padding:4px 10px;
                        border-radius:10px;
                        color:#1976d2;
                        font-weight:bold;
                    ">
                    ${c.soluong} SV
                </span>
            </div>
        `;
    });

    listEl.innerHTML = html;
}


// ===== VẼ CHART =====
function renderChart(grades) {

    const canvas = document.getElementById("gradeChart");

    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    // Xóa chart cũ
    if (window.gradeChartInstance) {
        window.gradeChartInstance.destroy();
    }

    window.gradeChartInstance = new Chart(ctx, {

        type: "pie",

        data: {
            labels: ["Giỏi", "Khá", "Trung bình", "Yếu"],

            datasets: [{
                data: [
                    Number(grades.Gioi) || 0,
                    Number(grades.Kha) || 0,
                    Number(grades.TrungBinh) || 0,
                    Number(grades.Yeu) || 0
                ],

                backgroundColor: [
                    "#2ecc71",
                    "#3498db",
                    "#f1c40f",
                    "#e74c3c"
                ],

                borderWidth: 1
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: "bottom"
                }
            }
        }
    });
}