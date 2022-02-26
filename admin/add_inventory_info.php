<?php

$ub64 = 1;
if(!empty($_POST["use_base64_image"])){
    $ub64 = $_POST["use_base64_image"];
}

$coin = '';
if(!empty($_POST["contact_info"])){
    $coin = $_POST["contact_info"];
}

$imgf = '';
if(!empty($_POST["image_file"])){
    $imgf = $_POST["image_file"];
}

$iloc = '';
if(!empty($_POST["inventory_location"])){
    $iloc = $_POST["inventory_location"];
}

$iname = '';
if(!empty($_POST["inventory_name"])){
    $iname = $_POST["inventory_name"];
}

include "../db.php";

include "../connection.php";

$db = new db($conn_vals);

//allow only one row of inv.info

$sql_delete = "DELETE FROM inventory_info";
$res = $db->update_query($sql_delete, array(), false);

$sql_insert_iinfo = "INSERT INTO `inventory_info` (`inventory_location`, `inventory_image_file`, `images_as_base64`, `inventory_name`, `inventory_contact_info`) VALUES (?,?,?,?,?)";
$values__ = array($iloc, $imgf, $ub64, $iname, $coin);
$result_row_count = $db->insert_query($sql_insert_iinfo, $values__, false);

if($result_row_count > 0){
    echo "<p>Insert success, please <a href='index.php'>return</a> at once</p>";
}
else{
    echo "<p>Seems it didn't work, <a href='index.php'>return to index</a></p>";
}

?>