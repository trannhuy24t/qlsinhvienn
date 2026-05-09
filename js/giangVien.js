// ===== KIỂM TRA PHẦN TỬ TRƯỚC KHI SỬ DỤNG =====
const safeGet = (id) => document.getElementById(id);

// Biến lưu trạng thái đang sửa (mode)
let isEditMode = false;

// ===== LOAD ALL DATA =====
function loadAll() {
    loadAssign();
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
            // Dùng JSON.stringify để bọc dữ liệu tránh lỗi dấu cách khi truyền vào onclick
            const gData = JSON.stringify(g).replace(/"/g, '&quot;');
            
            html += `
            <tr>
                <td>${g.magv}</td>
                <td>${g.hoten}</td>
                ${typeof IS_ADMIN !== 'undefined' && IS_ADMIN ? `
                <td>
                    <button class="btn-edit" onclick="editGV('${g.magv}', '${g.hoten}')">Sửa</button>
                    <button class="btn-delete" onclick="deleteGV('${g.magv}')">Xóa</button>
                </td>` : ""}
            </tr>`;
            select += `<option value="${g.magv}">${g.hoten} (${g.magv})</option>`;
        });
        if(gvTable) gvTable.innerHTML = html;
        if(gvSelect) gvSelect.innerHTML = select;
    });
}

// ===== HÀM SỬA (Đưa dữ liệu lên Form) =====
function editGV(magv, hoten) {
    if(!safeGet("magv") || !safeGet("hoten")) return;

    // Điền dữ liệu vào các ô input
    safeGet("magv").value = magv;
    safeGet("hoten").value = hoten;
    
    // Khóa ô Mã GV (vì mã là khóa chính không nên sửa)
    safeGet("magv").readOnly = true;
    safeGet("magv").style.backgroundColor = "#eee";

    // Đổi trạng thái nút bấm
    isEditMode = true;
    const btn = document.querySelector("#formGV button[type='submit']");
    if(btn) {
        btn.innerText = "Cập nhật thông tin";
        btn.style.background = "linear-gradient(135deg, #28a745, #218838)";
    }
}

// ===== HÀM THÊM HOẶC CẬP NHẬT GIẢNG VIÊN =====
function addGV() {
    const magv = document.getElementById("magv").value;
    const hoten = document.getElementById("hoten").value;

    if (!magv || !hoten) {
        alert("Vui lòng nhập đầy đủ Mã và Họ tên!");
        return;
    }

    let f = new FormData();
    f.append("action", "add"); // Dùng action 'add' kết hợp với ON DUPLICATE KEY trong PHP
    f.append("magv", magv);
    f.append("hoten", hoten);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if (data.status === "success") {
            alert(isEditMode ? "Cập nhật giảng viên thành công!" : "Thêm giảng viên mới thành công!");
            resetFormGV(); // Hàm này cực kỳ quan trọng để reset trạng thái
            loadGV();      // Tải lại bảng danh sách
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => alert("Lỗi kết nối Server!"));
}

// Reset form về trạng thái Thêm mới
function resetFormGV() {
    isEditMode = false;
    const f = document.getElementById("formGV");
    if(f) f.reset();
    
    const inputMa = document.getElementById("magv");
    inputMa.readOnly = false;
    inputMa.style.backgroundColor = "#fff";
    
    const btn = document.querySelector("#formGV button[type='submit']");
    if(btn) {
        btn.innerText = "Thêm giảng viên";
        btn.style.background = "linear-gradient(135deg, #007bff, #0056b3)";
    }
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

// ===== PHÂN CÔNG HÀNG LOẠT =====
function assignMultiple() {
    const magv = safeGet("gvSelect").value;
    const checkboxes = document.querySelectorAll('input[name="malhp_checkbox"]:checked');
    
    if(!magv) { alert("Vui lòng chọn giảng viên!"); return; }
    if(checkboxes.length === 0) { alert("Vui lòng chọn ít nhất 1 lớp!"); return; }

    let f = new FormData();
    f.append("action", "assign_multiple");
    f.append("magv", magv);
    checkboxes.forEach(cb => f.append("lhps[]", cb.value));

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") {
            alert("Phân công thành công!");
            loadAssign();
        } else alert("Lỗi phân công");
    });
}

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