// ===== ELEMENT =====
const sv = document.getElementById("sv");
const lhp = document.getElementById("lhp");

const cc = document.getElementById("cc");
const gk = document.getElementById("gk");
const ck = document.getElementById("ck");

const table = document.getElementById("table");
const svGPA = document.getElementById("svGPA");
const gpa = document.getElementById("gpa");

const svTong = document.getElementById("svTong");
const bangdiem = document.getElementById("bangdiem");
const tongket = document.getElementById("tongket");

// ===== LOAD SV CHO BẢNG ĐIỂM =====
function loadSVTong(){
    fetch("../php/sinhVien.php")
    .then(r=>r.json())
    .then(data=>{
        let html="";
        data.forEach(s=>{
            html+=`<option value="${s.masv}">${s.hoten}</option>`;
        });
        svTong.innerHTML = html;
    });
}

// ===== LOAD BẢNG ĐIỂM =====
function loadBangDiem(){
    fetch("../php/diem.php?action=bangdiem&masv=" + svTong.value)
    .then(r=>r.json())
    .then(data=>{
        let html="";

        data.monhoc.forEach(m=>{
            html+=`
            <tr>
                <td>${m.tenmon}</td>
                <td>${m.sotinchi}</td>
                <td>${m.diemtong}</td>
                <td>${m.xeploai}</td>
            </tr>`;
        });

        bangdiem.innerHTML = html;

        tongket.innerHTML = `
            🎓 Tổng tín chỉ: <b>${data.tongtinchi}</b> |
            GPA: <b>${data.gpa}</b> |
            Học lực: <b>${data.hocluc}</b>
        `;
    });
}
// ===== LOAD ALL =====
function loadAll(){
    loadSV();
    loadLHP();
    loadSVTong();
    loadDiem();
}

// ===== LOAD SINH VIÊN =====
function loadSV(){
    fetch("../php/sinhVien.php")
    .then(res => res.json())
    .then(data => {
        let html = "";
        data.forEach(s => {
            html += `<option value="${s.masv}">${s.hoten}</option>`;
        });

        // Đổ dữ liệu vào tất cả các ô Select sinh viên cùng lúc
        if(sv) sv.innerHTML = html;
        if(svGPA) svGPA.innerHTML = html;
        if(svTong) svTong.innerHTML = html; 
    })
}

// ===== LOAD LỚP HỌC PHẦN =====
function loadLHP(){
    fetch("../php/monhoc.php?action=listLHP")
    .then(res => res.json())
    .then(data => {
        let html = "";
        data.forEach(l => {
            html += `<option value="${l.malhp}">${l.tenmon}</option>`;
        });

        lhp.innerHTML = html;
    })
    .catch(err => {
        console.error("Lỗi loadLHP:", err);
    });
}

// ===== LƯU ĐIỂM =====
function save(){
    if(!sv.value || !lhp.value){
        alert("Chọn sinh viên và lớp học phần!");
        return;
    }

    let f = new FormData();
    f.append("action","save");
    f.append("masv", sv.value);
    f.append("malhp", lhp.value);
    f.append("diemchuyencan", cc.value || 0);
    f.append("diemgiuaky", gk.value || 0);
    f.append("diemcuoiky", ck.value || 0);

    fetch("../php/diem.php", {
        method:"POST",
        body:f
    })
    .then(res => res.json())
    .then(d => {
        if(d.status === "success"){
            alert("Lưu điểm thành công!");
            clearForm();
            loadDiem();
        } else {
            alert("Lỗi lưu điểm!");
            console.log(d);
        }
    })
    .catch(err => {
        console.error("Lỗi save:", err);
        alert("Lỗi kết nối server!");
    });
}

// ===== LOAD ĐIỂM =====
function loadDiem(){
    fetch("../php/diem.php?action=list")
    .then(res => res.json())
    .then(data => {
        let html = "";

        data.forEach(d => {
            html += `
            <tr>
                <td>${d.masv}</td>
                <td>${d.hoten}</td>
                <td>${d.tenmon}</td>
                <td>${d.diemchuyencan}</td>
                <td>${d.diemgiuaky}</td>
                <td>${d.diemcuoiky}</td>
                <td><b>${d.diemtong}</b></td>
                <td>${d.xeploai}</td>
            </tr>`;
        });

        table.innerHTML = html;
    })
    .catch(err => {
        console.error("Lỗi loadDiem:", err);
    });
}

// ===== GPA =====
function tinhGPA(){
    if(!svGPA.value){
        alert("Chọn sinh viên!");
        return;
    }

    fetch("../php/diem.php?action=gpa&masv=" + svGPA.value)
    .then(res => res.json())
    .then(d => {
        gpa.innerText = "GPA: " + d.gpa;
    })
    .catch(err => {
        console.error("Lỗi GPA:", err);
    });
}

// ===== XUẤT PDF =====
function xuatPDF(){
    // Đổi svGPA thành svTong để khớp với Select box trong phần Tổng kết
    if(!svTong.value){
        alert("Chọn sinh viên để xuất PDF!");
        return;
    }
    // Chuyển hướng đến file pdf.php
    window.location.href = "../php/pdf.php?masv=" + svTong.value;
}

// ===== CLEAR FORM =====
function clearForm(){
    cc.value = "";
    gk.value = "";
    ck.value = "";
}

// ===== LOAD KHI MỞ TRANG =====
window.onload = loadAll;