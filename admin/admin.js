
const dbFieldsToInput = {
    //types first 3 letters
    "int":"number",
    "var":"text",
    "dat":"date",
    "cha":"text"
}

const MySqlTable = ["Field", "Type", "Null", "Key", "Default", "Extra"];

//_fields is an array of objects, each object is string key-value, from mysql "describe table"
//appending to dom
function formFromFields(_fields, ignore, target, action, method, title){
    //alert("formFromFields");
    target.innerHTML = "";
    var fo = document.createElement("form");
    fo.action = action;
    fo.method = method;

    fo.innerHTML = "<h1>" + title + "</h1>";

    if(!Array.isArray(ignore)){
        ignore = [ignore];
        console.log(ignore);
    }


    var table = [];

    let count = 0;
    _fields.forEach(element => {
        if( ! ignore.includes(element["Field"])){
            var inp = inputFromField(element);

            var lbl = document.createElement("label");
            lbl.innerHTML = element["Field"];

            fo.appendChild(lbl);
            fo.appendChild(inp);

            if(count==0){
                setTimeout(function(){
                    inp.focus();
                }, 1000);
            }
        }
        count++;
    });
    console.log(table);

    var subm = document.createElement("input");
    subm.type = "submit";
    subm.value = "Ok";
    fo.appendChild(subm);
    target.appendChild(fo);
}

function inputFromField(field){
    var fieldT = field["Type"];
    //alert(fieldT);
    var fieldT3 = fieldT.substring(0,3);
    //alert(fieldT3);

    var maxL = false;

    var type="text";
    switch(fieldT3){
        case "int": type = dbFieldsToInput["int"];
        break;
        case "var": type = dbFieldsToInput["var"]; //varchar
            maxL = extractStringMaxLen(fieldT);
        break;
        case "dat": type = dbFieldsToInput["dat"];
        break;
        case "cha": type = dbFieldsToInput["cha"];
            maxL = extractStringMaxLen(fieldT);
        break;
    }

    var inp = document.createElement("input");
    inp.type = type;
    inp.name = field["Field"];
    console.log("inp.t " + inp.type + " maxL " + maxL);
    if(inp.type == "text" && maxL > 0){
        inp.maxLength = maxL;
    }
    else{
        console.log("ingen maxL");
    }
    console.log(inp);
    return inp;
}

function extractStringMaxLen(type){
    console.log("extractStringMaxLen , " + type);
    if(type.indexOf("(")<0 || type.indexOf(")")<0){
        console.log(type.indexOf("("));
        console.log(type.indexOf(")"));
        return false;
    }

    var start = type.indexOf("(");
    var end = type.indexOf(")");

    console.log("start " + start + " " + end);


    var len  = type.substring(start+1,end);
    return parseInt(len);
}

function updateCaseInfo(index){
    var target = document.querySelector("#caseInfo");
    target.innerHTML = "";
    console.log("updateCaseInfo, index " + index);

    var selectedCase = allCases[index];
    console.log(selectedCase);
    renderCase(selectedCase, target);
    renderAddNameForm(target, selectedCase.id, "Whole film name");

    target.appendChild(makeAddFilmButton("Lägg till film"));

    target.scrollIntoView({behavior: "smooth"});//, block: "end", inline: "nearest"

    updateIncludedFilms(selectedCase);
}

function updateIncludedFilms(selectedCase){
    var target = document.querySelector("#includedFilms");
    target.innerHTML = "";
    let films = selectedCase.films;

    let films_ = document.createElement("div");

    films.forEach(elem => {
        //make filmlabels list
        var f = document.createElement("article");
        f.innerHTML = elem.name;
        films_.appendChild(f);


        //disconnect
        var btn = document.createElement("button");
        btn.addEventListener("click", function(){
            var ca = selectedCase["id"];
            var fi = elem["fid"];
            getAjax("../ajax_functions.php?disconnect=yes&case=" + ca + "&film=" + fi,
                function(resp){
                    handleJsonResp(resp);
                    loadAll();
                }
            );
        });
        btn.innerHTML = "disconnect";
        f.appendChild(btn);
    });
    target.appendChild(films_);
}

