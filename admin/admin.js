
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
function formFromFields(_fields, ignore, target, action, method, title, description){
    //alert("formFromFields");
    target.innerHTML = "";
    var fo = document.createElement("form");
    fo.action = action;
    fo.method = method;
    fo.id = "form1";

    fo.innerHTML = "<h1>" + title + "</h1>"; //Initial assign...

    var desc = "";
    if(typeof description !== "undefined"){
        desc = description;
        fo.innerHTML += "<p class='description'>" + desc + "</p>";
    }

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

    setTimeout(function(){
        var fk = document.querySelector(".foreignKey");
        if(fk){
            //alert("fk");
            var ftbl = fk.dataset.referecedTable;
            var ffld = fk.dataset.referencedField;

            getAjax("../ajax_functions.php?get_select=yes&ref_table=" + ftbl + "&ref_field=" + ffld, function(resp){
                console.log(resp);
                var data = JSON.parse(resp);
                console.log(data);

                replaceWithSelect(fk, data);
            });
        };
        target.scrollIntoView({behavior: "smooth", block: "center"});
    },500);
}

//TODO: make like previous but with ajax
function ajaxFormFromFields(_fields, ignore, target, action, method, title, description, orderByAlpha){
    target.innerHTML = "";
    var fo = document.createElement("form");
    fo.action = action;
    fo.method = method;
    fo.id = "form1";

    //prepare for ajax

    fo.innerHTML = "<h1>" + title + "</h1>"; //Initial assign...

    var desc = "";
    if(typeof description !== "undefined"){
        desc = description;
        fo.innerHTML += "<p class='description'>" + desc + "</p>";
    }

    var orderb = false;
    if(typeof orderByAlpha !== "undefined"){
        orderb = orderByAlpha;
    }

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
    subm.value = "Ok ajax";
    subm.id = "ajax_submit_button";
    fo.appendChild(subm);
    target.appendChild(fo);

    setTimeout(function(){
        var fk = document.querySelector(".foreignKey");
        if(fk){
            //alert("fk");
            var ftbl = fk.dataset.referecedTable;
            var ffld = fk.dataset.referencedField;

            var queryString = "../ajax_functions.php?get_select=yes&ref_table=" + ftbl + "&ref_field=" + ffld;

            if(orderb){
                queryString += "&orderalphabet="+orderb;
            }

            getAjax(queryString, function(resp){
                console.log(resp);
                var data = JSON.parse(resp);
                console.log(data);

                replaceWithSelect(fk, data);
            });
        };
        target.scrollIntoView({behavior: "smooth", block: "center"});
    },500);

    /*setTimeout(function(){
        target.scrollIntoView({behavior: "smooth", block: "center"});
    });*/ //2022-02-03 commented out in lack of delay milli-sec

    fo.addEventListener("submit", function(ev){
        ev.preventDefault();//so use ajax

        //disable submit button
        var sb = document.querySelector("#ajax_submit_button");

        if(sb){
            sb.disabled = true;
        }

        if(this.method == "post")
        if (this.elements["c_short_name"]){
            var data = {};
            data.add_case = "yes";
            data.name = this.elements["c_short_name"].value; //TODO: get name from context
            data.location = this.elements["location"].value;
            postAjax(this.action, data, function(resp){
                console.log(resp);
                var result = JSON.parse(resp);
                loadAll(result.id);
            });
        }
        else if(this.elements["name"] && this.elements["film_id"]){
            console.log("Want to add film title?");
            var data = {};
            data.add_title = "yes";
            data.name = this.elements["name"].value;
            data.film_id = this.elements["film_id"].value;
            postAjax(this.action, data, function(resp){
                console.log(resp);
                var result = JSON.parse(resp);
                shortMessage("Sparade rader: " + result.affected_rows);
                loadAll();
            });
        }
    });
}

function inputFromField(field){
    var fieldT = field["Type"];
    //alert(fieldT);
    var fieldT3 = fieldT.substring(0,3); //getting first 3 letters only
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

    //referencing table and column
    if(field["Referenced_table"] != null && field["Referenced_field"] != null){
        //alert(field["Referenced_table"]);
        inp.dataset.referecedTable = field["Referenced_table"];
        inp.dataset.referencedField = field["Referenced_field"];
        inp.className = "foreignKey";
    }

    console.log(inp);
    return inp;
}

