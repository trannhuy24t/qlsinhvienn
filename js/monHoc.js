function loadAll(){
    loadMon();
    loadHK();
    loadLHP();
    loadLop();
}

function loadMon() {
    fetch("../php/monHoc.php?action=listMon")
    .then(r => r.json())
    .then(data => {
        let html = "", select = "";
        data.forEach(m => {
            html += `
            <tr>
                <td>${m.mamon}</td>
                <td>${m.tenmon}</td>
                <td>${m.sotinchi}</td>
                <td>${m.mota}</td>
                <td>
                    <button onclick="deleteMon('${m.mamon}')" style="color:red">Xóa</button>
                </td>
            </tr>`;
            select += `<option value="${m.mamon}">${m.tenmon}</option>`;
        });
        
        // Kiểm tra phần tử trước khi gán innerHTML
        if(document.getElementById('monTable')) {
            document.getElementById('monTable').innerHTML = html;
        }
        if(document.getElementById('mamonSelect')) {
            document.getElementById('mamonSelect').innerHTML = select;
        }
    });
}

function addMon() {
    // Kiểm tra dữ liệu đầu vào cơ bản
    if (!mamon.value || !tenmon.value) {
        alert("Vui lòng nhập đầy đủ mã và tên môn học!");
        return;
    }

    let f = new FormData();
    f.append("action", "addMon");
    f.append("mamon", mamon.value);
    f.append("tenmon", tenmon.value);
    f.append("sotinchi", sotinchi.value);
    f.append("mota", mota.value);

    fetch("../php/monHoc.php", { 
        method: "POST", 
        body: f 
    })
    .then(r => r.text()) // Nhận phản hồi văn bản từ PHP để kiểm tra lỗi nếu có
    .then(data => {
        console.log("Kết quả:", data);
        
        // Gọi lại hàm load danh sách để cập nhật bảng và select box
        loadMon(); 

        // Xóa trắng các ô nhập liệu sau khi thêm thành công
        mamon.value = "";
        tenmon.value = "";
        sotinchi.value = "";
        mota.value = "";
        
        alert("Thêm môn học thành công!");
    })
    .catch(err => {
        console.error("Lỗi:", err);
        alert("Có lỗi xảy ra khi thêm môn học.");
    });
}

function deleteMon(id){
    let f=new FormData();
    f.append("action","deleteMon");
    f.append("mamon",id);

    fetch("../php/monHoc.php",{method:"POST",body:f})
    .then(()=>loadMon());
}

// ===== HK =====
function loadHK(){
    fetch("../php/monHoc.php?action=listHK")
    .then(r=>r.json())
    .then(data=>{
        let html="";
        data.forEach(h=>{
            html+=`<option value="${h.mahocky}">
                ${h.tenhocky} - ${h.namhoc}
            </option>`;
        });
        hkSelect.innerHTML=html;
    });
}

