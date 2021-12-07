<?php

//offer editing forms...

function edit_form_id($id, $row, $ignore_field = "insert_date", $dbt){
    echo "<form action='index.php' method='post'>";

    $label_ = "label";
    $field1_ = "input";
    foreach($row as $key=>$value){
        if($key == $ignore_field){
            echo "<br>";
            continue;
        }



        echo "<$label_>$key</$label_>";
        echo "<br>";
        echo "<$field1_ value='$value' name='$key'>";
        echo "<br>";
    }
    echo "<input name='table' value='$dbt'><br>";
    echo "<input type='submit'>";
    echo "</form>"; 
}

function get_fields_from_rows($rows){
    $fields = array();
    foreach($rows as $row){
        $_link = "index.php?id=" . $row["id"];
        foreach($row as $key=>$value){
            $fields[] = $key;
        }
        break;
    }
    return $fields;
}

function add_form($fields, $ignore_field = array("insert_date", "id")){
    echo "<form action='index.php' method='post'>";
    echo "<h2>Add</h2>";
    foreach($fields as $field){
        if(in_array($field, $ignore_field)){
            continue;
        }
        echo "<span>$field</span>" . "<br>";
        echo "<input name='$field'>" . "<br>";
    }
    echo "<input type='submit'>";
    echo "</form>";
}

//Renders javascript
function use_table_description($db, $table_name, $js_elem){
    //TODO: use js short style initialize of array

    $conn_vals2 = array("localhost","information_schema","root","");
    $db2 = new db($conn_vals2);


    $sql_desc = "DESCRIBE $table_name";
    $result_describe = $db->select_query($sql_desc);

    $tbl_name_stripped = trim($table_name, '`');

    $sql_find_ref1 = "select * from KEY_COLUMN_USAGE kcu where kcu.REFERENCED_TABLE_SCHEMA = 'dvd_invent' and kcu.TABLE_NAME = '$tbl_name_stripped' LIMIT 1";
    //echo "//$sql_find_ref1\n";
    $result_ref = $db2->select_query($sql_find_ref1);
    $rows_ref = $result_ref->fetchAll();

    $ref1 = false;

    foreach($rows_ref as $r){
        //echo "//" . $r["COLUMN_NAME"] . "\n";
        //echo "//" . $r["REFERENCED_TABLE_NAME"] . "\n";
        //echo "//" . $r["REFERENCED_COLUMN_NAME"] . "\n";
        $ref1 = $r;
    }

    $rows = "[";

    $found_ref_index = false;
    $index = 0;

    $rows_describe = $result_describe->fetchAll();

    foreach($rows_describe as $row){
        array_walk_recursive($row, function (&$item, $key) {
            $item = null === $item ? '' : $item; //make 'null' gone
        });

        if($ref1 && $row["Field"] == $ref1["COLUMN_NAME"]){
            //echo "field match at index $index";
            $found_ref_index = $index;
        }
        else{
            //echo "field not match\n";
        }
        $index++;
    }

    if($found_ref_index){
        //echo "\nfound ref\n";
        $rows_describe[$found_ref_index]["Referenced_table"] = $ref1["REFERENCED_TABLE_NAME"];
        $rows_describe[$found_ref_index]["Referenced_field"] = $ref1["REFERENCED_COLUMN_NAME"];
    }

    reset($rows_describe);

    foreach($rows_describe as $row){
        /*array_walk_recursive($row, function (&$item, $key) {
            $item = null === $item ? '' : $item; //make 'null' gone
        });*/
        $rows .= "\n" . json_encode($row) . ",";
        $index++;
    }
    $rows = substr($rows, 0, -1);
    $rows .= "\n]";

    echo "$js_elem = $rows;\n";

    //echo "\n" . json_encode($rows_describe) . "\n";
}