function makeAddFilmButton(text){
    var btn = document.createElement("input");
    btn.type = "button";
    btn.value = text;
    btn.onclick = function(){
        var ftitle = prompt("Ange film namn:", choosenName);
        if(ftitle == null) return; //if cancel


        //var add_film = "yes";
        var c_id = document.querySelector("#film_id").value;
        var filmInfo = {add_film:"yes",case_id:c_id, name:ftitle};
        postAjax("../ajax_functions.php", filmInfo, function(resp){handleJsonResp(resp)});
    }
    return btn;

}

function renderAddNameForm(target, filmid, placeholder){
    var f = document.createElement("form");
    f.innerHTML="<input id='film_id' type='hidden' value='"+filmid+"'><input id='film_name' type='text' placeholder='"+placeholder+"' autofocus>";
    target.appendChild(f);
    var hints = document.createElement("div");
    hints.id = "hints";
    target.appendChild(hints);
    setTimeout(addNameAjaxSearch(document.querySelector("#film_name")),100);
}

function handleJsonResp(resp){
    console.log(resp);
    resp = JSON.parse(resp);
    if(resp.success){
        alert("OK");
        loadAll();
    }
    else{
        alert(resp.message);
    }
}

function addNameAjaxSearch(elem){
    elem.addEventListener("keyup", function(){
        //console.log(this);
        choosenName = this.value;
        if(this.value.length < 3) return;

        getAjax("../ajax_functions.php?names=yes&string=" + this.value, function(resp){presentHints(document.querySelector("#hints"), JSON.parse(resp))});
    });
}

function presentHints(target, hints){
    target.innerHTML = "";
    //console.log(hints);
    hints.forEach(elem =>{
        var hint = document.createElement("div");
        hint.className = "hint";
        hint.innerHTML = elem.name;
        target.appendChild(hint);

        hint.addEventListener("click", function(){
            document.querySelector("#film_name").value = hint.innerHTML;
            choosenName = hint.innerHTML;
        })
    });
}

function printAdminMenu(target){
    target.innerHTML = "";
    //target.appendChild(document.createTextNode("Admin"));
    

    var link1 = document.createElement("a");
    link1.href = "#";

    link1.id='alink1';
    link1.addEventListener("click", function(ev){
        ev.preventDefault();
        //showTable(document.querySelector("#tables"), "case", fields);
        echoTableAjax(document.querySelector("#tables"), "case");

        setTimeout(function(){
            formFromFields(fields, ['insert_date', 'id'], document.querySelector('.add'), 'index.php', 'post', 'lägg till fodral');
        }, 500);

    });
    link1.innerHTML = "fodral";

    var link2 = document.createElement("a");
    link2.href = "#";

    link2.id='alink2';
    link2.addEventListener("click", function(ev){
        ev.preventDefault();
        //showFilmNames(document.querySelector("#tables"));
        echoTableAjax(document.querySelector("#tables"), "film_title");

        setTimeout(function(){
            formFromFields(fields2, ['insert_date', 'id'], document.querySelector('.add'), 'index.php', 'post', 'lägg till namn');
        }, 500);

    });
    link2.innerHTML = "filmnamn";
    
    var link3 = document.createElement("a");
    link3.href = "#";

    link3.id='alink3';
    link3.addEventListener("click", function(ev){
        ev.preventDefault();
        //showFilmNames(document.querySelector("#tables"));
        echoTableAjax(document.querySelector("#tables"), "film");

        setTimeout(function(){
            formFromFields(fields3, ['insert_date', 'id'], document.querySelector('.add'), 'index.php', 'post', 'lägg till film');
        }, 1000);

    });
    link3.innerHTML = "filmer";

    var link4 = document.createElement("a");
    link4.href = "#";

    link4.id='alink4';
    link4.addEventListener("click", function(ev){
        ev.preventDefault();
        showFilmAndNames(document.querySelector("#tables"));
    });
    link4.innerHTML = "filmer & namn";


    target.appendChild(link1);
    target.appendChild(link2);
    target.appendChild(link3);
    target.appendChild(link4);

}

