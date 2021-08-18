selectedCase = null;
allCases = null;
choosenName = null;

function getAjax(url, success) {
	//console.log("getAjax med " + url + " och " + success);
	var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	xhr.open('GET', url);
	xhr.onreadystatechange = function() {
		if (xhr.readyState>3 && xhr.status==200) success(xhr.responseText);
		else if (xhr.readyState>3 && xhr.status>=500){ alert("Server error (" + xhr.responseText + ")") }
		else if (xhr.readyState>3 && xhr.status>=400){ alert("Client error (" + xhr.responseText + ")") }
	};
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	xhr.send();
	return xhr;
}

//data can be key1=value1&key2=value2
function postAjax(url, data, success) {
	console.log("postAjax till url " + url);
	var params = typeof data == 'string' ? data : Object.keys(data).map(
		function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
	).join('&');

	console.log(params);

	var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
	xhr.open('POST', url, true);
	xhr.onreadystatechange = function() {
		if (xhr.readyState>3 && xhr.status==200) {
			console.log("postAjax succeeded");
			success(xhr.responseText);
		}
		else if (xhr.status>=500) alert("Server error");
		else if (xhr.status>=400) alert("Client error");
	};

	/*xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');*/
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send(params);
	return xhr;
}

function deleteMe(elem){
    elem.parentNode.removeChild(elem);
}

function shortMessage(text){
    var messBox = document.createElement("div");
    messBox.innerHTML = text;
    messBox.style = "position: absolute; left: 10%; width:50%; border:2px solid green";

    setTimeout(function(){
        deleteMe(messBox);
    }, 1500);
}

function render(resp, target){

    var dvdCases = [];

    resp.forEach(elem => {
        var dvdCase = {};

        console.log(elem);

        dvdCase.shortN = elem.c_short_name ? elem.c_short_name : "-";
        dvdCase.loc = elem.location ? elem.location : "?";
        dvdCase.haveFilm = elem.fid ? true : false;
        dvdCase.id = elem.id ? elem.id : null;

        //list of discs (films)
        dvdCase.films = [];



        var filmDisc = {};
        filmDisc.fid = elem.fid;
        filmDisc.name = elem.ftid ? elem.name : elem.f_short_name;

        if(elem.ftid){
            console.log("found film title id");
        }
        else{
            console.log("no film title id");
        }

        console.log(filmDisc.name);

        //check if allready have
        var foundCase = searchById(elem.id, dvdCases); //this is based upon query getAjax("../ajax_functions.php?all=yes" "dvd1"
        if(foundCase){
            console.log("have allready... multi film case");
            foundCase.films.push(filmDisc);
        }
        else{ //empty or filled case
            console.log("new case / film");
            if(dvdCase.haveFilm){
                dvdCase.films.push(filmDisc);
            }
            dvdCases.push(dvdCase);
        }
    });

    target.innerHTML = "";

    dvdCases.forEach(elem =>{
        renderCase(elem, target);
    });

    allCases = dvdCases;

}

function renderCase(_case, target){
    console.log(_case);
    var cas = document.createElement("div");
    cas.className = "case";

    cas.dataset.id = _case.id;

    var row1 = document.createElement("div");
    row1.className = "row1";
    var row2 = document.createElement("div");
    row1.className = "row2";
    var row3 = document.createElement("div");
    row3.className = "row3";

    row1.innerHTML = _case.shortN;
    row2.innerHTML = _case.loc;
    row3.innerHTML = "x " + _case.films.length;


    cas.appendChild(row1);
    cas.appendChild(row2);
    cas.appendChild(row3);


    if(_case.haveFilm){
        cas.className += " haveDisc";
    }

    target.appendChild(cas);
}

function initCaseClick(){
    //alert("init");
    var cases = document.getElementsByClassName("case");
    var len = cases.length;
    //alert("len " + len);
    for(var i=0; i<len; i++){
        cases[i].dataset.index = i;
        cases[i].addEventListener("click", handleCaseClick, false);
    }
}

function handleCaseClick(evt){
    selectedCase = evt.currentTarget.dataset.index;//this.dataset.id;
    //console.log("clicked id" + selectedCase);
    updateCaseInfo(selectedCase);
}

function searchById(id, cases){
    let found = false;
    let count = 0;
    cases.forEach(elem => {
        //console.log(elem.id);

        //check if allready have
        if (elem.id == id){
            found = count;
        }
        count -= -1;
    });

    if(found){
        return cases[found];
    }
    return false;
}