//tested with mysql/mariadb
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
    console.log("len: " + len);
    return parseInt(len);
}

//planned to be used with help of a static textfile (js-file) 'database_structure', supplied by root admin/developer
function DB_get1VarcharLength(fields){
    console.log("DB_get1VarcharLength");
    console.log(fields);
    var found = null;
    fields.forEach(element => {
        if(element.Type.substring(0,4) == "varc"){
            var maxLen = extractStringMaxLen(element.Type);
            found = maxLen;
            console.log("found!");
        }
    });
    return found;
}

//When clicking on a 'case'
//Makes a case handling area appear
//TODO: get case info again with ajax
function updateCaseInfo(index){
    var target = document.querySelector("#caseInfo");
    target.innerHTML = "";
    console.log("updateCaseInfo, index " + index);

    var selectedCase = allCases[index];
    console.log(selectedCase);


    var caseId = selectedCase.id;
    getAjax("../ajax_functions.php?case_info=yes&case_id="+caseId, function(resp){
        console.log(resp);
        var includedFilms = JSON.parse(resp); //should get array with objects that each have "name"
        console.log(includedFilms);
        updateIncludedFilms2(includedFilms);

    });

    renderCase(selectedCase, target);
    renderAddNameForm(target, selectedCase.id, "Whole film name");

    //also offer delete case
    target.appendChild(offerDeleteCase(caseId));

    target.scrollIntoView({behavior: "smooth"});//, block: "end", inline: "nearest"

    //updateIncludedFilms(selectedCase);
}

