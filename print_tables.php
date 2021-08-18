<?php

function print_rows_table($rows, $html_table = true, $attr = array(), $ignore_field = array("id")){

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

    echo "<$table_";

    if(count($attr)>0){
        foreach($attr as $key=>$val){
            echo " $key='$val'";
        }
    }

    echo ">";

    $count = 0;
    $ignore_index = array();
    foreach($rows as $row){
        echo "<$row_>";
        if($count == 0){
            $count_index = 0;
            foreach($row as $key=>$value){
                
                //if should ignore
                if(in_array($key, $ignore_field)){
                    $ignore_index[] = $count_index;
                }
                else{
                    echo "<$hcell_>$key</$hcell_>";
                }

                $count_index++;
            }


            reset($row);

            echo "</$row_>\n<$row_>";
        }

        $count_index_ = 0;
        foreach($row as $cell){
            if(!in_array($count_index_, $ignore_index)){
                echo "<$cell_>$cell</$cell_>";
            }
            
            $count_index_++;
        }
        $count++;

        echo "</$row_>";
    }
    echo "</$table_>";
}

function print_table_menu($dom_target){
    //use before other table function

    //link names and sql-table names
    $links = ["cases"=>"case", "film titles"=>"film_title"];

    echo "<nav>";
    echo "<ul>";

    foreach($links as $key=>$val){
        echo "<a href='#' onclick=''>";
        echo $key;
        echo "</a>";
    }


    echo "</ul>";
    echo "</nav>";
}

function print_rows_table_admin($rows, $html_table = true, $attr = array(), $title=""){

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

        $_link = "index.php?id=" . $row["id"];
        $_link_delete = "index.php?delete=yes&id=" . $row["id"];
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