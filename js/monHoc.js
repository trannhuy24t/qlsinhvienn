function loadAll(){
    loadMon();
    loadHK();
    loadLHP();
}

// ===== MON =====
function loadMon(){
    fetch("../php/monHoc.php?action=listMon")
    .then(r=>r.json())
    .then(data=>{
        let html="", select="";
        data.forEach(m=>{
            html+=`
            <tr>
                <td>${m.mamon}</td>
                <td>${m.tenmon}</td>
                <td>${m.sotinchi}</td>
                <td>${m.mota}</td>
                <td>
                    <button onclick="deleteMon('${m.mamon}')">Xóa</button>
                </td>
            </tr>`;
            select+=`<option value="${m.mamon}">${m.tenmon}</option>`;
        });
        monTable.innerHTML=html;
        mamonSelect.innerHTML=select;
    });
}

function addMon(){
    let f=new FormData();
    f.append("action","addMon");
    f.append("mamon",mamon.value);
    f.append("tenmon",tenmon.value);
    f.append("sotinchi",sotinchi.value);
    f.append("mota",mota.value);

    fetch("../php/monHoc.php",{method:"POST",body:f})
    .then(()=>loadMon());
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

function addHK(){
    let f=new FormData();
    f.append("action","addHK");
    f.append("mahocky",mahocky.value);
    f.append("tenhocky",tenhocky.value);
    f.append("namhoc",namhoc.value);

    fetch("../php/monhoc.php",{method:"POST",body:f})
    .then(()=>loadHK());
}

// ===== LHP =====
function loadLHP(){
    fetch("../php/monHoc.php?action=listLHP")
    .then(r=>r.json())
    .then(data=>{
        let html="";
        data.forEach(l=>{
            html+=`
            <tr>
                <td>${l.malhp}</td>
                <td>${l.tenmon}</td>
                <td>${l.magv}</td>
                <td>${l.tenhocky}</td>
                <td>${l.namhoc}</td>
            </tr>`;
        });
        lhpTable.innerHTML=html;
    });
}

function addLHP(){
    let f=new FormData();
    f.append("action","addLHP");
    f.append("malhp",malhp.value);
    f.append("mamon",mamonSelect.value);
    f.append("magv",magv.value);
    f.append("mahocky",hkSelect.value);

    fetch("../php/monHoc.php",{method:"POST",body:f})
    .then(()=>loadLHP());
}

window.onload=loadAll;