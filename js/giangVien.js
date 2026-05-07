// ===== ELEMENT SELECTORS =====
const gvTable = document.getElementById("gvTable");
const pcTable = document.getElementById("pcTable");
const gvSelect = document.getElementById("gvSelect");
const monSelect = document.getElementById("monSelect");
const btnSubmit = document.getElementById("btnSubmit");
const btnAssign = document.getElementById("btnAssign");
const formGV = document.getElementById("formGV");
const formAssign = document.getElementById("formAssign"); // Cần thêm ID này vào thẻ form phân công

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
        let html = "", select = '<option value="">-- Chọn giảng viên --</option>';
        data.forEach(g => {
            html += `
            <tr>
                <td>${g.magv}</td>
                <td>${g.hoten}</td>
                <td>${g.email}</td>
                <td>${g.sodienthoai}</td>
                <td>${g.chuyennganh}</td>
                <td>
                    <button class="btn-edit" onclick="editGV('${g.magv}','${g.hoten}','${g.email}','${g.sodienthoai}','${g.chuyennganh}')">Sửa</button>
                    <button class="btn-delete" onclick="deleteGV('${g.magv}')">Xóa</button>
                </td>
            </tr>`;
            select += `<option value="${g.magv}">${g.hoten} (${g.magv})</option>`;
        });
        gvTable.innerHTML = html;
        gvSelect.innerHTML = select;
    });
}

// ===== THÊM / CẬP NHẬT GIẢNG VIÊN =====
function addGV() {
    let action = btnSubmit.innerText === "Cập nhật" ? "update" : "add";
    let f = new FormData();
    f.append("action", action);
    f.append("magv", document.getElementById("magv").value);
    f.append("hoten", document.getElementById("hoten").value);
    f.append("email", document.getElementById("email").value);
    f.append("sodienthoai", document.getElementById("sdt").value);
    f.append("chuyennganh", document.getElementById("chuyennganh").value);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") {
            alert(action === "add" ? "Thêm thành công!" : "Cập nhật thành công!");
            resetFormGV();
            loadGV();
        } else alert("Lỗi: " + data.error);
    });
}

function editGV(magvV, hotenV, emailV, sdtV, cnV) {
    document.getElementById("magv").value = magvV;
    document.getElementById("hoten").value = hotenV;
    document.getElementById("email").value = emailV;
    document.getElementById("sdt").value = sdtV;
    document.getElementById("chuyennganh").value = cnV;

    document.getElementById("magv").disabled = true; // Không cho sửa Mã GV
    btnSubmit.innerText = "Cập nhật";
}

function resetFormGV() {
    formGV.reset();
    document.getElementById("magv").disabled = false;
    btnSubmit.innerText = "Thêm giảng viên";
}

// ===== XÓA GIẢNG VIÊN =====
function deleteGV(id) {
    if(!confirm("Xóa giảng viên này sẽ xóa tất cả phân công liên quan. Tiếp tục?")) return;
    let f = new FormData();
    f.append("action", "delete");
    f.append("magv", id);
    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(() => {
        loadGV();
        loadAssign();
    });
}

// ===== QUẢN LÝ PHÂN CÔNG =====
function loadMon() {
    fetch("../php/giangVien.php?action=listMon")
    .then(r => r.json())
    .then(data => {
        let html = '<option value="">-- Chọn môn học --</option>';
        data.forEach(m => {
            html += `<option value="${m.mamon}">${m.tenmon}</option>`;
        });
        monSelect.innerHTML = html;
    });
}

// Hàm này dùng chung cho cả Thêm và Cập nhật Phân công để tránh lỗi sự kiện
function handleAssign() {
    let action = btnAssign.innerText.includes("Cập nhật") ? "updateAssign" : "assign";
    let f = new FormData();
    f.append("action", action);
    if(action === "updateAssign") f.append("id", btnAssign.getAttribute("data-id"));
    
    f.append("magv", gvSelect.value);
    f.append("mamon", monSelect.value);

    fetch("../php/giangVien.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if(data.status === "success") {
            alert("Thành công!");
            btnAssign.innerText = "Xác nhận phân công";
            gvSelect.value = "";
            monSelect.value = "";
            loadAssign();
        } else alert("Lỗi: " + data.error);
    });
}

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
                    <button class="btn-edit" onclick="prepareEditAssign('${p.id}', '${p.magv}', '${p.mamon}')">Sửa</button>
                    <button class="btn-delete" onclick="deleteAssign('${p.id}')">Xóa</button>
                </td>
            </tr>`;
        });
        pcTable.innerHTML = html;
    });
}

function prepareEditAssign(id, magv, mamon) {
    gvSelect.value = magv;
    monSelect.value = mamon;
    btnAssign.innerText = "Cập nhật phân công";
    btnAssign.setAttribute("data-id", id); // Lưu ID vào attribute để dùng khi cập nhật
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
    });
}

window.onload = loadAll;