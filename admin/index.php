<!doctype html>
<html>
    <head>
        <title>DVD-fodral med skivor</title>
        <link rel="stylesheet" href="../dvd2.css" />
        <style>
            .hide_first_col td:first-child, .hide_first_col th:first-child{
                display: none;
            }
            label, input[type="submit"]{
                display: block;
            }
        </style>
        <script src="admin.js"></script>
        <script src="../dvd2.js"></script>
    </head>
<body>

<div id="leftSide">
<?php
include "../db.php";
include "edit.php";
include "../print_tables.php";
$table_name = "`case`";
$table_name2 = "`film_title`";
$table_name3 = "`film`";

//reserved js classnames: case
//reserved js id's : cases

$db = new db();
?>
<script>
var fields = []; //table schema, check table `case`
var field; //tbl field, temp variable

var fields2 = []; //film_title
var fields3 = []; //film...

window.addEventListener("load", function(){
    //formFromFields(fields, ['insert_date', 'id'], document.querySelector('.add'), 'index.php', 'post', 'lägg till fodral');
    loadAll();
    setTimeout(function(){
        if(document.querySelector("input") !== null)
        document.querySelector("input").focus();
    },1100);

});


function loadAll(findCaseId){
    console.log("loadAll");

    selectedCase = allCases = choosenName = null;
    document.querySelector("#caseInfo").innerHTML = "";
    document.querySelector("#includedFilms").innerHTML = "";
    document.querySelector("#adminMenu").innerHTML = "";

    getAjax("../ajax_functions.php?all=yes", function(resp){
            console.log(resp);
            var jsonresp = JSON.parse(resp);
            render(jsonresp, document.querySelector("#cases"));
            setTimeout(initCaseClick, 100);
            setTimeout(function(){
                printAdminMenu(document.querySelector("#adminMenu"));
                document.querySelector("#alink1").click();
            },100);
        }
    );

    if(typeof findCaseId != "undefined"){
        //try focus on case in ui
        //TODO extract as function
        var cases = document.getElementsByClassName("case");
        var len = cases.length;

        setTimeout(function(){
            for(var i=0; i<len; i++){
            var ca_ = cases[i];
            if(ca_.dataset.id == findCaseId){

                ca_.scrollIntoView({behavior: "smooth", block: "end", inline: "nearest"});
            }
        }
        },500);

    }

    shortMessage("Innehåll har laddats om");
}


</script>

<?php

//$sql_desc = "DESCRIBE $table_name";
//$res_desc = $db->select_query($sql_desc);
use_table_description($db, $table_name, "fields");
use_table_description($db, $table_name2, "fields2");
use_table_description($db, $table_name3, "fields3");
?>

<?php

if(!empty($_GET["id"]) && empty($_GET["delete"]) && !empty($_GET["table"])){ //edit

    $id = $_GET["id"];

    $dbt = $_GET["table"];

    $sql_dvd = "SELECT * FROM `$dbt` WHERE id = $id";
    echo $sql_dvd;
    $res_dvd = $db->select_query($sql_dvd);
    $row_dvd = $res_dvd->fetch();
    edit_form_id($id, $row_dvd, array("insert_date"), $dbt);
}

if(!empty($_POST["id"]) && !empty($_POST["table"])){ //update
    $id = $_POST["id"];
    $POST = $_POST;
    //echo "Got post for id $id<br>";

    $dbt = $_POST["table"];

    $sql_update = "UPDATE " . $db->get_db_name() . ".`$dbt` SET ";

    $values = array();

    foreach($POST as $key=>$value){
        if($key == "id") continue;
        if($key == "table") continue;

        if(is_numeric($value)){
            //$sql_update .= "$key = $value,";
        }
        else{
            //$sql_update .= "$key = '$value',";
        }

        $sql_update .= "$key = ?,";

        $values[] = $value;
        
    }
    $sql_update = rtrim($sql_update, ',');

    $sql_update .= " WHERE id = ?";
    $values[]=$id;

    echo $sql_update;
    print_r($values);
    $row_count = $db->update_query($sql_update, $values, false);
    if($row_count > 0){
        echo "<p>Row was updated, please reload <a href='index.php'>here</a>";
        //header("refresh: 0");
    }
    else{
        echo "not updated";
    }
}

