<!doctype html>
<?php
$jsv = date('Y-m-d-s')
?>
<html>
    <head>
        <title>DVD-fodral med skivor</title>
        <link rel="stylesheet" href="../dvd2.css" />
        <link rel="stylesheet" href="dvdInventAdmin.css" />
        <style>
            .hide_first_col td:first-child, .hide_first_col th:first-child{
                display: none;
            }
            label, input[type="submit"]{
                display: block;
            }
            .admin_message{
                position: fixed;
                height: 100px;
                background-color: #cfc;
                width: 100%;
                padding: 20px;
            }
        </style>
        <script src="admin.js?v=<?=$jsv?>"></script>
        <script src="../dvd2.js?v=<?=$jsv?>"></script>
        <script src="database_structure.js?v=<?=$jsv?>"></script>
    </head>
<body>
    <div id="banner"><a href="<?=$_SERVER["PHP_SELF"]?>"><h1>DVD-invent</h1></a></div>

<div id="leftSide">
<?php
error_reporting(E_ALL);
include "../db.php";
include "edit.php";
include "../print_tables.php";
include "../connection.php";
$table_name = "`case`";
$table_name2 = "`film`";
$table_name3 = "`film_title`";

//reserved js classnames: case
//reserved js id's : cases

$db = new db($conn_vals);

?>
<script>
var fields = []; //table schema, check table `case`
var field; //tbl field, temp variable

//to store table field names
var fields3 = []; //film_title
var fields2 = []; //film...

var tblDesc1 = {};

window.addEventListener("load", function(){
    //formFromFields(fields, ['insert_date', 'id'], document.querySelector('.add'), 'index.php', 'post', 'lägg till fodral');
    loadAll();
    setTimeout(function(){
        if(document.querySelector("input") !== null)
            document.querySelector("input").focus();
    },1100);
});

document.body.onscroll = function(evt){
    //if(this.scrollY > 60) alert("hej");
    var bh = document.querySelector("#banner").getBoundingClientRect().height;
    if(this.scrollY > bh){
        document.body.className = "scrolledDown"; //to adjust #rightSide
    }
    else{
        document.body.className = "";
    }
};

function loadAll(findCaseId){
    console.log("loadAll...");

    selectedCase = allCases = choosenName = null;
    //note: allCases, choosenName are global variables
    document.querySelector("#caseInfo").innerHTML = "";
    document.querySelector("#includedFilms").innerHTML = "";
    document.querySelector("#adminMenu").innerHTML = "";

    getAjax("../ajax_functions.php?all=yes", function(resp){
            //console.log(resp);
            var jsonresp = JSON.parse(resp);
            render(jsonresp, document.querySelector("#cases"));
            setTimeout(initCaseClick, 100);
            setTimeout(function(){
                printAdminMenu(document.querySelector("#adminMenu"), 500);
                <?php if(empty($_GET)){
                    //TODO: abstract out so code gets more beautyfull
                    ?>

                document.querySelector("#alink1").click();//adminlink 1, 'case', to start input of new case
                document.querySelector("#alink5").click();//adminlink 5, '1-disc-case', to start input of new 1-disc-case
                    <?php
                }
                ?>
            },100);
        }
    );

    if(typeof findCaseId !== "undefined"){//after having saved a new case
        console.log("findCaseId is " + findCaseId);
        //try focus on case in ui
        //TODO extract as function


        setTimeout(function(){
            var cases = document.getElementsByClassName("case");
            var len = cases.length;
            console.log(len);

            console.log("function... findCaseId ? (" + findCaseId + ") and len ? (" + len + ")");
            for(var i=0; i<len; i++){
                var ca_ = cases[i];
                console.log(ca_);
                if(ca_.dataset.id == findCaseId){
                    console.log("match for id " + findCaseId);
                    ca_.scrollIntoView({behavior: "smooth", block: "end", inline: "nearest"});
                }
            }
        },2000);//long delay to make it come after printAdminMenu(document.querySelector("#adminMenu"), 500);
    }
    else{
        console.log("findCaseId is undefined");
    }

    shortMessage("Innehåll har laddats om");
}


</script>
<script>
    /*use_table_description*/
<?php

//$sql_desc = "DESCRIBE $table_name";
//$res_desc = $db->select_query($sql_desc);
//this requires root access to mysql/mariadb, for alternative, use fields_1, fields_2, fields_3 (from database_structure.js) instead of fields,fields2,fields3
//and comment out the following 3 lines:
use_table_description($db, $table_name, "fields");
use_table_description($db, $table_name3, "fields3");
use_table_description($db, $table_name2, "fields2");
?>


//console.log(fields_1);
//console.log(fields_2);//these could be used if "use_table_description" can't be used
//console.log(fields_3);
</script>
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
        admin_message("Row was updated, please reload <a href='index.php'>here</a>");
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
        echo "<script>reloadMessage($last_insert_id)</script>";
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
    $sql_add_name = "INSERT INTO " . $db->get_db_name() . ".$table_name3 (name, film_id) VALUES (?, ?)";

    echo $sql_add_name;

    $insert_count = $db->insert_query($sql_add_name, $values, false);

    if($insert_count>0){
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

    $sql_add_film = "INSERT INTO " . $db->get_db_name() . ".$table_name2 (f_short_name) VALUES (?)";


    $insert_count = $db->insert_query($sql_add_film, $values, false);

    if($insert_count>0){
        admin_message("Row inserted, please reload <a href='index.php'>here</a>");
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
                admin_message("Row was deleted, please reload <a href='index.php'>here</a>");
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
<?php
$sql_inventory_info = "SELECT * FROM `inventory_info`";

$res_inventory_info = $db->select_query($sql_inventory_info);

if($res_inventory_info->rowCount() != 1){
    offerInventoryInfoForm();
}

?>
</body>
</html>

