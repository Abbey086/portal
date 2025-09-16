<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['student_id'])) {
    header("Location: students_login.php");
    exit;
}


$school_id = $_SESSION['school_id'];
 $q = $conn->prepare("SELECT * FROM timetables WHERE school_id = :school_id ");
$q->bindValue(":school_id",$_SESSION["school_id"],SQLITE3_TEXT);
$lg = $q->execute();

 $t = $conn->prepare("SELECT * FROM teachers WHERE school_id = :school_id AND status = 'approved'");
$t->bindValue(":school_id",$_SESSION["school_id"],SQLITE3_TEXT);
$lt = $t->execute();

$classes = $conn->query("SELECT classes FROM schools WHERE school_id = '$school_id'");
$ro = $classes->fetchArray(SQLITE3_ASSOC);
$ro = $ro["classes"];
?>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Timetable</title>
  <link rel="stylesheet" href="../remixicon.css">
  <style>
    body{
      padding: 0;
      margin: 0;
    font-family: Comic Sans MS;
  }

.u > * td:not(:first-of-type),.u > * th:not(:first-of-type){
  display: none;
}

.v > * td:first-of-type,
.v > * th:first-of-type{
  display: none;
}
#con{
  position: relative;
  width: 100%;
  z-index: 1;
  overflow-x: scroll;
}

#con table.edit th select{
  width: 20px;
}
#con table.edit tr td:not(:first-of-type),
#con table.edit thead th:not(:first-of-type){
  min-width: 250px;
}
#con table.edit td,
#con table.edit th{
  height: 100px;
  box-sizing: border-box;
  padding: 6px;
}
table.edit th b{
  font-weight: 400;
  background: white;
  margin: 2px;
  padding: 4px;
  border-radius: 3px;
  color: black;
  font-size: .9rem;
}
table.edit select,
table.edit input {
  margin: 2px;
  border-radius: 4px;
  padding: 3px;
  border: solid 1px black;
}
#con table td,
#con table th{
  border: solid 1px rgb(229,231,235) ;
  padding:0 4px;
  height: 75px;
  width: 100px;
  margin: 0;
}

#con .u tr:last-of-type td{
  border-bottom: solid 1px rgb(229,231,235);
}
#con .u th,
#con .u td{
  border: solid 1px rgb(229,231,235)
}
#con .u{
  position: sticky;
  left: 0;
  width:93px;
  background: rgb(20,184,166);
  border-collapse: collapse;
  color: white;
  z-index: 6;
}
table.v > tbody > tr td span{
 font-weight: 600;
 display: block;
 font-size: 1.1em;
 width:  100px;
 text-align: center;
 max-block-size: 40px;
 overflow: hidden;
}
table.v > tbody > tr td ic{
  color: blue;
}
table.v > tbody > tr td i{
 font-weight: 400;
 display: inline-block;
 width: 100px;
 overflow: hidden;
 height: 30px;
 color: #444;
 font-family: serif;
 font-size: .8em;
}

#con .v thead th{
  border: solid 1px rgb(229,231,235);
  color: white;
  background: rgb(20,184,166);}
#con .v{
  margin-left: 92px;
  position: absolute;
  top: 0;
  border-collapse: collapse;
  z-index: 2;
}
h2{
  color: rgb(20,184,166);
  text-align: center;
  margin-block: 19px;
  font-size: 2.4em;
}
#classes, .modal select{
  width: 98%;
  margin-inline: auto;
  padding: 6px;
  border-radius: 7px;
  margin-block: 8px;
  background: white;
  border: solid 2px rgb(20,184,166);
  display: none;
}
#edit-btn,#save-btn {
  background: rgb(20,20,20);
  color: white;
  border: none;
  margin: 12px 3px;
  border-radius: 6px;
  padding: 6px 12px;
font-size: 13px;
}
.modal{
  background: #0009;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 37;
  width: 100vw;
  display: none;
  place-items: center;
}
.modal .iner h2{
  text-align: left;
  
  font-size: 1.3em;
}
.modal .iner{
  padding: 8px 8px 20px 8px;
  border-radius: 12px;
  background: white;
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 80%;
}

