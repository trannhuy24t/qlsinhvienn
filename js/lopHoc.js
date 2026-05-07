// ===== ELEMENT =====
const malop = document.getElementById("malop");
const tenlop = document.getElementById("tenlop");
const khoa = document.getElementById("khoa");

// ===== LOAD LỚP =====
function loadLop() {
    fetch("../php/lopHoc.php")
    .then(res => res.json())
    .then(data => {
        render(data);
        renderDropdown(data);
    })
    .catch(err => console.error("Lỗi load lớp:", err));
}

// ===== RENDER TABLE =====
function render(data) {
    let html = "";

    data.forEach(l => {
        // Cập nhật nút bấm với Class và Icon để khớp với CSS mới
        html += `
        <tr>
            <td>${l.malop}</td>
            <td>${l.tenlop}</td>
            <td>${l.khoa}</td>
            <td>
                <button class="btn-edit" onclick="editLop('${l.malop}','${l.tenlop}','${l.khoa}')">
                    <i class="fas fa-edit"></i> Sửa
                </button>
                <button class="btn-delete" onclick="deleteLop('${l.malop}')">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </td>
        </tr>`;
    });

    document.getElementById("table").innerHTML = html;
}

// ===== ADD =====
function addLop() {
    // Kiểm tra dữ liệu trống trước khi gửi
    if (!malop.value || !tenlop.value) {
        alert("Vui lòng nhập đầy đủ Mã lớp và Tên lớp");
        return;
    }

    const formData = new FormData();
    formData.append("action", "add");
    formData.append("malop", malop.value.trim());
    formData.append("tenlop", tenlop.value.trim());
    formData.append("khoa", khoa.value.trim());

    fetch("../php/lopHoc.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Thêm lớp thành công!");
            document.getElementById("formLop").reset();
            loadLop();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error("Lỗi add:", err));
}

// ===== DELETE =====
function deleteLop(malopValue) {
    if (!confirm(`Bạn có chắc muốn xóa lớp ${malopValue}?`)) return;

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("malop", malopValue);

    fetch("../php/lopHoc.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            loadLop();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error("Lỗi delete:", err));
}

// ===== EDIT =====
function editLop(malopValue, tenlopValue, khoaValue) {
    malop.value = malopValue;
    tenlop.value = tenlopValue;
    khoa.value = khoaValue;

    malop.disabled = true; // Không cho sửa mã lớp khi đang cập nhật
    document.getElementById("btnSubmit").innerText = "Cập nhật";
    document.getElementById("btnSubmit").classList.replace("btn-add", "btn-search"); // Đổi màu nút nếu muốn

    document.getElementById("formLop").onsubmit = function () {
        updateLop();
        return false;
    };
}

// ===== UPDATE =====
function updateLop() {
    const formData = new FormData();
    formData.append("action", "update");
    formData.append("malop", malop.value);
    formData.append("tenlop", tenlop.value);
    formData.append("khoa", khoa.value);

    fetch("../php/lopHoc.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Cập nhật thành công!");
            resetForm();
            loadLop();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error("Lỗi update:", err));
}

// Hàm bổ trợ để đưa form về trạng thái Thêm mới
function resetForm() {
    document.getElementById("formLop").reset();
    malop.disabled = false;
    document.getElementById("btnSubmit").innerText = "Thêm lớp";
    document.getElementById("btnSubmit").classList.replace("btn-search", "btn-add");
    document.getElementById("formLop").onsubmit = function () {
        addLop();
        return false;
    };
}

// ===== DROPDOWN =====
function renderDropdown(data) {
    let html = "<option value=''>-- Chọn lớp để xem SV --</option>";
    data.forEach(l => {
        html += `<option value="${l.malop}">${l.tenlop}</option>`;
    });
    document.getElementById("chonlop").innerHTML = html;
}

// ===== LOAD SINH VIÊN THEO LỚP =====
function loadSV() {
    const maLopSelected = document.getElementById("chonlop").value; // Lấy Mã lớp từ dropdown
    const svTable = document.getElementById("svTable");

    if (!maLopSelected) {
        svTable.innerHTML = "<tr><td colspan='2' style='text-align:center;'>Vui lòng chọn lớp để xem</td></tr>";
        return;
    }

    // Sử dụng FormData để đồng bộ với cách xử lý POST trong sinhVien.php
    const formData = new FormData();
    formData.append("action", "search");
    formData.append("malop", maLopSelected); // Gửi malop thay vì tenlop

    fetch("../php/sinhVien.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        let html = "";
        if (data.length === 0) {
            html = "<tr><td colspan='2' style='text-align:center;'>Lớp này hiện chưa có sinh viên</td></tr>";
        } else {
            data.forEach(sv => {
                html += `
                <tr>
                    <td>${sv.masv}</td>
                    <td>${sv.hoten}</td>
                </tr>`;
            });
        }
        svTable.innerHTML = html;
    })
    .catch(err => {
        console.error("Lỗi load SV:", err);
        svTable.innerHTML = "<tr><td colspan='2'>Lỗi khi tải dữ liệu</td></tr>";
    });
}

// ===== INIT =====
window.onload = loadLop;