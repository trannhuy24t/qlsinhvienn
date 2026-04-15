// ===== ELEMENT SELECTORS =====
// Đảm bảo ID trong HTML khớp với các biến này
const gvTable = document.getElementById("gvTable");
const pcTable = document.getElementById("pcTable");
const gvSelect = document.getElementById("gvSelect");
const monSelect = document.getElementById("monSelect");
const btnSubmit = document.getElementById("btnSubmit");
const btnAssign = document.getElementById("btnAssign"); // Nút bấm phần phân công
const formGV = document.getElementById("formGV");

// ===== LOAD ALL DATA =====
function loadAll() {
    loadGV();
    loadMon();
    loadAssign();
}

// ===== LOAD GIẢNG VIÊN =====
function loadGV() {
    fetch("../php/giangVien.php?action=listGV")
    .then(r => r.json())
    .then(data => {
        let html = "", select = '<option value="">Chọn giảng viên</option>';

        data.forEach(g => {
            html += `
            <tr>
                <td>${g.magv}</td>
                <td>${g.hoten}</td>
                <td>${g.email}</td>
                <td>${g.sodienthoai}</td>
                <td>${g.chuyennganh}</td>
                <td>
                    <button onclick="editGV('${g.magv}','${g.hoten}','${g.email}','${g.sodienthoai}','${g.chuyennganh}')">Sửa</button>
                    <button onclick="deleteGV('${g.magv}')">Xóa</button>
                </td>
            </tr>`;

            select += `<option value="${g.magv}">${g.hoten}</option>`;
        });

        gvTable.innerHTML = html;
        gvSelect.innerHTML = select;
    });
}

// ===== THÊM GIẢNG VIÊN =====
function addGV() {
    let f = new FormData();
    f.append("action", "add");
    f.append("magv", document.getElementById("magv").value);
    f.append("hoten", document.getElementById("hoten").value);
    f.append("email", document.getElementById("email").value);
    f.append("sodienthoai", document.getElementById("sdt").value);
    f.append("chuyennganh", document.getElementById("chuyennganh").value);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") {
            formGV.reset();
            loadGV();
        } else alert("Lỗi: " + data.error);
    });
}

// ===== EDIT GIẢNG VIÊN (CHUẨN BỊ FORM) =====
function editGV(magvV, hotenV, emailV, sdtV, cnV) {
    document.getElementById("magv").value = magvV;
    document.getElementById("hoten").value = hotenV;
    document.getElementById("email").value = emailV;
    document.getElementById("sdt").value = sdtV;
    document.getElementById("chuyennganh").value = cnV;

    document.getElementById("magv").disabled = true;
    btnSubmit.innerText = "Cập nhật";

    formGV.onsubmit = function() {
        updateGV();
        return false;
    };
}

// ===== UPDATE GIẢNG VIÊN =====
function updateGV() {
    let f = new FormData();
    f.append("action", "update");
    f.append("magv", document.getElementById("magv").value);
    f.append("hoten", document.getElementById("hoten").value);
    f.append("email", document.getElementById("email").value);
    f.append("sodienthoai", document.getElementById("sdt").value);
    f.append("chuyennganh", document.getElementById("chuyennganh").value);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") {
            alert("Cập nhật thành công!");
            document.getElementById("magv").disabled = false;
            btnSubmit.innerText = "Thêm";
            formGV.reset();
            formGV.onsubmit = function() { addGV(); return false; };
            loadGV();
        }
    });
}

// ===== DELETE GIẢNG VIÊN (XÓA CẢ HỒ SƠ) =====
function deleteGV(id) {
    if(!confirm("Xóa giảng viên này sẽ xóa tất cả phân công liên quan. Tiếp tục?")) return;

    let f = new FormData();
    f.append("action", "delete");
    f.append("magv", id);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(() => {
        loadGV();
        loadAssign();
    });
}

// ===== LOAD MÔN HỌC (DROPDOWN) =====
function loadMon() {
    fetch("../php/giangVien.php?action=listMon")
    .then(r => r.json())
    .then(data => {
        let html = '<option value="">Chọn môn học</option>';
        data.forEach(m => {
            html += `<option value="${m.mamon}">${m.tenmon}</option>`;
        });
        monSelect.innerHTML = html;
    });
}

// ===== PHÂN CÔNG MỚI =====
function assign() {
    let f = new FormData();
    f.append("action", "assign");
    f.append("magv", gvSelect.value);
    f.append("mamon", monSelect.value);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") {
            alert("Phân công thành công!");

            // 🔥 RESET SELECT
            gvSelect.value = "";
            monSelect.value = "";

            loadAssign();
        } else {
            alert("Lỗi: " + data.error);
        }
    });
}

// ===== LOAD DANH SÁCH PHÂN CÔNG =====
function loadAssign() {
    fetch("../php/giangVien.php?action=listAssign")
    .then(r => r.json())
    .then(data => {
        let html = "";
        data.forEach(p => {
            html += `
            <tr>
                <td>${p.hoten}</td>
                <td>${p.chuyennganh}</td>
                <td>${p.tenmon}</td>
                <td>
                    <button onclick="prepareEditAssign('${p.id}', '${p.magv}', '${p.mamon}')" style="background:orange; color:white">Sửa</button>
                    <button onclick="deleteAssign('${p.id}')" style="background:red; color:white">Xóa</button>
                </td>
            </tr>`;
        });
        pcTable.innerHTML = html;
    });
}

// ===== CHUẨN BỊ SỬA PHÂN CÔNG =====
function prepareEditAssign(id, magv, mamon) {
    gvSelect.value = magv;
    monSelect.value = mamon;
    
    btnAssign.innerText = "Cập nhật phân công";
    btnAssign.onclick = function() {
        updateAssign(id);
    };
}

// ===== UPDATE PHÂN CÔNG (GỬI LÊN SERVER) =====
function updateAssign(id) {
    let f = new FormData();
    f.append("action", "updateAssign");
    f.append("id", id);
    f.append("magv", gvSelect.value);
    f.append("mamon", monSelect.value);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") {
            alert("Cập nhật phân công thành công!");
            btnAssign.innerText = "Phân công";
            btnAssign.onclick = assign; // Trả lại sự kiện thêm mới
            gvSelect.value = "";
            monSelect.value = "";
            loadAssign();
        }
    });
}

// ===== XÓA PHÂN CÔNG (RIÊNG BIỆT) =====
function deleteAssign(id) {
    if(!confirm("Xóa phân công này? (Hồ sơ giảng viên vẫn được giữ lại)")) return;

    let f = new FormData();
    f.append("action", "deleteAssign");
    f.append("id", id);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") loadAssign();
        else alert("Lỗi: " + data.error);
    });
}

window.onload = loadAll;