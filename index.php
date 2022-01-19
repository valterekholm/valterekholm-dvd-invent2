<!doctype html>
<html>
    <head>
        <title>DVD-fodral med skivor</title>
        <style>
            table{
                table-layout: fixed;
                width: 100%;
            }
            th:first-child{
                width: 70%;
            }
            table, td{
                border: 1px dotted gray;
            }
            td{
                word-wrap: break-word;
            }
        </style>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
<body>
    <!--pre-->
<?php
include "db.php";
include "print_tables.php";
include "connection.php";
error_reporting(E_ALL);

$media_files_dir = "media";

$db = new db($conn_vals);

$sql_orderby = "";
if(!empty($_GET["orderby"])){
    $ob = $_GET["orderby"];
    $sql_orderby = " ORDER BY $ob";
}
else{
    $sql_orderby = " ORDER BY filmnamn";
}

$sql_dvds = "SELECT GROUP_CONCAT(name separator ' / ') as filmnamn, c_short_name fodralbeteckning FROM `case` c JOIN case_film cf ON (c.id = cf.case_id) JOIN film f ON (cf.film_id = f.id) JOIN film_title ft ON (f.id = ft.film_id) GROUP BY c.id$sql_orderby";
            //todo: make ao that only case first dvd (if more than one) is listed - so not "group by ft.id" (solved using f.id

//"SELECT DISTINCT COALESCE(name, `case`.`c_short_name`) 'filmnamn' FROM `case` LEFT JOIN `case_film` ON `case_film`.`case_id` = `case`.`id` LEFT JOIN `film` ON `case_film`.`film_id` = `film`.`id` LEFT JOIN film_title ON film.id = film_title.film_id";

$res_dvds = $db->select_query($sql_dvds);
$rows_dvds = $res_dvds->fetchAll();


print_rows_table($rows_dvds, true, array(), array(""));

echo "printed " . date("Y-m-d") . "<br>";

?>

<?php
//info

$sql_inventory_info = "SELECT * FROM `inventory_info`";

$res_inventory_info = $db->select_query($sql_inventory_info);

$row_inventory_info = $res_inventory_info->fetch();

echo "Contact info:" . $row_inventory_info["inventory_contact_info"];
echo "<br>";
$filename = $row_inventory_info["inventory_image_file"];

printBase64Image($media_files_dir, $filename);


?>
<!--/pre--> <!-- 'pre' disables the word-wrap css function -->

<!-- Start of SimpleHitCounter Code -->
<div align="center"><a href="http://www.simplehitcounter.com" target="_blank"><img src="http://simplehitcounter.com/hit.php?uid=348135516&f=16777215&b=0" border="0" height="18" width="83" alt="web counter"></a><br><a href="http://www.simplehitcounter.com" target="_blank" style="text-decoration:none;">web counter</a></div>
<!-- End of SimpleHitCounter Code -->


</body>
</html>

<?php
function printBase64Image($media_files_directory, $image_file_name){
    $path = "$media_files_directory/$image_file_name";
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
    echo "<image src='$base64'><br>";
    echo "<i>Inventory image file: $image_file_name</i>";
    echo "<br>";
}