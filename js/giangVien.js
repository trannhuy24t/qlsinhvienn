// ===== KIỂM TRA PHẦN TỬ TRƯỚC KHI SỬ DỤNG =====
const safeGet = (id) => document.getElementById(id);

// ===== LOAD ALL DATA =====
function loadAll() {
    // Luôn tải danh sách phân công (ai cũng xem được)
    loadAssign();

    // Chỉ tải các dữ liệu quản trị nếu các phần tử tồn tại (dành cho Admin)
    if (safeGet("gvTable")) {
        loadGV();
    }
    if (safeGet("lhpList")) {
        loadLHPCheckboxes();
    }
}

// ===== LOAD GIẢNG VIÊN =====
function loadGV() {
    const gvTable = safeGet("gvTable");
    const gvSelect = safeGet("gvSelect");

    fetch("../php/giangVien.php?action=listGV")
    .then(r => r.json())
    .then(data => {
        let html = "", select = '<option value="">-- Chọn giảng viên --</option>';
        data.forEach(g => {
            html += `
            <tr>
                <td>${g.magv}</td>
                <td>${g.hoten}</td>
                <td>
                    <button class="btn-edit" onclick="editGV('${g.magv}','${g.hoten}','${g.email}','${g.sodienthoai}','${g.chuyennganh}')">Sửa</button>
                    <button class="btn-delete" onclick="deleteGV('${g.magv}')">Xóa</button>
                </td>
            </tr>`;
            select += `<option value="${g.magv}">${g.hoten} (${g.magv})</option>`;
        });
        if(gvTable) gvTable.innerHTML = html;
        if(gvSelect) gvSelect.innerHTML = select;
    });
}

// ===== HIỂN THỊ BẢNG CHI TIẾT PHÂN CÔNG =====
function loadAssign() {
    const pcTable = safeGet("pcTable");
    if (!pcTable) return;

    fetch("../php/giangVien.php?action=listAssign")
    .then(r => r.json())
    .then(data => {
        let html = "";
        data.forEach(p => {
            html += `
            <tr>
                <td>${p.hoten}</td>
                <td><span class="badge-lhp">LHP: ${p.malhp}</span></td>
                <td><b>${p.tenmon}</b></td>
                <td><i class="fas fa-users"></i> ${p.tenlop}</td>
                ${typeof IS_ADMIN !== 'undefined' && IS_ADMIN ? `
                <td>
                    <button class="btn-delete" onclick="deleteAssign('${p.id}')">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>` : ""}
            </tr>`;
        });
        pcTable.innerHTML = html || "<tr><td colspan='5'>Chưa có dữ liệu phân công</td></tr>";
    })
    .catch(err => console.error("Lỗi tải phân công:", err));
}

// ===== TẢI CHECKBOX LỚP HỌC PHẦN (ADMIN) =====
function loadLHPCheckboxes() {
    const container = safeGet("lhpList");
    if (!container) return;

    fetch("../php/giangVien.php?action=listLHP")
    .then(r => r.json())
    .then(data => {
        let html = "";
        data.forEach(l => {
            html += `
            <div class="class-item">
                <input type="checkbox" name="malhp_checkbox" value="${l.malhp}" id="l_${l.malhp}">
                <label for="l_${l.malhp}">Lớp <b>${l.malhp}</b> - ${l.tenmon}</label>
            </div>`;
        });
        container.innerHTML = html || "Không có lớp.";
    });
}

// Các hàm addGV, deleteAssign... giữ nguyên logic nhưng nên dùng safeGet
function deleteAssign(id) {
    if(!confirm("Xóa phân công này?")) return;
    let f = new FormData();
    f.append("action", "deleteAssign");
    f.append("id", id);
    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") loadAssign();
        else alert(data.message);
    });
}

// Khởi chạy
window.onload = loadAll;