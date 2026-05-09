// ===== ELEMENT =====
const malop = document.getElementById("malop");
const tenlop = document.getElementById("tenlop");
const khoa = document.getElementById("khoa");

// ===== ROLE =====
const role =
    document.body.dataset.role || "sinhvien";

// ===== LOAD LỚP =====
function loadLop() {

    fetch("../php/lopHoc.php?action=list")

    .then(res => {

        if (!res.ok) {
            throw new Error("Không thể tải dữ liệu lớp");
        }

        return res.json();
    })

    .then(data => {

        render(data);

        if (role === "giangvien") {
            renderDropdown(data);
        }

    })

    .catch(err => {

        console.error("Lỗi load lớp:", err);

    });
}

// ===== RENDER TABLE =====
function render(data) {

    const table =
        document.getElementById("table");

    if (!table) return;

    let html = "";

    if (data.length === 0) {

        html = `
        <tr>
            <td colspan="4" style="text-align:center;">
                Chưa có dữ liệu lớp học
            </td>
        </tr>`;

    } else {

        data.forEach(l => {

            let actionHTML = "";

            if (role === "admin") {

                actionHTML = `
                    <button class="btn-edit"
                        onclick="editLop('${l.malop}','${l.tenlop}','${l.khoa}')">

                        <i class="fas fa-edit"></i>
                        Sửa
                    </button>

                    <button class="btn-delete"
                        onclick="deleteLop('${l.malop}')">

                        <i class="fas fa-trash"></i>
                        Xóa
                    </button>
                `;
            }

            html += `
            <tr>

                <td>${l.malop}</td>
                <td>${l.tenlop}</td>
                <td>${l.khoa}</td>

                ${role === "admin"
                    ? `<td>${actionHTML}</td>`
                    : ""}

            </tr>`;
        });
    }

    table.innerHTML = html;
}

// ===== ADD =====
function addLop() {

    if (role !== "admin") {
        alert("Bạn không có quyền");
        return;
    }

    const formData = new FormData();

    formData.append("action", "add");
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

            alert("Thêm lớp thành công");

            resetForm();
            loadLop();

        } else {

            alert(data.message);
        }
    });
}

// ===== DELETE =====
function deleteLop(malopValue) {

    if (role !== "admin") {
        return;
    }

    if (!confirm("Bạn chắc chắn muốn xóa?")) {
        return;
    }

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
    });
}

// ===== EDIT =====
function editLop(malopValue, tenlopValue, khoaValue) {

    if (role !== "admin") {
        return;
    }

    malop.value = malopValue;
    tenlop.value = tenlopValue;
    khoa.value = khoaValue;

    malop.disabled = true;

    document.getElementById("btnSubmit").innerText =
        "Cập nhật";

    document.getElementById("formLop").onsubmit = function () {

        updateLop();

        return false;
    };
}

// ===== UPDATE =====
function updateLop() {

    if (role !== "admin") {
        return;
    }

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

            alert("Cập nhật thành công");

            resetForm();
            loadLop();

        } else {

            alert(data.message);
        }
    });
}

// ===== RESET =====
function resetForm() {

    document.getElementById("formLop").reset();

    malop.disabled = false;

    document.getElementById("btnSubmit").innerText =
        "Thêm lớp";

    document.getElementById("formLop").onsubmit = function () {

        addLop();

        return false;
    };
}

// ===== DROPDOWN =====
function renderDropdown(data) {

    const select =
        document.getElementById("chonlop");

    if (!select) return;

    let html = `
    <option value="">
        -- Chọn lớp --
    </option>`;

    data.forEach(l => {

        html += `
        <option value="${l.malop}">
            ${l.tenlop}
        </option>`;
    });

    select.innerHTML = html;
}

// ===== LOAD SV =====
function loadSV() {

    const maLopSelected =
        document.getElementById("chonlop").value;

    fetch(`../php/lopHoc.php?action=listSV&malop=${maLopSelected}`)

    .then(res => res.json())

    .then(renderSV);
}

// ===== RENDER SV =====
function renderSV(data) {

    const svTable =
        document.getElementById("svTable");

    if (!svTable) return;

    let html = "";

    if (data.length === 0) {

        html = `
        <tr>
            <td colspan="2" style="text-align:center;">
                Không có sinh viên
            </td>
        </tr>`;

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
}

// ===== SINH VIÊN =====
function loadSVByCurrentClass() {

    fetch(`../php/lopHoc.php?action=listSV&malop=${USER_MALOP}`)

    .then(res => res.json())

    .then(renderSV)

    .catch(err => {

        console.error(err);

    });
}

// ===== INIT =====
window.onload = function () {

    if (role === "sinhvien") {

        loadSVByCurrentClass();

    } else {

        loadLop();
    }
};