//this 2:nd version based on a new variant of the "get_all" query
function updateIncludedFilms2(includedFilms){ //takes an array that should refer to one case and one or more films...
    var target = document.querySelector("#includedFilms");
    target.innerHTML = "";
    let films = includedFilms;

    let films_ = document.createElement("div");

    films.forEach(elem => {
        //make filmlabels list
        if(elem.fid /*&& elem.ftid*/){
        var f = document.createElement("article");
        f.innerHTML = elem.name;
        films_.appendChild(f);


        //disconnect
        var btn = document.createElement("button");
        btn.addEventListener("click", function(){
            var ca = includedFilms[0]["id"];
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
    }
    });
    target.appendChild(films_);
}

//the adding of film/disc to a case - 'dialog'
function makeAddFilmButton(text){
    var btn = document.createElement("input");
    btn.type = "button";
    btn.id = "addFilmButton";
    btn.value = text;
    btn.onclick = function(){
        var ftitle = prompt("Ange film namn:", choosenName);
        if(ftitle == null) return; //if cancel


        //var add_film = "yes";
        var c_id = document.querySelector("#case_id").value;
        var filmInfo = {add_film:"yes",case_id:c_id, name:ftitle};
        postAjax("../ajax_functions.php", filmInfo, function(resp){handleJsonResp(resp)});
    }
    return btn;
}

function offerDeleteCase(caseId){
    console.log("offerDeleteCase: " + caseId);
    var deleteCaseBtn = document.createElement("button");
    deleteCaseBtn.innerHTML = "Delete";
    deleteCaseBtn.id = "deleteCaseButton";
    deleteCaseBtn.className = "bottom_right";


    deleteCaseBtn.addEventListener("click", function(){
        getAjax("../ajax_functions.php?delete_case=yes&case_id=" + caseId,
        function(resp){
            handleJsonResp(resp);
            loadAll();
        }
    );
    });
    return deleteCaseBtn;
}

//the adding of 1-film-case - 'dialog'
function makeAdd1FilmCaseButton(text){
    var btn = document.createElement("input");
    btn.type = "button";
    btn.id = "add1FilmCaseButton";
    btn.value = text;
    btn.onclick = function(){
        var ftitle = prompt("Ange film namn:", choosenName);
        if(ftitle == null) return; //if cancel

        var maxLengthT = DB_get1VarcharLength(fields_1);

        console.log(maxLengthT);

        shname = shortenName(ftitle, maxLengthT);


        //var add_film = "yes";
        var filmInfo = {add_1film_case:"yes", name:ftitle, short_name: shname};
        postAjax("../ajax_functions.php", filmInfo, function(resp){handleJsonResp(resp)});
        //TODO: add film if needed and a case, using (shortened) title...
    }
    return btn;
}



function renderAddNameForm(target, caseid, placeholder){
    var f = document.createElement("form");
    f.innerHTML="<input id='case_id' type='hidden' value='"+caseid+"'><input id='film_name' type='text' placeholder='"+placeholder+"' autofocus autocomplete='off'>";
    target.appendChild(f);
    var hints = document.createElement("div");
    hints.id = "hints";
    target.appendChild(hints);
    setTimeout(addNameAjaxSearch(document.querySelector("#film_name"), "#hints", "#film_name"),100);

    //submit button
    var submitBtn = makeAddFilmButton("Lägg till film");
    submitBtn.id = "addFilmButton";
    f.appendChild(submitBtn); //#addFilmButton
    f.addEventListener("submit", function(ev){
        ev.preventDefault();
        document.querySelector("#addFilmButton").click();

    })
}

function renderAdd1DiscCaseForm(target, placeholder){
    var f = document.createElement("form");
    f.innerHTML="<input id='film_name2' type='text' placeholder='"+placeholder+"' autofocus autocomplete='off'>";
    target.appendChild(f);
    var hints = document.createElement("div");
    hints.id = "hints2";
    target.appendChild(hints);
    setTimeout(addNameAjaxSearch(document.querySelector("#film_name2"), "#hints2", "#film_name2"),100);

    //submit button
    var submitBtn = makeAdd1FilmCaseButton("Add 1-disc-film");
    submitBtn.id = "add1DiscFilm";
    f.appendChild(submitBtn); //#add1DiscFilm
    f.addEventListener("submit", function(ev){
        ev.preventDefault();
        document.querySelector("#add1DiscFilm").click();

    });
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

function addNameAjaxSearch(elem, hintsTargetQuerySelector, filmNameQuerySelector){
    elem.addEventListener("keyup", function(){
        //console.log(this);
        choosenName = this.value;
        if(this.value.length < 3) return; //to not search for too few letters

        var val = encodeURIComponent(this.value);

        getAjax("../ajax_functions.php?names=yes&string=" + val, function(resp){
            console.log(resp);
            presentHints(document.querySelector(hintsTargetQuerySelector), JSON.parse(resp), filmNameQuerySelector);
        });
    });
}

function presentHints(target, hints, filmNameQuerySelector){
    if(target == null){
        return false;
    }
    target.innerHTML = "";//clear and list if any
    console.log(hints);
    hints.forEach(elem =>{
        var hint = document.createElement("div");
        hint.className = "hint";
        hint.innerHTML = elem.name;
        target.appendChild(hint);

        hint.addEventListener("click", function(){
            document.querySelector(filmNameQuerySelector).value = hint.innerHTML;
            choosenName = hint.innerHTML;
            var fna = document.querySelector(filmNameQuerySelector);
            if(fna) fna.focus();
        })
    });
}

function printAdminMenu(target, delay){
    target.innerHTML = "";
    //target.appendChild(document.createTextNode("Admin"));
    
    var link1 = document.createElement("a");
    link1.href = "#";
    link1.innerHTML = "fodral";

    link1.id='alink1';

    if(typeof delay == "undefined"){
        delay = 500;
    }
    link1.addEventListener("click", function(ev){
        ev.preventDefault();
        //showTable(document.querySelector("#tables"), "case", fields);
        echoTableAjax(document.querySelector("#tables"), "case");

        setTimeout(function(){
            ajaxFormFromFields(fields, ['insert_date', 'id'], document.querySelector('.add'), '../ajax_functions.php', 'post', 'Lägg till fodral');
        }, delay);

    });


    var link2 = document.createElement("a");
    link2.innerHTML = "filmer";
    link2.href = "#";

    link2.id='alink2';
    link2.addEventListener("click", function(ev){
        ev.preventDefault();
        //showFilmNames(document.querySelector("#tables"));
        echoTableAjax(document.querySelector("#tables"), "film");

        setTimeout(function(){
            //fields2 is set by the use_table_description function
            formFromFields(fields2, ['insert_date', 'id'], document.querySelector('.add'), 'index.php', 'post', 'lägg till film');
        }, 1000);

    });

    //for listing titles
    var link3 = document.createElement("a");
    link3.innerHTML = "filmnamn";
    link3.href = "#";

    link3.id='alink3';
    link3.addEventListener("click", function(ev){
        ev.preventDefault();
        //showFilmNames(document.querySelector("#tables"));
        echoTableAjax(document.querySelector("#tables"), "film_title", "name");

        setTimeout(function(){
            ajaxFormFromFields(fields3, ['insert_date', 'id'], document.querySelector('.add'), '../ajax_functions.php', 'post', 'lägg till namn', 'Lägg till filmtiteln på ett annat språk, välj sen existerande namnet i rull-listan. För vanlig registrering är det enklast att lägga in filmer via "fodral" (case)', 'f_short_name');
        }, delay);

    });


    var link4 = document.createElement("a");
    link4.href = "#";
    link4.innerHTML = "filmer & namn";

    link4.id='alink4';
    link4.addEventListener("click", function(ev){
        ev.preventDefault();
        showFilmAndNames(document.querySelector("#tables"));
    });

    link4.title="Filmer med kopplade namn ger en sammanfattande bild av filmer / filmnamn"


    //TODO: link5; to offer a fast way to insert case/film/film_title, initially for case with one film in it, asking "Enter film title"
    var link5 = document.createElement("a");
    link5.href = "#";
    link5.innerHTML = "Add 1-disc-case";
    link5.id = "alink5";

    link5.addEventListener("click", function(ev){
        ev.preventDefault();
        setTimeout(function(){
            //ajaxFormFromFields(fields, ['insert_date', 'id'], document.querySelector('.add'), '../ajax_functions.php', 'post', 'Lägg till fodral');
            renderAdd1DiscCaseForm(document.querySelector('.add'), 'abc')
        }, delay);
    });

    target.appendChild(link1);
    target.appendChild(link2);
    target.appendChild(link3);
    target.appendChild(link4);

    //test
    target.appendChild(link5);

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

function echoTableAjax(target, tableName, orderBy){
    var ob = "";
    var descr = "";
    if(typeof orderBy !== "undefined"){
        ob = orderBy;
    }

    getAjax("../ajax_functions.php?echotables=yes&tablename="+tableName+"&orderby="+ob,
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
    
    //console.log(fieldsDescr);
    //console.log(row);

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

function getFirstReferencedTable(childTableName){
    console.log("getFirstReferencedTable " + childTableName);
    //select * from KEY_COLUMN_USAGE kcu where kcu.REFERENCED_TABLE_SCHEMA = 'dvd_invent' and kcu.TABLE_NAME = childTableName
}

function replaceNumberFieldWithReferences(idField, referecedTable, referencedField){
    getAjax("../ajax_functions.php?get_select=yes&ref_table=" + referecedTable + "&ref_field=" + referencedField, function(resp){
        console.log(resp);
    });
}

function replaceWithSelect(inputElem, data){
    var sel = document.createElement("select");
    data.forEach(element => {
        if(element["id"]){
            var keys = Object.keys(element);
            //console.log(keys);

            var o = document.createElement("option");
            o.value = element["id"];
            o.text = element[keys[1]];//the second field, whatever it is...

            sel.add(o);
        }
    });


    var pare = inputElem.parentNode;
    console.log(pare);
    pare.insertBefore(sel, inputElem);
    pare.removeChild(inputElem);
    sel.name = inputElem.name;
}


//text functions

function shortenName(filmName, maxLen){
    console.log("shortenName " + filmName + ", maxLen: " + maxLen);
    if(typeof filmName !== "string"){
        return null;
    }
    if(typeof maxLen !== "number"){
        return null;
    }

    var tempName = "";

    if(filmName.length <= maxLen){
        tempName = replaceSpaceWithUnderscore(filmName).toLowerCase();
        return tempName;
    }
    //else

    tempName = replaceSpaceWithCamelCase(filmName);

    if(tempName.length <= maxLen){
        return tempName;
    }
    //else

    tempName = removeVowelsUntil(tempName,maxLen,true);

    if(tempName.length <= maxLen){
        return tempName;
    }
    //else

    //Hmm, ("Sagan om ringen", 4) becomes "sgnO", well

    tempName = removeVowelsUntil(tempName,maxLen,false);

    if(tempName.length <= maxLen){
        return tempName;
    }
    //else

    tempName = tempName.substring(0, maxLen);
    return tempName;


}

function replaceSpaceWithUnderscore(text){
    return text.replace(" ", "_");
}

function replaceSpaceWithCamelCase(text, sadCamel){
    if(typeof sadCamel == "undefined"){
        sadCamel = true;
    }

    var pieces = text.split(" ");

    var newText = "";
    if(pieces.length > 1){
        var count = 0;
        pieces.forEach(element => {
            var newW = "";//a word?
            if(count == 0){ // first word
                if(sadCamel){
                    newW = element.substring(0,1).toLowerCase();
                    newW += element.substring(1).toLowerCase();
                }
                else{
                    newW = element.substring(0,1).toUpperCase();
                    newW += element.substring(1).toLowerCase();
                }
            }
            else{
                newW = element.substring(0,1).toUpperCase();
                newW += element.substring(1).toLowerCase();
            }
            newText += newW;
            count++;
        });
    }
    return newText;
}

function removeVowelsUntil(text, maxLengthWanted, spareCapitalL){

    console.log("removeVowelsUntil");

    if(typeof spareCapitalL == "undefined"){
        spareCapitalL = false;
    }
    else{
        spareCapitalL = true;
    }

    if(typeof text !== "string"){
        return false;
    }

    if(typeof maxLengthWanted == "undefined" || maxLengthWanted == 0){
        console.log("maxLengthW undefined");
        //remove all vowels
        var tempText = text;

        var temp = tempText;
        do{
            temp = tempText;
            if(!spareCapitalL)
                tempText = removeLastVowel(tempText);
            else
                tempText = removeLastSmallVowel(tempText)
        } while(temp != tempText);
    }
    else if(typeof maxLengthWanted == "number"){
        console.log("maxLengthW " + maxLengthWanted);
        var newLen = 10000;
        var tempText = text;
        var temp = tempText;

        do{
            console.log(newLen);
            temp = tempText;
            if(!spareCapitalL)
                tempText = removeLastVowel(tempText);
            else
                tempText = removeLastSmallVowel(tempText);
            newLen = tempText.length;
        } while(newLen > maxLengthWanted && temp != tempText);
    }
    else{
        return false;
    }

    return tempText;
}

function removeLastVowel(text){
    var vowels = "aeiouyåäö";
    var reversedLowered = reverseText(text.toLowerCase(), true); // true - get array
    var asArray = stringToArray(text);

    var len = reversedLowered.length;
    var foundIndex = false;
    var indexMirrored = 0;

    for(var i=0; i<len; i++){
        if(vowels.includes(reversedLowered[i])){
            //var removed = reversed.splice(i,1);
            console.log("includes");
            foundIndex = i;
            indexMirrored = len - i;
            break;
        }
    }

    console.log(foundIndex);

    if(foundIndex !== false){
        var removed = asArray.splice(indexMirrored-1,1);
        console.log(removed);
    }
    else{
        console.log("no last vowel found");
    }

    return asArray.join("");
}

function removeLastSmallVowel(text){
    var vowels = "aeiouyåäö";
    var reversed = reverseText(text, true); // true - get array
    var asArray = stringToArray(text);

    var len = reversed.length;
    var foundIndex = false;
    var indexMirrored = 0;

    for(var i=0; i<len; i++){
        if(vowels.includes(reversed[i])){
            //var removed = reversed.splice(i,1);
            console.log("includes");
            foundIndex = i;
            indexMirrored = len - i;
            break;
        }
    }

    console.log(foundIndex);

    if(foundIndex !== false){
        var removed = asArray.splice(indexMirrored-1,1);
        console.log(removed);
    }
    else{
        console.log("no last vowel found");
    }

    return asArray.join("");
}

function reverseText(text, returnAsArray){
    var newArray = stringToArray(text);
    newArray = newArray.reverse();

    if(typeof returnAsArray != "undefined"){
        if(returnAsArray === true){
            return newArray;
            //end
        }
    }

    var newText = "";

    newArray.forEach(element => {
        newText += element;
    });

    return newText;
}

function stringToArray(text){
    var newArray = [];

    var len = text.length;

    var tempText = text; // todo: check type

    for(var i = 0; i<len; i++){
        newArray.push(tempText.substring(0,1));
        tempText = tempText.substring(1);
    }

    return newArray;
}