function showFilmAndNames(target){
    getAjax("../ajax_functions.php?filmandnames=yes", function(resp){getTableTo(target, JSON.parse(resp), "Film and names")});
}
function showTable(target, tableName, tableSchema){
    console.log("showTable " + tableName);
    console.log(target);
    console.log(tableSchema);

    getAjax("../ajax_functions.php?tables=yes&tablename="+tableName,
    function(resp){
        //alert(resp);
        getTableTo(target, JSON.parse(resp), tableName, tableSchema);
    }
    );
}

function echoTableAjax(target, tableName){
    getAjax("../ajax_functions.php?echotables=yes&tablename="+tableName,
    function(resp){
        //alert(resp);
        //getTableTo(target, JSON.parse(resp), tableName, tableSchema);
        target.innerHTML = resp;
    }
    );
}

function getTableTo(target, names, header, fieldsDescr){
    console.log(names);
    target.innerHTML = "<h1>" + header + "</h1>";

    var tbl = document.createElement("table");
    let count =0;
    names.forEach(elem =>{

        if(count==0 && typeof fieldsDescr != "undefined"){
            var firstRow =
                getTableHeadersFromFields(fieldsDescr);
            tbl.appendChild(firstRow);
        }
        else if(count==0){
            var firstRow =
            getTableHeadersFromRow(elem);
            tbl.appendChild(firstRow);
        }

        var row = document.createElement("tr");

        row = getTableRowFromFields(fieldsDescr, elem, count);

        tbl.appendChild(row);
        count++;
    });
    target.appendChild(tbl);
}


function getTableHeadersFromFields(fieldsDescr){
    //alert("getTableHeadersFromFields");
    console.log(fieldsDescr);
    var tr = document.createElement("tr");

    var ignore = [];
/*
    if(!Array.isArray(ignore)){
        ignore = [ignore];
        console.log(ignore);
    }
*/
    console.log(ignore);

    fieldsDescr.forEach(element => {
        console.log(element);
        if( ! ignore.includes(element["Field"])){
            var th = document.createElement("th");
            th.innerHTML = element["Field"];

            tr.appendChild(th);
        }
        else console.log("now ignored " + element["Field"]);
    });
    return tr;
}

function getTableHeadersFromRow(row){
    console.log(row);

    var tr = document.createElement("tr");

    var ignore = [];
    
    var keys = Object.keys(row);

    keys.forEach(key => {
        var th = document.createElement("th");
        th.innerHTML = key;
        tr.appendChild(th);
    });

    return tr;
}

function getTableRowFromFields(fieldsDescr, row, count){
    
    console.log(fieldsDescr);
    console.log(row);

    var tr = document.createElement("tr");
    tr.id = "n_table_" + count;

    var ignore = [];
/*
    if(!Array.isArray(ignore)){
        ignore = [ignore];
        console.log(ignore);
    }
*/
    //console.log(ignore);

    
    if(typeof fieldsDescr == "undefined"){
        var vals = Object.values(row);
        vals.forEach(val =>{
            var td = document.createElement("td");
            td.innerHTML = val;
            tr.appendChild(td);
        });

    }
    else{
        fieldsDescr.forEach(element => {
            console.log(element);
            if( ! ignore.includes(element["Field"])){
                var td = document.createElement("td");
                td.innerHTML = row[element["Field"]];

                tr.appendChild(td);
            }
            else console.log("now ignored " + element["Field"]);
        });
    }
    return tr;
}