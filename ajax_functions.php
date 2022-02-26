<?php
include "db.php";
include "connection.php";
$db = new db($conn_vals);

$test_sleep_value = 0; //use to test delayments effect


if(!empty($_GET["all"])){ //cases
    //query "dvd1"
    $sql_dvds = "SELECT
    c.id,
    c.c_short_name,
    f.f_short_name,
    c.location,
    f.id as fid,
    ft.id as ftid,
    (SELECT COUNT(*) FROM case_film cf2 WHERE cf2.case_id = c.id) film_count,
    GROUP_CONCAT(name separator ' / ') 'name'
    FROM `case` c
    LEFT JOIN case_film cf
    ON (c.id = case_id)
    LEFT JOIN film f
    ON (film_id = f.id)
    LEFT JOIN film_title ft
    ON (f.id=ft.film_id)
    GROUP BY(c.id) ORDER BY c.id";

    //Denna query med group by c.id ger 'rader' med sammanslagning av flera filmtitlar kallat 'name'
        //Detta i sin tur passar för uppvisningen av "fodral" med alternativa titlar separerade av "/"
        //Det passar dock inte för case info rutan, där fodral med flera filmer får dessa sammanslagna till en enda film

    //liknande med group by f.id

    //Query:n kan dock köras med group by ft.id, då blir uppvisningen av fodral något fel, men case info rutan blir rätt;
    //man får nu alla olika filmer/skivor som sparata rutor som kan tas bort
    //dock slås separata fodral av samma film ihop på oönskat sätt

    //"SELECT c.id, c.c_short_name, f.f_short_name, c.location, f.id as fid, ft.id as ftid, ft.name from `case` c LEFT JOIN case_film cf ON (c.id = cf.case_id) LEFT JOIN film f ON(cf.film_id = f.id) LEFT JOIN film_title ft ON (f.id = ft.film_id)";

    //group by c.id funkar men man kan inte se hur många filmer ett visst fodral innehåller...
    //la till (SELECT COUNT(*) FROM case_film cf2 WHERE cf2.case_id = c.id) film_count,
    error_log($sql_dvds);

    $res_dvds = $db->select_query($sql_dvds);
    $rows_dvds = $res_dvds->fetchAll();

    error_log(count($rows_dvds));

    sleep($test_sleep_value);

    echo json_encode($rows_dvds);

}

if(!empty($_GET["case_info"]) && !empty($_GET["case_id"])){ //case with all it's films
    //query "dvd1"
    $case_id = $_GET["case_id"];
    $sql_dvds = "SELECT
    c.id,
    c.c_short_name,
    f.f_short_name,
    c.location,
    f.id as fid,
    ft.id as ftid,
    (SELECT COUNT(*) FROM case_film cf2 WHERE cf2.case_id = c.id) film_count,
    GROUP_CONCAT(name separator ' / ') 'name'
    FROM `case` c
    LEFT JOIN case_film cf
    ON (c.id = case_id)
    LEFT JOIN film f
    ON (film_id = f.id)
    LEFT JOIN film_title ft
    ON (f.id=ft.film_id)
    WHERE c.id = $case_id
    GROUP BY(f.id) ORDER BY c.id";

    error_log($sql_dvds);

    $res_dvds = $db->select_query($sql_dvds);
    $rows_dvds = $res_dvds->fetchAll();

    error_log(count($rows_dvds));

    echo json_encode($rows_dvds);

}

//for searching, safe placeholders
if(!empty($_GET["names"]) && !empty($_GET["string"])){
    //$string = urldecode($_GET["string"]);
    $string = $_GET["string"];

    $string = str_replace("'s", "´s", $string);
    //echo $string;
    $str2 = $db->quote($string);
    $sql_search_names = "SELECT id, `name` FROM film_title WHERE `name` LIKE('$string%') GROUP BY(`name`)";
    //$sql_search_names = "SELECT id, `name` FROM film_title WHERE `name` LIKE(:search_string)"; //didn't work
    //$values = array($string);
    //Added group by name
    $params = array();
    $params[] = array(":search_string", $string, PDO::PARAM_STR);
    $rows_search_names = $db->select_query2($sql_search_names, $params, false);


    //echo json_encode($rows_search_names->fetchAll());//if using db->select_query
/*    if(! $rows_search_names){
        http_response_code(500);//Internal Server Error
        echo "[inga rader]";
    }
    else{*/
    echo json_encode($rows_search_names);//->fetchAll()
    /*}*/

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

    if(!empty($_GET["orderby"])){
        $order_by = $_GET["orderby"];
        $order_by_sql = "ORDER BY $order_by";
    }
    else{
        $order_by_sql = "";
    }

    $sql_get = "SELECT * FROM `$table_name` $order_by_sql";
    $res_get = $db->select_query($sql_get);
    $rows_get = $res_get->fetchAll();

    echo echo_rows_table_admin($rows_get, true, array(), $table_name, $table_name);
}

