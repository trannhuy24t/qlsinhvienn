// ===== LẤY ELEMENT =====
const masv = document.getElementById("masv");
const hoten = document.getElementById("hoten");
const ngaysinh = document.getElementById("ngaysinh");
const gioitinh = document.getElementById("gioitinh");
const email = document.getElementById("email");
const sodienthoai = document.getElementById("sodienthoai");
const diachi = document.getElementById("diachi");
const malop = document.getElementById("malop");
const anh = document.getElementById("anh");

// ===== SEARCH =====
const s_masv = document.getElementById("s_masv");
const s_hoten = document.getElementById("s_hoten");
const s_malop = document.getElementById("s_malop");
const s_gioitinh = document.getElementById("s_gioitinh");

let listSinhVien = [];

// ===== LOAD DATA =====
function loadData() {

    fetch("../php/sinhVien.php?action=load")

    .then(async (res) => {

        const text = await res.text();

        try {

            return JSON.parse(text);

        } catch (e) {

            console.error("PHP ERROR:", text);

            throw new Error("Dữ liệu JSON không hợp lệ");
        }

    })

    .then((data) => {

        console.log("DATA:", data);

        if (!Array.isArray(data)) {

            console.error("Không phải array:", data);

            return;
        }

        listSinhVien = data;

        render(data);

    })

    .catch((err) => {

        console.error("Lỗi loadData:", err);

    });
}

// ===== RENDER =====
function render(data) {

    const table = document.getElementById("table");

    let html = "";

    if (!Array.isArray(data)) {

        table.innerHTML = `
            <tr>
                <td colspan="7">Dữ liệu không hợp lệ</td>
            </tr>
        `;

        return;
    }

    if (data.length === 0) {

        table.innerHTML = `
            <tr>
                <td colspan="7">Không có dữ liệu</td>
            </tr>
        `;

        return;
    }

    data.forEach((sv) => {

        html += `
            <tr>

                <td>${sv.masv || ""}</td>

                <td>${sv.hoten || ""}</td>

                <td>${sv.malop || ""}</td>

                <td>${sv.tenlop || ""}</td>

                <td>${sv.gioitinh || ""}</td>

                <td>
                    ${
                        sv.anh
                        ?
                        `<img src="../images/${sv.anh}"
                              width="50"
                              height="50"
                              style="object-fit:cover;border-radius:6px;">`
                        :
                        "Không có"
                    }
                </td>

                <td>

                    <button
                        onclick="editSV('${sv.masv}')"
                        class="btn-edit">

                        <i class="fas fa-edit"></i>
                        Sửa

                    </button>

                    <button
                        onclick="deleteSV('${sv.masv}')"
                        class="btn-delete">

                        <i class="fas fa-trash"></i>
                        Xóa

                    </button>

                </td>

            </tr>
        `;
    });

    table.innerHTML = html;
}

// ===== ADD =====
function add() {

    const formData = new FormData();

    formData.append("action", "add");
    formData.append("masv", masv.value.trim());
    formData.append("hoten", hoten.value.trim());
    formData.append("ngaysinh", ngaysinh.value);
    formData.append("gioitinh", gioitinh.value);
    formData.append("email", email.value.trim());
    formData.append("sodienthoai", sodienthoai.value.trim());
    formData.append("diachi", diachi.value.trim());
    formData.append("malop", malop.value.trim());

    if (anh.files.length > 0) {

        formData.append("anh", anh.files[0]);
    }

    fetch("../php/sinhVien.php", {

        method: "POST",
        body: formData

    })

    .then(async (res) => {

        const text = await res.text();

        try {

            return JSON.parse(text);

        } catch (e) {

            console.error("ADD ERROR:", text);

            throw new Error("Lỗi JSON");
        }

    })

    .then((data) => {

        if (data.status === "success") {

            alert("Thêm thành công");

            resetForm();

            loadData();

        } else {

            alert(data.message || "Lỗi thêm");
        }

    })

    .catch((err) => {

        console.error("Lỗi add:", err);

    });
}