function addHK() {
    let f = new FormData();
    f.append("action", "addHK");
    f.append("mahocky", mahocky.value);
    f.append("tenhocky", tenhocky.value);
    f.append("namhoc", namhoc.value);

    fetch("../php/monHoc.php", { method: "POST", body: f })
    .then(r => r.json()) // Chuyển phản hồi sang JSON
    .then(data => {
        if (data.status === "success") {
            alert("Thêm học kỳ thành công!");
            loadHK(); // Chỉ load lại khi thành công
            // Xóa sạch các ô input sau khi thêm
            mahocky.value = "";
            tenhocky.value = "";
            namhoc.value = "";
        } else {
            // Hiển thị thông báo lỗi (ví dụ: "Mã học kỳ này đã tồn tại!")
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => {
        console.error("Lỗi kết nối:", err);
    });
}

// ===== LHP =====
function loadLHP(){
    fetch("../php/monHoc.php?action=listLHP")
    .then(r => r.json())
    .then(data => {
        let html = "";
        data.forEach(l => {
            html += `
            <tr>
                <td>${l.malhp}</td>
                <td>${l.tenmon}</td>
                <td>${l.magv}</td>
                <td>${l.tenhocky}</td>
                <td>${l.namhoc}</td>
                <td><span class="badge-lop">${l.tenlop}</span></td> <!-- Thêm cột tên lớp -->
                <td>
                    <button class="btn-edit" 
                        onclick="editLHP('${l.malhp}', '${l.mamon}', '${l.magv}', '${l.mahocky}', '${l.malop}')">Sửa</button>
                    <button class="btn-delete" 
                        onclick="deleteLHP('${l.malhp}')">Xóa</button>
                </td>
            </tr>`;
        });
        if(document.getElementById("lhpTable")) {
            document.getElementById("lhpTable").innerHTML = html;
        }
    });
}

function addLHP(){
    // 1. Kiểm tra ID của select lớp là 'lopSelect'
    const lopElement = document.getElementById("lopSelect");
    
    if (!malhp.value || !mamonSelect.value || !magv.value || !hkSelect.value || !lopElement.value) {
        alert("Vui lòng nhập đầy đủ thông tin lớp học phần!");
        return;
    }

    let f = new FormData();
    f.append("action", "addLHP");
    f.append("malhp", document.getElementById("malhp").value);
    f.append("mamon", document.getElementById("mamonSelect").value);
    f.append("magv", document.getElementById("magv").value);
    f.append("mahocky", document.getElementById("hkSelect").value);
    f.append("malop", lopElement.value); // Gửi giá trị malop lên server

    fetch("../php/monHoc.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if (data.status === "success") {
            alert("Thêm lớp học phần thành công!");
            loadLHP(); 
            document.getElementById("formLHP").reset();
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => console.error("Lỗi:", err));
}

// Tương tự cho hàm editLHP để đổ lại dữ liệu lên form
function editLHP(ma, mamon, magv, mahk, malop) {
    document.getElementById("malhp").value = ma;
    document.getElementById("malhp").readOnly = true;
    document.getElementById("mamonSelect").value = mamon;
    document.getElementById("magv").value = magv;
    document.getElementById("hkSelect").value = mahk;
    
    // Đổ dữ liệu lớp vào select
    if(document.getElementById("malopSelect")) {
        document.getElementById("malopSelect").value = malop;
    }

    const btn = document.querySelector("#formLHP button");
    if(btn) {
        btn.innerText = "Cập nhật lớp";
        document.getElementById("formLHP").onsubmit = function() { updateLHP(); return false; };
    }
}
// --- PHẦN MÔN HỌC ---
// Ví dụ cho bảng môn học
function loadMon() {
    fetch("../php/monHoc.php?action=listMon")
    .then(r => r.json())
    .then(data => {
        let htmlTable = "";
        let htmlSelect = '<option value="">-- Chọn môn học --</option>'; // Thêm dòng mặc định
        
        data.forEach(m => {
            // Đổ vào bảng
            htmlTable += `
            <tr>
                <td>${m.mamon}</td>
                <td>${m.tenmon}</td>
                <td>${m.sotinchi}</td>
                <td>${m.mota}</td>
                <td>
                    <button class="btn-edit" onclick="editMon('${m.mamon}','${m.tenmon}','${m.sotinchi}','${m.mota}')">Sửa</button>
                    <button class="btn-delete" onclick="deleteMon('${m.mamon}')">Xóa</button>
                </td>
            </tr>`;
            
            // Đổ vào Select box ở phần Mở lớp học phần
            htmlSelect += `<option value="${m.mamon}">${m.tenmon}</option>`;
        });
        
        document.getElementById("monTable").innerHTML = htmlTable;
        document.getElementById("mamonSelect").innerHTML = htmlSelect; // Đảm bảo ID này đúng với thẻ <select> môn học
    });
}

function editMon(id, ten, tc, mt) {
    document.getElementById("mamon").value = id;
    document.getElementById("mamon").readOnly = true; // Không cho sửa mã
    document.getElementById("tenmon").value = ten;
    document.getElementById("sotinchi").value = tc;
    document.getElementById("mota").value = mt;
    
    // Đổi nút thành Cập nhật
    const btn = document.querySelector("#formMon button");
    btn.innerText = "Cập nhật";
    document.getElementById("formMon").onsubmit = function() { updateMon(); return false; };
}

function updateMon() {
    // 1. Thu thập dữ liệu từ Form
    let f = new FormData();
    f.append("action", "updateMon");
    f.append("mamon", document.getElementById("mamon").value);
    f.append("tenmon", document.getElementById("tenmon").value);
    f.append("sotinchi", document.getElementById("sotinchi").value);
    f.append("mota", document.getElementById("mota").value);

    // 2. Gửi dữ liệu đi
    fetch("../php/monHoc.php", {
        method: "POST", 
        body: f
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === "success") {
            alert("Cập nhật thành công!");
            // Reset form và cho phép nhập lại mã môn nếu cần
            document.getElementById("mamon").readOnly = false;
            document.getElementById("formMon").reset();
            // Đổi lại nút bấm từ "Cập nhật" về "Thêm"
            const btn = document.querySelector("#formMon button");
            if(btn) btn.innerText = "Thêm môn";
            
            loadMon(); // Tải lại bảng dữ liệu
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => console.error("Lỗi kết nối:", err));
}

// --- PHẦN HỌC KỲ ---
function loadHK(){
    fetch("../php/monHoc.php?action=listHK")
    .then(r=>r.json())
    .then(data=>{
        let select = '<option value="">-- Chọn học kỳ --</option>';
        let tableHtml = ""; // Biến chứa HTML cho bảng

        data.forEach(h=>{
            // Đổ vào Select box
            select += `<option value="${h.mahocky}">${h.tenhocky} - ${h.namhoc}</option>`;
            
            // Đổ vào Table (Thêm phần này)
            tableHtml += `
            <tr>
                <td>${h.mahocky}</td>
                <td>${h.tenhocky}</td>
                <td>${h.namhoc}</td>
                <td>
                    <button class="btn-edit" onclick="editHK('${h.mahocky}','${h.tenhocky}','${h.namhoc}')">Sửa</button>
                    <button class="btn-delete" onclick="deleteHK('${h.mahocky}')">Xóa</button>
                </td>
            </tr>`;
        });

        if(document.getElementById("hkSelect")) hkSelect.innerHTML = select;
        if(document.getElementById("hkTable")) document.getElementById("hkTable").innerHTML = tableHtml;
    });
}

// Hàm chuẩn bị sửa Học kỳ (gọi khi bạn có bảng danh sách HK)
function editHK(ma, ten, nam) {
    document.getElementById("mahocky").value = ma;
    document.getElementById("mahocky").readOnly = true;
    document.getElementById("tenhocky").value = ten;
    document.getElementById("namhoc").value = nam;
    
    const btn = document.querySelector("#formHK button");
    btn.innerText = "Cập nhật";
    document.getElementById("formHK").onsubmit = function() { updateHK(); return false; };
}

function updateHK() {
    let f = new FormData();
    f.append("action", "updateHK");
    f.append("mahocky", document.getElementById("mahocky").value);
    f.append("tenhocky", document.getElementById("tenhocky").value);
    f.append("namhoc", document.getElementById("namhoc").value);

    fetch("../php/monHoc.php", {method:"POST", body:f})
    .then(r=>r.json())
    .then(data => {
        if(data.status === "success") {
            alert("Cập nhật học kỳ thành công!");
            location.reload();
        }
    });
}
function deleteHK(id) {
    if(confirm("Bạn có chắc chắn muốn xóa học kỳ này?")) {
        let f = new FormData();
        f.append("action", "deleteHK");
        f.append("mahocky", id);

        fetch("../php/monHoc.php", { method: "POST", body: f })
        .then(r => r.json())
        .then(data => {
            if(data.status === "success") {
                alert("Xóa thành công!");
                loadHK();
            } else {
                alert("Lỗi: " + data.message);
            }
        });
    }
}

// Hàm Xóa
function deleteLHP(id) {
    if(confirm("Bạn có chắc muốn xóa lớp học phần này?")) {
        let f = new FormData();
        f.append("action", "deleteLHP");
        f.append("malhp", id);

        fetch("../php/monHoc.php", { method: "POST", body: f })
        .then(r => r.json())
        .then(data => {
            if(data.status === "success") {
                loadLHP();
            } else {
                alert("Lỗi: " + data.message);
            }
        });
    }
}

// Đổ dữ liệu lên form để chuẩn bị sửa

// Gửi yêu cầu cập nhật về server
function updateLHP() {
    let f = new FormData();
    f.append("action", "updateLHP");
    f.append("malhp", document.getElementById("malhp").value);
    f.append("mamon", document.getElementById("mamonSelect").value);
    f.append("magv", document.getElementById("magv").value);
    f.append("mahocky", document.getElementById("hkSelect").value);
    f.append("malop", document.getElementById("lopSelect").value); // Đảm bảo lấy đúng ID này

    fetch("../php/monHoc.php", { method: "POST", body: f })
    .then(r => r.json())
    .then(data => {
        if (data.status === "success") {
            alert("Cập nhật thành công!");
            document.getElementById("malhp").readOnly = false;
            document.getElementById("formLHP").reset();
            loadLHP();
        }
    });
}
function loadLop() {
    fetch("../php/monHoc.php?action=listLop")
    .then(r => r.json())
    .then(data => {
        let select = '<option value="">-- Chọn lớp --</option>';
        data.forEach(l => {
            // Sử dụng malop làm value và tenlop làm text hiển thị
            select += `<option value="${l.malop}">${l.tenlop}</option>`;
        });
        
        // Sửa ID ở đây thành "lopSelect" để khớp với HTML
        const selectElement = document.getElementById('lopSelect');
        if(selectElement) {
            selectElement.innerHTML = select;
        }
    })
    .catch(err => console.error("Lỗi load lớp:", err));
}
window.onload=loadAll;