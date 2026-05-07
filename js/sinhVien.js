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

let listSinhVien = []; // Biến lưu trữ dữ liệu tạm thời

function loadData() {
    fetch("../php/sinhVien.php")
        .then(res => res.json())
        .then(data => {
            listSinhVien = data; // Lưu dữ liệu vào biến toàn cục
            render(data);
        })
        .catch(err => console.error("Lỗi loadData:", err));
}

function render(data) {
    let html = "";
    if(!Array.isArray(data)) return; 

    data.forEach(sv => {
        html += `
        <tr>
            <td>${sv.masv}</td>
            <td>${sv.hoten}</td>
            <td>${sv.malop}</td>
            <td>${sv.gioitinh || "Không có"}</td>
            <td>${sv.tenlop || "Chưa cập nhật"}</td>
            <td>
                ${sv.anh ? `<img src="../images/${sv.anh}" width="50" style="border-radius:4px">` : "None"}
            </td>
            <td>
                <div style="display: flex; gap: 5px; justify-content: center;">
                    <button onclick="editSV('${sv.masv}')" class="btn-edit">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button onclick="deleteSV('${sv.masv}')" class="btn-delete">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
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
function editSV(id) {
    // 1. Tìm sinh viên trong danh sách dựa trên mã SV
    const sv = listSinhVien.find(item => item.masv === id);
    
    if (sv) {
        // 2. Đổ dữ liệu lên các ô input
        masv.value = sv.masv;
        hoten.value = sv.hoten;
        ngaysinh.value = sv.ngaysinh;
        gioitinh.value = sv.gioitinh;
        email.value = sv.email;
        sodienthoai.value = sv.sodienthoai;
        diachi.value = sv.diachi;
        malop.value = sv.malop;

        // Khóa mã SV không cho sửa vì là khóa chính
        masv.readOnly = true;
        masv.style.backgroundColor = "#f0f0f0";

        // 3. THAY ĐỔI NÚT THÀNH CẬP NHẬT
        const btnSubmit = document.getElementById("btn-submit");
        if (btnSubmit) {
            btnSubmit.innerText = "Cập nhật"; // Đổi chữ trên nút
            btnSubmit.style.backgroundColor = "#ffc107"; // Đổi màu sang vàng (tùy chọn)
            
            // Đổi hàm khi nhấn nút từ add() sang updateSV()
            btnSubmit.setAttribute("onclick", "updateSV()");
        }

        // Cuộn lên đầu trang để người dùng thực hiện sửa
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
function updateSV() {
    const formData = new FormData();
    formData.append("action", "update"); // Hành động update
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
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Cập nhật thành công!");
            const btnSubmit = document.getElementById("btn-submit");
            btnSubmit.innerText = "Thêm sinh viên";
            btnSubmit.style.backgroundColor = "#28a745"; // Trả về màu xanh ban đầu
            btnSubmit.setAttribute("onclick", "add()");
            
            // Mở khóa lại ô Mã SV
            masv.readOnly = false;
            masv.style.backgroundColor = "white";

            loadData(); // Load lại bảng
            document.getElementById("formsinhvien").reset();
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => console.error("Lỗi update:", err));
}

// Hàm bổ trợ để đưa Form về trạng thái ban đầu
function resetForm() {
    const form = document.getElementById("formsinhvien");
    form.reset();
    form.onsubmit = function(e) {
        e.preventDefault();
        add(); // Quay lại hàm add ban đầu
    };
    
    masv.readOnly = false;
    masv.style.backgroundColor = "white";
    
    const btnAction = document.querySelector(".btn-save");
    if (btnAction) {
        btnAction.innerText = "Thêm sinh viên";
        btnAction.className = "btn-save";
    }
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