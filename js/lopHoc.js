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
        html += `
        <tr>
            <td>${l.malop}</td>
            <td>${l.tenlop}</td>
            <td>${l.khoa}</td>
            <td>
                <button onclick="editLop('${l.malop}','${l.tenlop}','${l.khoa}')">Sửa</button>
                <button onclick="deleteLop('${l.malop}')">Xóa</button>
            </td>
        </tr>`;
    });

    document.getElementById("table").innerHTML = html;
}

// ===== ADD =====
function addLop() {
    const formData = new FormData();
    formData.append("action","add");
    formData.append("malop", malop.value.trim());
    formData.append("tenlop", tenlop.value.trim());
    formData.append("khoa", khoa.value.trim());

    fetch("../php/lopHoc.php", {
        method:"POST",
        body:formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Thêm lớp thành công!");
            document.querySelector("form").reset();
            loadLop();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error("Lỗi add:", err));
}

// ===== DELETE =====
function deleteLop(malopValue) {
    if (!confirm("Bạn có chắc muốn xóa lớp này?")) return;

    const formData = new FormData();
    formData.append("action","delete");
    formData.append("malop", malopValue);

    fetch("../php/lopHoc.php", {
        method:"POST",
        body:formData
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

    malop.disabled = true;

    document.getElementById("btnSubmit").innerText = "Cập nhật";

    document.getElementById("formLop").onsubmit = function () {
        updateLop();
        return false;
    };
}

// ===== UPDATE =====
function updateLop() {
    const formData = new FormData();

    formData.append("action","update");
    formData.append("malop", malop.value);
    formData.append("tenlop", tenlop.value);
    formData.append("khoa", khoa.value);

    fetch("../php/lopHoc.php", {
        method:"POST",
        body:formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Cập nhật thành công!");

            document.getElementById("formLop").reset();
            malop.disabled = false;

            document.getElementById("btnSubmit").innerText = "Thêm";

            document.getElementById("formLop").onsubmit = function () {
                addLop();
                return false;
            };

            loadLop();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error("Lỗi update:", err));
}

// ===== DROPDOWN =====
function renderDropdown(data) {
    let html = "<option value=''>Chọn lớp</option>";

    data.forEach(l => {
        html += `<option value="${l.malop}">${l.tenlop}</option>`;
    });

    document.getElementById("chonlop").innerHTML = html;
}

// ===== LOAD SINH VIÊN THEO LỚP =====
function loadSV() {
    const malopValue = document.getElementById("chonlop").value;

    fetch("../php/sinhVien.php?action=search&malop=" + malopValue)
    .then(res => res.json())
    .then(data => {
        let html = "";

        data.forEach(sv => {
            html += `
            <tr>
                <td>${sv.masv}</td>
                <td>${sv.hoten}</td>
            </tr>`;
        });

        document.getElementById("svTable").innerHTML = html;
    })
    .catch(err => console.error("Lỗi load SV:", err));
}

// ===== INIT =====
window.onload = loadLop;