<!doctype html>
<html>
    <head>
        <title>DVD-fodral med skivor</title>
        <style>
            table, td{
                border: 1px dotted gray;
            }
        </style>
</head>
<body>
    <pre>
<?php
include "db.php";
include "print_tables.php";
include "connection.php";
error_reporting(E_ALL);

$db = new db($conn_vals);

$sql_orderby = "";
if(!empty($_GET["orderby"])){
    $ob = $_GET["orderby"];
    $sql_orderby = " ORDER BY $ob";
}
else{
    $sql_orderby = " ORDER BY filmnamn";
}

$sql_dvds = "SELECT GROUP_CONCAT(name separator ' / ') as filmnamn FROM `case` c JOIN case_film cf ON (c.id = cf.case_id) JOIN film f ON (cf.film_id = f.id) JOIN film_title ft ON (f.id = ft.film_id) GROUP BY c.id$sql_orderby";
            //todo: make ao that only case first dvd (if more than one) is listed - so not "group by ft.id" (solved using f.id

//"SELECT DISTINCT COALESCE(name, `case`.`c_short_name`) 'filmnamn' FROM `case` LEFT JOIN `case_film` ON `case_film`.`case_id` = `case`.`id` LEFT JOIN `film` ON `case_film`.`film_id` = `film`.`id` LEFT JOIN film_title ON film.id = film_title.film_id";

$res_dvds = $db->select_query($sql_dvds);
$rows_dvds = $res_dvds->fetchAll();


print_rows_table($rows_dvds, true, array(), array("id"));

echo "printed " . date("Y-m-d");

?>
</pre>
</body>
</html>
