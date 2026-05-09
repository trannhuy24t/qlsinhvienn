// ===== ELEMENT =====
const sv = document.getElementById("sv");
const lhp = document.getElementById("lhp");

const cc = document.getElementById("cc");
const gk = document.getElementById("gk");
const ck = document.getElementById("ck");

const table = document.getElementById("table");

const svTong = document.getElementById("svTong");
const bangdiem = document.getElementById("bangdiem");
const tongket = document.getElementById("tongket");

// ===== ROLE =====
const role = document.body.dataset.role || "";

// ===== USER SAFE =====
const currentUserId = document.body.dataset.masv || "";
const currentHoten = document.body.dataset.hoten || "";

// =====================================================
// LOAD ALL (giống style lopHoc.js)
// =====================================================
function loadAll() {

    if (role === "admin" || role === "giangvien") {

        loadSV();
        loadLHP();
        loadDiem();
        loadSVTong();
    }

    if (role === "sinhvien") {

        loadSVTongSinhVien();
        loadBangDiem();
    }
}

// =====================================================
// LOAD SINH VIÊN
// =====================================================
function loadSV() {

    fetch("../php/sinhVien.php")
        .then(res => res.json())
        .then(data => {

            let html = "";

            data.forEach(s => {

                html += `
                    <option value="${s.masv}">
                        ${s.hoten}
                    </option>
                `;
            });

            if (sv) sv.innerHTML = html;
        })
        .catch(err => console.error("loadSV lỗi:", err));
}

// =====================================================
// LOAD SV TỔNG
// =====================================================
// Tìm hàm loadSV() và loadSVTong(), sửa đường dẫn fetch:

function loadSV() {
    // SỬA: Thay "../php/sinhVien.php" thành "../php/diem.php?action=get_students"
    fetch("../php/diem.php?action=get_students") 
        .then(res => res.json())
        .then(data => {
            let html = '<option value="">-- Chọn sinh viên --</option>'; // Nên thêm dòng mặc định
            data.forEach(s => {
                html += `<option value="${s.masv}">${s.masv} - ${s.hoten}</option>`;
            });
            if (sv) sv.innerHTML = html;
        })
        .catch(err => console.error("loadSV lỗi:", err));
}

function loadSVTong() {
    if (role === "sinhvien") {
        loadSVTongSinhVien();
        return;
    }
    // SỬA: Tương tự như trên
    fetch("../php/diem.php?action=get_students")
        .then(res => res.json())
        .then(data => {
            let html = '<option value="">-- Chọn sinh viên --</option>';
            data.forEach(s => {
                html += `<option value="${s.masv}">${s.masv} - ${s.hoten}</option>`;
            });
            if (svTong) svTong.innerHTML = html;
        })
        .catch(err => console.error("loadSVTong lỗi:", err));
}

// =====================================================
// LOAD LHP
// =====================================================
function loadLHP() {
    // SỬA: Thay đường dẫn fetch để lấy danh sách lớp học phần từ diem.php
    fetch("../php/diem.php?action=get_classes")
        .then(res => res.json())
        .then(data => {
            let html = '<option value="">-- Chọn môn học --</option>';
            data.forEach(l => {
                html += `<option value="${l.malhp}">${l.malhp} - ${l.tenmon}</option>`;
            });
            if (lhp) lhp.innerHTML = html;
        })
        .catch(err => console.error("loadLHP lỗi:", err));
}

// =====================================================
// SAVE ĐIỂM
// =====================================================
function save() {

    if (role === "sinhvien") {
        alert("Không có quyền!");
        return;
    }

    if (!sv || !lhp || !sv.value || !lhp.value) {
        alert("Chọn sinh viên và lớp!");
        return;
    }

    let f = new FormData();

    f.append("action", "save");
    f.append("masv", sv.value);
    f.append("malhp", lhp.value);
    f.append("diemchuyencan", cc?.value || 0);
    f.append("diemgiuaky", gk?.value || 0);
    f.append("diemcuoiky", ck?.value || 0);

    fetch("../php/diem.php", {
        method: "POST",
        body: f
    })
        .then(res => res.json())
        .then(d => {

            if (d.status === "success") {

                alert("Lưu thành công!");
                clearForm();
                loadDiem();

            } else {

                alert(d.message || "Lỗi!");
            }
        })
        .catch(err => console.error("save lỗi:", err));
}