// ===== DELETE =====
function deleteSV(id) {

    if (!confirm("Bạn có chắc muốn xóa?")) return;

    const formData = new FormData();

    formData.append("action", "delete");
    formData.append("masv", id);

    fetch("../php/sinhVien.php", {

        method: "POST",
        body: formData

    })

    .then(async (res) => {

        const text = await res.text();

        try {

            return JSON.parse(text);

        } catch (e) {

            console.error("DELETE ERROR:", text);

            throw new Error("Lỗi JSON");
        }

    })

    .then((data) => {

        if (data.status === "success") {

            alert("Xóa thành công");

            loadData();

        } else {

            alert(data.message || "Lỗi xóa");
        }

    })

    .catch((err) => {

        console.error("Lỗi delete:", err);

    });
}

// ===== EDIT =====
function editSV(id) {

    const sv = listSinhVien.find(item => item.masv == id);

    if (!sv) return;

    masv.value = sv.masv || "";
    hoten.value = sv.hoten || "";
    ngaysinh.value = sv.ngaysinh || "";
    gioitinh.value = sv.gioitinh || "";
    email.value = sv.email || "";
    sodienthoai.value = sv.sodienthoai || "";
    diachi.value = sv.diachi || "";
    malop.value = sv.malop || "";

    masv.readOnly = true;

    const btn = document.getElementById("btn-submit");

    btn.innerHTML = "Cập nhật";

    btn.onclick = function (e) {

        e.preventDefault();

        updateSV();
    };
}

// ===== UPDATE =====
function updateSV() {

    const formData = new FormData();

    formData.append("action", "update");
    formData.append("masv", masv.value.trim());
    formData.append("hoten", hoten.value.trim());
    formData.append("ngaysinh", ngaysinh.value);
    formData.append("gioitinh", gioitinh.value);
    formData.append("email", email.value.trim());
    formData.append("sodienthoai", sodienthoai.value.trim());
    formData.append("diachi", diachi.value.trim());
    formData.append("malop", malop.value.trim());

    if (anh.files.length > 0) {

        formData.append("anh", anh.files[0]);
    }

    fetch("../php/sinhVien.php", {

        method: "POST",
        body: formData

    })

    .then(async (res) => {

        const text = await res.text();

        try {

            return JSON.parse(text);

        } catch (e) {

            console.error("UPDATE ERROR:", text);

            throw new Error("Lỗi JSON");
        }

    })

    .then((data) => {

        if (data.status === "success") {

            alert("Cập nhật thành công");

            resetForm();

            loadData();

        } else {

            alert(data.message || "Lỗi cập nhật");
        }

    })

    .catch((err) => {

        console.error("Lỗi update:", err);

    });
}

// ===== RESET FORM =====
function resetForm() {

    document.getElementById("formsinhvien").reset();

    masv.readOnly = false;

    const btn = document.getElementById("btn-submit");

    btn.innerHTML = "Thêm sinh viên";

    btn.onclick = function (e) {

        e.preventDefault();

        add();
    };
}

// ===== SEARCH =====
function search() {

    const formData = new FormData();

    formData.append("action", "search");
    formData.append("masv", s_masv.value.trim());
    formData.append("hoten", s_hoten.value.trim());
    formData.append("malop", s_malop.value.trim());
    formData.append("gioitinh", s_gioitinh.value);

    fetch("../php/sinhVien.php", {

        method: "POST",
        body: formData

    })

    .then(async (res) => {

        const text = await res.text();

        try {

            return JSON.parse(text);

        } catch (e) {

            console.error("SEARCH ERROR:", text);

            throw new Error("Lỗi JSON");
        }

    })

    .then((data) => {

        render(data);

    })

    .catch((err) => {

        console.error("Lỗi search:", err);

    });
}

// ===== AUTO LOAD =====
window.onload = function () {

    loadData();

};