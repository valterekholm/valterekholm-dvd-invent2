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

function use_table_description($db, $table_name, $js_elem){

    $sql_desc = "DESCRIBE $table_name";
    $result_describe = $db->select_query($sql_desc);
    echo "<script>/*(use table description)*/\n";
    foreach($result_describe->fetchAll() as $row){
        array_walk_recursive($row, function (&$item, $key) {
            $item = null === $item ? '' : $item; //make 'null' gone
        });

        foreach($row as $key=>$value){
            //echo "$key : $value<br>";
            //echo "field['" . $key . "'] = '$value';";
        }
        echo "$js_elem.push(".json_encode($row).");\n";
    }
    echo "</script>\n";
}