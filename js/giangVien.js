function loadAll(){
    loadGV();
    loadMon();
    loadAssign();
}

// ===== LOAD GV =====
function loadGV(){
    fetch("../php/giangVien.php?action=listGV")
    .then(r=>r.json())
    .then(data=>{
        let html="", select="";

        data.forEach(g=>{
            html+=`
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

            select+=`<option value="${g.magv}">${g.hoten}</option>`;
        });

        gvTable.innerHTML=html;
        gvSelect.innerHTML=select;
    });
}

// ===== ADD =====
function addGV(){
    let f=new FormData();
    f.append("action","add");
    f.append("magv",magv.value);
    f.append("hoten",hoten.value);
    f.append("email",email.value);
    f.append("sodienthoai",sdt.value);
    f.append("chuyennganh",chuyennganh.value);

    fetch("../php/giangVien.php",{method:"POST",body:f})
    .then(()=>loadGV());
}

// ===== EDIT =====
function editGV(magvV, hotenV, emailV, sdtV, cnV){
    magv.value = magvV;
    hoten.value = hotenV;
    email.value = emailV;
    sdt.value = sdtV;
    chuyennganh.value = cnV;

    magv.disabled = true;
    btnSubmit.innerText = "Cập nhật";

    formGV.onsubmit = function(){
        updateGV();
        return false;
    };
}

// ===== UPDATE =====
function updateGV(){
    let f=new FormData();
    f.append("action","update");
    f.append("magv",magv.value);
    f.append("hoten",hoten.value);
    f.append("email",email.value);
    f.append("sodienthoai",sdt.value);
    f.append("chuyennganh",chuyennganh.value);

    fetch("../php/giangVien.php",{method:"POST",body:f})
    .then(()=>{
        formGV.reset();
        magv.disabled = false;
        btnSubmit.innerText = "Thêm";

        formGV.onsubmit = function(){
            addGV();
            return false;
        };

        loadGV();
    });
}

// ===== DELETE =====
function deleteGV(id){
    let f=new FormData();
    f.append("action","delete");
    f.append("magv",id);

    fetch("../php/giangVien.php",{method:"POST",body:f})
    .then(()=>loadGV());
}

// ===== MON =====
function loadMon(){
    fetch("../php/giangVien.php?action=listMon")
    .then(r=>r.json())
    .then(data=>{
        let html="";
        data.forEach(m=>{
            html+=`<option value="${m.mamon}">${m.tenmon}</option>`;
        });
        monSelect.innerHTML=html;
    });
}

// ===== PHÂN CÔNG =====
function assign(){
    let f=new FormData();
    f.append("action","assign");
    f.append("magv",gvSelect.value);
    f.append("mamon",monSelect.value);

    fetch("../php/giangVien.php",{method:"POST",body:f})
    .then(()=>loadAssign());
}

function loadAssign(){
    fetch("../php/giangVien.php?action=listAssign")
    .then(r=>r.json())
    .then(data=>{
        let html="";
        data.forEach(p=>{
            html+=`
            <tr>
                <td>${p.hoten}</td>
                <td>${p.chuyennganh}</td>
                <td>${p.tenmon}</td>
            </tr>`;
        });
        pcTable.innerHTML=html;
    });
}

window.onload=loadAll;