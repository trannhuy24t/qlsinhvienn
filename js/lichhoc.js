// ===== KHỞI TẠO =====
const formLich = document.getElementById("formLichHoc");
let listData = [];
let isUpdate = false;

// Các trường input (Dành cho Admin sửa)
const inputs = ['malich','mamon','magv','malop','phonghoc','thu','tietbatdau','tietketthuc','ngayhoc','giobatdau','gioketthuc','ghichu'];

// =========================
// TẢI DỮ LIỆU
// =========================
function loadData() {
    fetch("../php/lichhoc.php?action=load")
    .then(res => res.json())
    .then(data => {
        // KIỂM TRA LỖI TỪ PHP (Ví dụ: Session rỗng)
        if (data.status === "error") {
            console.error("Lỗi hệ thống:", data.message);
            document.getElementById("table").innerHTML = `<tr><td colspan="12" style="color:red; font-weight:bold; padding:20px;">${data.message}</td></tr>`;
            return;
        }

        listData = data;
        render(data);
    })
    .catch(err => {
        console.error("Lỗi load dữ liệu:", err);
        document.getElementById("table").innerHTML = `<tr><td colspan="12">Không thể kết nối máy chủ.</td></tr>`;
    });
}

// =========================
// HIỂN THỊ DỮ LIỆU
// =========================
function render(data) {
    let html = "";
    if (!data || data.length === 0) {
        html = `<tr><td colspan="12">Không có lịch học nào cho lớp của bạn.</td></tr>`;
    } else {
        data.forEach(item => {
            html += `
            <tr>
                <td>${item.malich}</td>
                <td>${item.mamon}</td>
                <td>${item.magv}</td>
                <td>${item.malop}</td>
                <td>${item.phonghoc}</td>
                <td>${item.thu}</td>
                <td>${item.tietbatdau}</td>
                <td>${item.tietketthuc}</td>
                <td>${item.ngayhoc}</td>
                <td>${item.giobatdau} - ${item.gioketthuc}</td>
                <td>${item.ghichu}</td>
                ${typeof ROLE !== 'undefined' && ROLE === "admin" ? `
                <td>
                    <button class="btn-edit" onclick="editData('${item.malich}')">Sửa</button>
                    <button class="btn-delete" onclick="deleteData('${item.malich}')">Xóa</button>
                </td>` : ""}
            </tr>`;
        });
    }
    document.getElementById("table").innerHTML = html;
}

// =========================
// THÊM / CẬP NHẬT (CHỈ ADMIN)
// =========================
if (formLich) {
    formLich.addEventListener("submit", e => {
        e.preventDefault();
        const formData = new FormData(formLich);
        formData.append("action", isUpdate ? "update" : "add");

        fetch("../php/lichhoc.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success") {
                alert(isUpdate ? "Cập nhật thành công" : "Thêm thành công");
                resetForm();
                loadData();
            } else {
                alert("Lỗi: " + res.message);
            }
        });
    });
}

// =========================
// CHUẨN BỊ SỬA
// =========================
function editData(id) {
    const item = listData.find(x => x.malich === id);
    if (!item) return;

    inputs.forEach(key => {
        const el = document.getElementById(key);
        if (el) el.value = item[key];
    });

    isUpdate = true;
    const btn = document.querySelector("#formLichHoc button");
    if (btn) btn.textContent = "Cập nhật lịch";
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// =========================
// XÓA
// =========================
function deleteData(id) {
    if (!confirm("Bạn có chắc muốn xóa lịch này?")) return;

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("malich", id);

    fetch("../php/lichhoc.php", { method: "POST", body: formData })
    .then(res => res.json())
    .then(res => {
        if (res.status === "success") loadData();
        else alert(res.message);
    });
}

function resetForm() {
    if (formLich) formLich.reset();
    isUpdate = false;
    const btn = document.querySelector("#formLichHoc button");
    if (btn) btn.textContent = "Thêm lịch";
}

// Chạy khi trang tải xong
window.onload = loadData;