if(!empty($_POST["add_case"]) && !empty($_POST["name"]) && !empty($_POST["location"])){
    
    $name = $_POST["name"];
    $location = $_POST["location"];

    error_log("add case with $name at location $location");

    $table_name = "case";
    $sql_add_case = "INSERT INTO `$table_name` (c_short_name, `location`) VALUES (?,?)";

    $count_res_case = $db->insert_query($sql_add_case, array($name, $location));
    $lastInsertId = $db->getLastInsertId();
    error_log("lastInsertId $lastInsertId");
    if($count_res_case>0){
        $resp = makeJsonRespons(true, "Sparade fodral", $count_res_case, $lastInsertId);
        echo $resp;
    }
    else{
        http_response_code(500);
        echo "fel vid sparande av fodral";
    }
}

//add/use film-title and connect it to a case (through a 'film') 
if(!empty($_POST["add_film"]) && !empty($_POST["case_id"]) && !empty($_POST["name"])){
    $case_id = $_POST["case_id"];
    $name = $_POST["name"];

    $name = str_replace("'s","´s",$name); // for some films like ocean's eleven
    //echo "--- $case_id : $name ---";

    //is allready stored?
    $table_name = "film_title";

    $sql_name_is = "SELECT * FROM $table_name WHERE name = '$name'";
    error_log($sql_name_is);
    $res_name_is = $db->select_query($sql_name_is, false);
    $found_id = null;
    if($res_name_is->rowCount()>0){
        //TODO: check if 'film' actually exists
        $row_name_is = $res_name_is->fetch();
        $found_id = $row_name_is["id"];
        $film_id = $row_name_is["film_id"];
        //echo "found name $found_id $film_id";

        //TODO: check if 'film' actually exists

        //insert a 'connectyrow' between the supplied case-id and the found-existing film/filmname
        $sql_connect_film_case = "INSERT INTO case_film VALUES(?,?)";
        error_log($sql_connect_film_case);
        $values = array($case_id, $film_id);
        $count_connect_film_case = $db->insert_query($sql_connect_film_case, $values,false);
        if($count_connect_film_case>0){
            $resp = makeJsonRespons(true, "Satte ihop fodral/film/namn", $count_connect_film_case);
        }
    }
    else{
        $values = array(substr($name,0,11));
        $sql_film = "INSERT INTO film (f_short_name) VALUES (?)";
        error_log($sql_film);
        $count_res_film = $db->insert_query($sql_film, $values, false);
        $lastInsertId = $db->getLastInsertId();
        if($count_res_film>0){
            $sql_name = "INSERT INTO film_title (`name`, film_id) VALUES(?,?)";
            error_log($sql_name);
            $count_res_name = $db->insert_query($sql_name, array($name, $lastInsertId));
            if($count_res_name > 0){
                //now have film and name
                //insert a connectyrow
                $sql_connect_film_case = "INSERT INTO case_film VALUES(?,?)";
                error_log($sql_connect_film_case);
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

//add 1film-case step-1
if(!empty($_POST["add_1film_case"]) && !empty($_POST["name"]) && !empty($_POST["short_name"])){

    if(empty($_POST["location"])){
        echo makeJsonRespons(false,"Location is missing!", 0);
        return;
    }

    $name = urldecode($_POST["name"]);

    $name = str_replace("'s","´s",$name); // for some films like ocean's eleven
    //echo "--- $case_id : $name ---";

    $short_name = urldecode($_POST["short_name"]);

    $location = urldecode($_POST["location"]);

    //make the case
    $case_table_name = "case";
    $sql_add_case = "INSERT INTO `$case_table_name` (c_short_name, `location`) VALUES (?,?)";
    $values = array($short_name, $location);
    $rowcount_add_case = $db->insert_query($sql_add_case, $values, false);

    if($rowcount_add_case == 0){
        echo  makeJsonRespons(false,"Could not insert new case", 0);
        return;
    }
    //else
    $newCaseId = $db->getLastInsertId();

    //is allready stored?
    $title_table_name = "film_title";

    $sql_name_is = "SELECT * FROM $title_table_name WHERE name = '$name'";
    error_log($sql_name_is);
    $res_name_is = $db->select_query($sql_name_is, false);
    $found_id = null;
    if($res_name_is->rowCount()>0){
        $row_name_is = $res_name_is->fetch();
        $found_id = $row_name_is["id"];
        $film_id = $row_name_is["film_id"];

        //check if 'film' exists

        $sql_check_film_exists = "SELECT COUNT(*) co FROM film WHERE id = $film_id";
        $res_check_exists = $db->select_query($sql_check_film_exists, false);
        $co = $res_check_exists->fetch()["co"];

        $film_existed = true;

        if($co == 0){
            //insert film
            $sql_insert_film = "INSERT INTO film (f_short_name) VALUES (?)";
            $values_new_film = array($short_name);
            //TODO: insert and get id, alter film_title->film_id at flag-001

            $film_existed = false;
        }


        //echo "found name $found_id $film_id";
        //flag-001
        if(!$film_existed){
            $countInsertFilm = $db->insert_query($sql_insert_film, $values_new_film, false);
            $newFilmId = $db->getLastInsertId();
            $film_id = $newFilmId;
        }

        //insert a 'connectyrow' between the supplied case-id and the found-existing film/filmname
        $sql_connect_film_case = "INSERT INTO case_film VALUES(?,?)";
        error_log($sql_connect_film_case);
        $values = array($newCaseId, $film_id);
        $count_connect_film_case = $db->insert_query($sql_connect_film_case, $values,false);
        if($count_connect_film_case>0){
            $resp = makeJsonRespons(true, "Satte ihop fodral/film/namn", $count_connect_film_case);
        }
    }
    else{
        $values = array(substr($name,0,11));
        $sql_film = "INSERT INTO film (f_short_name) VALUES (?)";
        error_log($sql_film);
        $count_res_film = $db->insert_query($sql_film, $values, false);
        $lastInsertId = $db->getLastInsertId();
        if($count_res_film>0){
            $sql_name = "INSERT INTO $title_table_name (`name`, film_id) VALUES(?,?)";
            error_log($sql_name);
            $count_res_name = $db->insert_query($sql_name, array($name, $lastInsertId));
            if($count_res_name > 0){
                //now have film and name
                //insert a connectyrow
                $sql_connect_film_case = "INSERT INTO case_film VALUES(?,?)";
                error_log($sql_connect_film_case);
                $values = array($newCaseId, $lastInsertId);
                $count_connect_film_case = $db->insert_query($sql_connect_film_case, $values,false);
                if($count_connect_film_case>0){
                    $resp = makeJsonRespons(true, "Satte ihop fodral/film/namn", $count_connect_film_case, $newCaseId);
                }
            }
        }
        else{
            $resp = makeJsonRespons(false,"Kunde ej spara film/namn",0);
        }
    }
    echo $resp;
}

if(!empty($_POST["add_title"]) && !empty($_POST["name"]) && !empty($_POST["film_id"])){
    $name = $_POST["name"];
    $film_id = $_POST["film_id"];

    $values = array();
    $values[]=$name;
    $values[]=$film_id;

    $sql_add_title = "INSERT INTO " . $db->get_db_name() . ".film_title (name, film_id) VALUES (?, ?)";
    $insert_count = $db->insert_query($sql_add_title, $values, false);

    if($insert_count>0){
        $resp = makeJsonRespons(true, "Sparade namn", $insert_count);
    }
    else{
        $resp = makeJsonRespons(false,"Kunde ej spara film/namn",0);
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

//delete case
if(
    !empty($_GET["delete_case"])
    &&
    !empty($_GET["case_id"])
    ){
    //error_log("delete case: " . $GET["case_id"]);
    
    $del_id = $_GET["case_id"];
    //echo "delete case $del_id";

    $sql_del = "DELETE FROM " . $db->get_db_name() . ".`case` WHERE id = ?";
    $values = array($del_id);
    $row_count =$db->update_query($sql_del, $values, false);
    if($row_count > 0){
        echo makeJsonRespons(true,"Delete case ok", $row_count);
    }
    else{
        echo makeJsonRespons(false,"No case deleted", $row_count);
    }
}

if(!empty($_GET["get_select"]) && !empty($_GET["ref_table"]) && !empty($_GET["ref_field"])){
    $ref_table = $_GET["ref_table"];
    $ref_field = $_GET["ref_field"];

    $order_by = "";
    if(!empty($_GET["orderalphabet"])){
        $order_by = " ORDER BY " . $_GET["orderalphabet"];

    }

    $sql_select = "SELECT * FROM `$ref_table`$order_by";
    $res_select = $db->select_query($sql_select);
    $rows_select = $res_select->fetchAll();

    error_log(count($rows_select));
    //echo $sql_select;
    echo json_encode($rows_select);
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