// =====================================================
// LOAD DANH SÁCH ĐIỂM
// =====================================================
function loadDiem() {

    if (role === "sinhvien") {

        if (table) {

            table.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center;">
                        Sinh viên chỉ xem bảng tổng kết
                    </td>
                </tr>
            `;
        }
        return;
    }

    fetch("../php/diem.php?action=list")
        .then(res => res.json())
        .then(data => {

            let html = "";

            data.forEach(d => {

                html += `
                <tr>
                    <td>${d.masv}</td>
                    <td>${d.hoten}</td>
                    <td>${d.tenmon}</td>
                    <td>${d.diemchuyencan}</td>
                    <td>${d.diemgiuaky}</td>
                    <td>${d.diemcuoiky}</td>
                    <td><b>${d.diemtong}</b></td>
                    <td>${d.xeploai}</td>
                </tr>
                `;
            });

            if (table) table.innerHTML = html;
        })
        .catch(err => console.error("loadDiem lỗi:", err));
}

// =====================================================
// BẢNG ĐIỂM
// =====================================================
function loadBangDiem() {

    let masv =
        svTong?.value ||
        currentUserId ||
        "";

    if (!masv) {

        if (bangdiem) {

            bangdiem.innerHTML = `
                <tr><td colspan="4">Không có dữ liệu</td></tr>
            `;
        }
        return;
    }

    fetch("../php/diem.php?action=bangdiem&masv=" + masv)
        .then(r => r.json())
        .then(data => {

            if (!data.monhoc) return;

            let html = "";

            data.monhoc.forEach(m => {

                html += `
                <tr>
                    <td>${m.tenmon}</td>
                    <td>${m.sotinchi}</td>
                    <td>${m.diemtong}</td>
                    <td>${m.xeploai}</td>
                </tr>
                `;
            });

            if (bangdiem) bangdiem.innerHTML = html;

            if (tongket) {

                tongket.innerHTML = `
                    GPA: ${data.gpa} |
                    Tín chỉ: ${data.tongtinchi} |
                    Học lực: ${data.hocluc}
                `;
            }
        })
        .catch(err => console.error("loadBangDiem lỗi:", err));
}

// =====================================================
// CLEAR FORM
// =====================================================
function clearForm() {

    if (cc) cc.value = "";
    if (gk) gk.value = "";
    if (ck) ck.value = "";
}

// =====================================================
// INIT (GIỐNG LOPHOC.JS)
// =====================================================
window.onload = function () {

    if (role === "sinhvien") {

        const formSection = document.querySelector(".grid-form");

        if (formSection) {

            const card = formSection.closest(".card");
            if (card) card.style.display = "none";
        }

        const cards = document.querySelectorAll(".card");

        if (cards.length > 1 && cards[1]) {
            cards[1].style.display = "none";
        }
    }

    loadAll();
};
// ===== XUẤT PDF =====
// ===== XUẤT PDF (THEO USER) =====
function xuatPDF() {

    let userId = "";

    // SINH VIÊN → luôn lấy user hiện tại
    if (role === "sinhvien") {

        userId = currentUserId;

    } else {

        // ADMIN / GIẢNG VIÊN → lấy từ dropdown nếu có
        userId = svTong ? svTong.value : "";
    }

    if (!userId) {

        alert("Không xác định được người dùng!");
        return;
    }

    window.location.href =
        "../php/pdf.php?user_id=" + userId;
}