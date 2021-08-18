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

$db = new db();

$sql_dvds = "SELECT `name` as filmnamn, COUNT(c.id) AS antal FROM `case` c JOIN case_film cf ON (c.id = cf.case_id) JOIN film f ON (cf.film_id = f.id) JOIN film_title ft ON (f.id = ft.film_id) GROUP BY ft.id";
//"SELECT DISTINCT COALESCE(name, `case`.`c_short_name`) 'filmnamn' FROM `case` LEFT JOIN `case_film` ON `case_film`.`case_id` = `case`.`id` LEFT JOIN `film` ON `case_film`.`film_id` = `film`.`id` LEFT JOIN film_title ON film.id = film_title.film_id";
//"SELECT * FROM `case`";
$res_dvds = $db->select_query($sql_dvds);
$rows_dvds = $res_dvds->fetchAll();


print_rows_table($rows_dvds, true, array(), array("id"));

?>
</pre>
</body>
</html>