.top{
  display: flex;
gap: 5px;
  align-items: center;
}
.top span.ri-printer-fill{
  color: black;
  border: 2px solid;
  border-radius: 6px;
  padding: 5px 9px;
  font-size: 13px;
}
.top .ri-arrow-left-s-line{
  font-size: 28px;
  margin-left: 12px;
}
</style>
</head>
<body>
  <select id="classes" onchange="populate(tt,this.value)"></select>
      <div id="con">
          <table  id="table" ></table>
      </div>
  <script>
      const dO = new Date();
      const today = dO.getDay();
      
      var days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
      var tt = {
       <?php 
       while($cl = $lg->fetchArray(SQLITE3_ASSOC)):
       ?>
        "<?php echo $cl['class_id'];?>":<?php echo $cl["data"];?>,
      
        <?php endwhile;?>
      }
      
    var cs = document.getElementById("classes");
      var ci = "<?php echo $_SESSION["grade_level"]?>";
    Object.keys(tt).map(key => {
      var sl = ci == key?"selected":"";
      
      cs.innerHTML += `<option ${sl} value="${key}">${key}</option>`;
    });

    var editMode = false;
    var currentClass = ci;
   var teacher = [
   <?php 
       while($tr = $lt->fetchArray(SQLITE3_ASSOC)):
       ?>
       
        <?php $tn = $tr['full_name'];
        echo "['".$tn."',".$tr['id']."],"; ?>
        <?php endwhile;?>
   ];
   var temp = "<option>Select instructor</option>";
   for(i=0;i<teacher.length;i++){
     var ct = teacher[i];
     temp += `<option value="${ct[1]}">${ct[0]}</option>`;
   }
    function populate(t, g) {
      var cont = false;
      Object.keys(t).map(key=>{
        cont = cont || (key==g);
      })
      if(cont){
      currentClass = g;
      var data = t[g];
      var con = document.getElementById("con");
      con.innerHTML = "<table id='table'></table>";
      var table = document.getElementById("table");
      Object.keys(data).map(key => {
        var other = today==6?days[0]:days[today+1]
        if (key == "periods") {
          var tri = "<th>Period</th>";
          for (i = 0; i < data[key].length; i++) {
            if(editMode){
              tri += `<th> <input onchange="handlePeriod(this,'${key}',${i},'${g}',0)" style="width:auto" type="time" value="${data[key][i][0]}"/> - <input value="${data[key][i][1]}" style="width:auto" onchange="handlePeriod(this,'${key}',${i},'${g}',1)" type="time"/> <br><br><b onclick="np(${i},'${g}')" class='ri-function-add-line'> Insert Period</b><b class='ri-delete-bin-5-fill' onclick="dp(${i},'${g}')"> Delete Period</b></th>`;
            }else{
              tri += `<th> <span>${data[key][i][0]}</span> - <span>${data[key][i][1]}</span></th>`;
              
            }
          }
          table.innerHTML += `<thead> ${tri} </thead>`;
         
        } else if((key == days[today] )||( key == other)){
          
          var tri = `<td>${key[0].toUpperCase()+key.substring(1)}</td>`;
          for (i = 0; i < data[key].length; i++) {
            if (editMode) {
              tri += `<td> <input type="text" value="${data[key][i][0]}" onchange="hSc(this,'${key}',${i},'${g}')" data-index="${i}" placeholder='Subject/Activity' data-day="${key}" data-field="subject"> <input readonly type="text" style="display:none" value="${data[key][i][1]}" data-index="${i}" data-day="${key}" data-field="instructor" class="instructor">
              <input style="display:none" type="text" value="${data[key][i][2]}" data-index="${i}" data-day="${key}" class='it' data-field="type">
              
              <select onchange="handleInstructor(this,'${key}',${i},'${g}')">
              <option value="none">None</option>
              <option ${data[key][i][2]=='id'?'selected':''} value="id">Teacher Id</option>
              <option ${data[key][i][2]=='name'?'selected':''} value="name">Custom Instructor</option>
              </select>
              
              <input placeholder='Instructor Name' onchange="instructorer(this,'${key}',${i},'${g}',true)" style="display: ${data[key][i][2]=='name'?'inline':'none'}" type="text" class="iname" value="${data[key][i][1]}">
              <select selected="${data[key][i][1]}" onchange="instructorer(this,'${key}',${i},'${g}')" style="display: ${data[key][i][2]=='id'?'inline':'none'}" class="tslt">
                ${temp}
              </select>
              </td>`;
            } else {
        
          var tid = data[key][i][1];
        //  alert("tid"+tid+tid.length);
          
          var tv = tid;
        //  alert("tv1",tv);
           for (q= 0; q < teacher.length; q++) {
       //   alert("tq"+teacher[q]);
            if (teacher[q][1] == tid) {
              tv = teacher[q][0];
            }
          }
    //      alert("tv2"+tv);
              tri += `<td> <span>${data[key][i][0]} </span> <i>${tv}${(data[key][i][2]=="id")?"<ic class='ri-verified-badge-fill'></ic>":""}</i> </td>`;
            }
          }
          table.innerHTML += `<tr> ${tri} </tr>`;
        }
      });

    
    var allts = document.querySelectorAll(".tslt");
    for(i=0;i<allts.length;i++){
      var s = allts[i].getAttribute("selected") || "";
    var alls = allts[i].querySelectorAll("option");
          for(q=0;q<alls.length;q++){
             if(alls[q].getAttribute("value")==s){
               alls[q].setAttribute("selected","")
             }
          }
    }
    }else{
      document.querySelector("table").innerHTML = "Nogbh ygfvgv vg";
    }
     beu();
                editMode?document.querySelector("table.v").classList.add("edit"):document.querySelector("table.v").classList.remove("edit");editMode?document.querySelector("table.u").classList.add("edit"):document.querySelector("table.v").classList.remove("edit");
     
     }
    
    function np(id,gl){
      Object.keys(tt[gl]).map(key =>{
                      console.log(key);

      
              var ar = tt[gl][key];
              var tp = id;
              var t = ["","",""];
                var temp = [];
                for(i=0;i<ar.length;i++){
                  temp.push(ar[i]);
                  if(i==tp){
                    temp.push(t);
                  }
                }

                tt[gl][key] = temp;
                      populate(tt, gl);
    });
    }
    function dp(id,gl){
      if(confirm("Are you sure you want delete this row? This is irreversible")){
      Object.keys(tt[gl]).map(key =>{
                      console.log(key);

      
              var ar = tt[gl][key];
              var tp = id;
                var temp = [];
                for(i=0;i<ar.length;i++){
                  if(i!=tp){
                  temp.push(ar[i]);
                  }
                }

                tt[gl][key] = temp;
                      populate(tt, gl);
    });
    }
    }
    function hSc(e,hkey,hi,gl){
      tt[gl][hkey][hi][0] = e.value;
      populate(tt,gl);
    }
