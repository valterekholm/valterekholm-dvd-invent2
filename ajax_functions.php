<?php
include "db.php";
$db = new db();

if(!empty($_GET["all"])){ //cases
    //query "dvd1"
    $sql_dvds = "SELECT
    c.id,
    c.c_short_name,
    f.f_short_name,
    c.location,
    f.id as fid,
    ft.id as ftid,
    GROUP_CONCAT(name separator ' / ') 'name'
    FROM `case` c
    LEFT JOIN case_film cf
    ON (c.id = case_id)
    LEFT JOIN film f
    ON (film_id = f.id)
    LEFT JOIN film_title ft
    ON (f.id=ft.film_id)
    GROUP BY(f.id) ORDER BY c.id";
    //"SELECT c.id, c.c_short_name, f.f_short_name, c.location, f.id as fid, ft.id as ftid, ft.name from `case` c LEFT JOIN case_film cf ON (c.id = cf.case_id) LEFT JOIN film f ON(cf.film_id = f.id) LEFT JOIN film_title ft ON (f.id = ft.film_id)";


    $res_dvds = $db->select_query($sql_dvds);
    $rows_dvds = $res_dvds->fetchAll();

    echo json_encode($rows_dvds);

}

//for searching
if(!empty($_GET["names"]) && !empty($_GET["string"])){
    $string = $_GET["string"];
    //echo $string;
    $sql_search_names = "SELECT id, `name` FROM film_title WHERE `name` LIKE('$string%')";
    $res_search_names = $db->select_query($sql_search_names);
    $rows_search_names = $res_search_names->fetchAll();

    echo json_encode($rows_search_names);
}

if(!empty($_GET["filmnames"])){
    $sql_search_names = "SELECT * FROM film_title";
    $res_search_names = $db->select_query($sql_search_names);
    $rows_search_names = $res_search_names->fetchAll();

    echo json_encode($rows_search_names);
}

if(!empty($_GET["filmandnames"])){
    $sql_search_names = "SELECT f.id, f_short_name, GROUP_CONCAT(name SEPARATOR ' / ') 'name', COUNT(name) 'name-count' FROM film f JOIN film_title ft ON (f.id=ft.film_id) GROUP BY(f.id);";
    $res_search_names = $db->select_query($sql_search_names);
    $rows_search_names = $res_search_names->fetchAll();

    echo json_encode($rows_search_names);
}

if(!empty($_GET["tables"]) && !empty($_GET["tablename"])){
    $table_name = $_GET["tablename"];
    $sql_get = "SELECT * FROM `$table_name`";
    $res_get = $db->select_query($sql_get);
    $rows_get = $res_get->fetchAll();

    echo json_encode($rows_get);
}

if(!empty($_GET["echotables"]) && !empty($_GET["tablename"])){
    $table_name = $_GET["tablename"];
    $sql_get = "SELECT * FROM `$table_name`";
    $res_get = $db->select_query($sql_get);
    $rows_get = $res_get->fetchAll();

    echo echo_rows_table_admin($rows_get, true, array(), $table_name, $table_name);
}

if(!empty($_POST["add_film"]) && !empty($_POST["case_id"]) && !empty($_POST["name"])){
    $case_id = $_POST["case_id"];
    $name = $_POST["name"];
    //echo "--- $case_id : $name ---";

    //is allready stored?
    $table_name = "film_title";

    $sql_name_is = "SELECT * FROM $table_name WHERE name = '$name'";
    $res_name_is = $db->select_query($sql_name_is, false);
    $found_id = null;
    if($res_name_is->rowCount()>0){
        $row_name_is = $res_name_is->fetch();
        $found_id = $row_name_is["id"];
        $film_id = $row_name_is["film_id"];
        //echo "found name $found_id $film_id";

        $sql_connect_film_case = "INSERT INTO case_film VALUES(?,?)";
        $values = array($case_id, $film_id);
        $count_connect_film_case = $db->insert_query($sql_connect_film_case, $values,false);
        if($count_connect_film_case>0){
            $resp = makeJsonRespons(true, "Satte ihop fodral/film/namn", $count_connect_film_case);
        }
    }
    else{
        $values = array(substr($name,0,11));
        $sql_film = "INSERT INTO film (f_short_name) VALUES (?)";
        $count_res_film = $db->insert_query($sql_film, $values, false);
        $lastInsertId = $db->getLastInsertId();
        if($count_res_film>0){
            $sql_name = "INSERT INTO film_title (`name`, film_id) VALUES(?,?)";
            $count_res_name = $db->insert_query($sql_name, array($name, $lastInsertId));
            if($count_res_name > 0){
                //have film and name
                $sql_connect_film_case = "INSERT INTO case_film VALUES(?,?)";
                $values = array($case_id, $lastInsertId);
                $count_connect_film_case = $db->insert_query($sql_connect_film_case, $values,false);
                if($count_connect_film_case>0){
                    $resp = makeJsonRespons(true, "Satte ihop fodral/film/namn", $count_connect_film_case);
                }

            }
        }
        else{
            $resp = makeJsonRespons(false,"Kunde ej spara film/namn",0);
        }
    }
    echo $resp;

}

if(!empty($_GET["disconnect"]) && !empty($_GET["case"]) && !empty($_GET["film"])){
    $case = $_GET["case"];
    $film = $_GET["film"];

    $sql_disconnect = "DELETE FROM case_film WHERE case_id = ? AND film_id = ?";

    $values = array($case, $film);
    $row_count =$db->update_query($sql_disconnect, $values, false);
    //echo "Delete row count: $row_count";
    if($row_count > 0){
        echo makeJsonRespons(true,"Disconnect finished ok", $row_count);
    }
    else{
        echo makeJsonRespons(false,"No disconnect", $row_count);
    }
}

function makeJsonRespons($success, $message, $affected_rows, $id = 0){
    $resp = array("success"=>$success, "message"=>$message, "affected_rows"=>$affected_rows, "id"=>$id);
    return json_encode((object)$resp);
}



function echo_rows_table_admin($rows, $html_table = true, $attr = array(), $title="", $dbtable){

    //echo "<script>alert('rows: " . print_r($rows, true) . "')</script>";

    if(count($rows) == 0){
        echo "No rows in result";
        return;
    }

    if($html_table){
        $table_ = "table";
        $row_ = "tr";
        $cell_ = "td";
        $hcell_ = "th";
    }
    else{ //div
        $table_ = $row_ = $cell_ = $hcell_ = "div";
    }

    echo "<h1>$title</h1>";

    echo "<$table_";

    if(count($attr)>0){
        foreach($attr as $key=>$val){
            echo " $key='$val'";
        }
    }

    echo ">";


    $count = 0;
    foreach($rows as $row){

        $_link = "index.php?table=$dbtable&id=" . $row["id"];
        $_link_delete = "index.php?delete=yes&table=$dbtable&id=" . $row["id"];
        echo "<$row_>";
        if($count == 0){
            foreach($row as $key=>$value){
                echo "<$hcell_>$key</$hcell_>";
            }
            echo "<$hcell_>edit</$hcell_>"; //adm
            echo "<$hcell_>delete</$hcell_>";
            reset($row);

            echo "</$row_>\n<$row_>";
        }

        foreach($row as $cell){
            echo "<$cell_>$cell</$cell_>";
        }
        echo "<$cell_><a href='$_link'>-----</a></$cell_>";
        echo "<$cell_><a href='$_link_delete'>xxxxx</a></$cell_>";
        $count++;

        echo "</$row_>";
    }
    echo "</$table_>";
}
?>