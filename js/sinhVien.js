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

// search
const s_masv = document.getElementById("s_masv");
const s_hoten = document.getElementById("s_hoten");
const s_malop = document.getElementById("s_malop");
const s_gioitinh = document.getElementById("s_gioitinh");
const from = document.getElementById("from");
const to = document.getElementById("to");

// ===== LOAD DATA =====
function loadData() {
    fetch("../php/sinhVien.php")
        .then(res => res.json())
        .then(data => render(data))
        .catch(err => {
            console.error("Lỗi loadData:", err);
        });
}

// ===== RENDER =====
function render(data) {
    let html = "";
    if(!Array.isArray(data)) return; // Tránh lỗi nếu data không phải mảng

    data.forEach(sv => {
        html += `
        <tr>
            <td>${sv.masv}</td>
            <td>${sv.hoten}</td>
            <td>${sv.malop}</td>
            <td>
                ${sv.anh ? `<img src="../images/${sv.anh}" width="50">` : "Không có"}
            </td>
            <td>
                <button onclick="deleteSV('${sv.masv}')" style="color:red">Xóa</button>
            </td>
        </tr>`;
    });
    document.getElementById("table").innerHTML = html;
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
    .then(async res => {
        const text = await res.text(); // Đọc dưới dạng text trước để tránh lỗi JSON
        try {
            return JSON.parse(text); // Thử chuyển sang JSON
        } catch (e) {
            // Nếu lỗi, in thẳng nội dung server trả về ra console để xem lỗi PHP
            console.error("Server trả về lỗi thực tế:", text);
            throw new Error("Server trả về dữ liệu không hợp lệ (xem trong Console)");
        }
    })
    .then(data => {
        if (data.status === "success") {
            alert("Thêm thành công!");
            loadData();
            document.getElementById("formsinhvien").reset(); // Xóa sạch form
        } else {
            alert("Lỗi SQL: " + data.message);
        }
    })
    .catch(err => {
        console.error("Lỗi add:", err);
        alert(err.message);
    });
}

// ===== DELETE =====
function deleteSV(id) {
    if (!confirm("Bạn có chắc muốn xóa sinh viên này?")) return;

    // Log ra để kiểm tra id có tồn tại không trước khi gửi
    console.log("Đang xóa sinh viên có mã:", id);

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("masv", id);

    fetch("../php/sinhVien.php", {
        method: "POST",
        body: formData
        // Không set Content-Type thủ công khi dùng FormData nhé
    })
    .then(res => res.text()) // Đọc text trước để check lỗi PHP (nếu có)
    .then(text => {
        console.log("Server phản hồi:", text); // Xem server trả về success hay error
        try {
            const data = JSON.parse(text);
            if (data.status === "success") {
                alert("Xóa thành công!");
                loadData();
            } else {
                alert("Lỗi server: " + data.message);
            }
        } catch (e) {
            alert("Lỗi định dạng phản hồi từ server!");
        }
    })
    .catch(err => console.error("Lỗi Fetch:", err));
}

// ===== SEARCH =====
function search() {
    const formData = new FormData();
    formData.append("action", "search");
    formData.append("masv", s_masv.value.trim());
    formData.append("hoten", s_hoten.value.trim());
    formData.append("malop", s_malop.value.trim());
    formData.append("gioitinh", s_gioitinh.value);
    formData.append("from", from.value);
    formData.append("to", to.value);

    fetch("../php/sinhVien.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => render(data))
    .catch(err => console.error(err));
}

// ===== LOAD =====
window.onload = loadData;