function handleInstructor(e,hkey,hi,gl){
  var i = e.parentElement.querySelector("input.it");
  i.value = e.value;
  tt[gl][hkey][hi][2] = e.value;
  if(e.value == "none"){
    
  tt[gl][hkey][hi][1] = "";
  }
      populate(tt, gl);

}
function handlePeriod(e,hkey,hi,gl,z){
  tt[gl][hkey][hi][z] = e.value;
      populate(tt, gl);

}
function instructorer(e,hkey,hi,gl,z=false){
  tt[gl][hkey][hi][1] = e.value;
      populate(tt, gl);
      
}
    function toggleEditMode() {
      editMode = !editMode;
      if (editMode) {
        document.getElementById("edit-btn").style.display = "none";
        document.getElementById("save-btn").style.display = "inline";
        populate(tt, currentClass);
      } else {
        document.getElementById("edit-btn").style.display = "inline";
        document.getElementById("save-btn").style.display = "none";
        populate(tt, currentClass);
      }
                editMode?document.querySelector("table.v").classList.add("edit"):document.querySelector("table.v").classList.remove("edit");editMode?document.querySelector("table.u").classList.add("edit"):document.querySelector("table.v").classList.remove("edit");

    }

function saveChanges() {
  var inputs = document.querySelectorAll("input[type='text']");
  inputs.forEach(input => {
    var day = input.getAttribute("data-day");
    var index = input.getAttribute("data-index");
    var field = input.getAttribute("data-field");
    if (field == "subject") {
      tt[currentClass][day][index][0] = input.value;
    } else if (field == "instructor") {
      tt[currentClass][day][index][1] = input.value;
    }
  });
  toggleEditMode();
  var datat  = JSON.stringify(tt[currentClass]);
  cloudy(currentClass,datat);
}
async function cloudy(gl,data){
  const f = await fetch(`update_timetable.php?data=${data}&class=${gl}`);
  const response = await f.json();
  console.log(response);
}
    populate(tt, ci);
  </script>
  <div class="modal">
    <div class="iner">
  <h2>Add Timetable for another class</h2>
<select id="class_select"></select>
<button type="button" style="background:rgb(20,184,166); border:none; display:inline; width:20vw; border-radius : 9px; padding:6px" class="" onclick="this.parentElement.parentElement.style.display='none';addrt()">Add</button>
<button type="button" style="background:rgb(217,14,16); border:none; color:white; display:inline; width:20vw; border-radius : 9px; padding:6px" class="" onclick="this.parentElement.parentElement.style.display='none';">Cancel</button>
      
    </div>
  </div>
<script>
    var classes = [<?php echo $ro;?>];
  var slt = document.getElementById("class_select");
  for(i = 0; i< classes.length; i++){
            var cont = false;
      Object.keys(tt).map(key=>{
        cont = cont || (key==classes[i]);
      })
      
        if(!cont){
      slt.innerHTML += `<option value="${classes[i]}">${classes[i]}</option>`;}
    
  }
  
  function addrt(){
    var rt = {
         periods: [["08:00","09:40"]],
         monday: [["","",""]],
         tuesday: [["","",""]],
         wednesday: [["","",""]],
         thursday: [["","",""]],
         friday: [["","",""]],
         saturday: [["","",""]],
         sunday: [["","",""]]
       };
       tt[slt.value]= rt;
       populate(tt,slt.value);
       
      cs.innerHTML = ``;
       Object.keys(tt).map(key => {
         var d = (key == slt.value)?"selected":"";
      cs.innerHTML += `<option ${d} value="${key}">${key}</option>`;
    });
    slt.innerHTML=""
  for(i = 0; i< classes.length; i++){
            var cont = false;
      Object.keys(tt).map(key=>{
        cont = cont || (key==classes[i]);
      })
        if(!cont){
      slt.innerHTML += `<option value="${classes[i]}">${classes[i]}</option>`;}
    
  }
  }
  function beu(){
  var div = document.getElementById("con");
          div.innerHTML = div.innerHTML + div.innerHTML;
  var tables = div.querySelectorAll("table");

   tables[0].classList.add("u");
   tables[1].classList.add("v");
     
  }
  beu()
</script>
</body>
</html>