//add new (case)
if(empty($_POST["id"]) && !empty($_POST["c_short_name"]) && !empty($_POST["location"])){
    //echo "add";
    $values = array();//for sql placeh
    $short_name = $_POST["c_short_name"];
    $location = $_POST["location"];

    $values[]=$short_name;
    $values[]=$location;

    $sql_add = "INSERT INTO " . $db->get_db_name() . ".$table_name (c_short_name, location) VALUES (?,?)";

    //echo $sql_add;

    $insert_count = $db->insert_query($sql_add, $values, false);

    $last_insert_id = $db->getLastInsertId();

    if($insert_count>0){
        //echo "<p>Row inserted, please reload <a href='index.php'>here</a></p>";
        echo "<div style='position: absolute; left: 40%; width:20%'>Insert ok <button style='position: relative; left: -50%' onclick='loadAll($last_insert_id);deleteMe(this.parentNode)'>loadAll</button></div>";
        //header("refresh: 0");
    }
    else{
        echo "insert 0";
    }
}

//add new (film_title)
if(empty($_POST["id"]) && !empty($_POST["name"])){
    echo "add film title";
    $values = array();//for sql placeh
    $name = $_POST["name"];
    $film_id = 0;

    $values[]=$name;


    if(!empty($_POST["film_id"])){
        $film_id = $_POST["film_id"];
    }
    $values[]=$film_id;
    $sql_add_name = "INSERT INTO " . $db->get_db_name() . ".$table_name2 (name, film_id) VALUES (?, ?)";

    echo $sql_add_name;

    $insert_count = $db->insert_query($sql_add_name, $values, false);

    if($insert_count>0){
        //echo "<p>Row inserted, please reload <a href='index.php'>here</a></p>";
        header("refresh: 0");
    }
    else{
        echo "insert 0";
    }
}

//add new (film)
if(empty($_POST["id"]) && !empty($_POST["f_short_name"])){
    echo "add film";
    $values = array();//for sql placeh
    $name = $_POST["f_short_name"];

    $values[]=$name;

    $sql_add_film = "INSERT INTO " . $db->get_db_name() . ".$table_name3 (f_short_name) VALUES (?)";


    $insert_count = $db->insert_query($sql_add_film, $values, false);

    if($insert_count>0){
        echo "<p>Row inserted, please reload <a href='index.php'>here</a></p>";
        //header("refresh: 0");
    }
    else{
        echo "insert 0";
    }
}

//delete
if(!empty($_GET["delete"])){
    if($_GET["delete"] == "yes"){
        if(!empty($_GET["id"])){
            if(!empty($_GET["table"])){
                $dbt = $_GET["table"];
            }
            else $dbt = "unknown";

            $del_id = $_GET["id"];

            $sql_del = "DELETE FROM " . $db->get_db_name() . ".`$dbt` WHERE id = ?";
            $values = array($del_id);
            $row_count =$db->update_query($sql_del, $values, false);
            echo "Delete row count: $row_count";
            if($row_count > 0){
                echo "<p>Row was deleted, please reload <a href='index.php'>here</a></p>";
                //header("refresh: 0");
                header("Location: {$_SERVER['PHP_SELF']}");
            }
        }
    }
}

$sql_dvds = "SELECT * FROM $table_name";
//$sql_dvds = "SELECT * from `case` LEFT JOIN film ON(`case`.id = film.case_id) LEFT JOIN film_title ON (film.id = film_title.film_id)";
$res_dvds = $db->select_query($sql_dvds);
$rows_dvds = $res_dvds->fetchAll();

?>
<div id="tables">

    <?php

    //print_table_menu("#tables");

    //print_rows_table_admin($rows_dvds, true, array(), "DVD-cases");
?>

</div>

<div class="add">
</div>
</div>


<div id="rightSide">
    <div id="caseInfo"></div>
    <div id="includedFilms"></div>
    <div id="adminMenu"></div>
</div>

<div id="cases"></div>
</body>
</html>
