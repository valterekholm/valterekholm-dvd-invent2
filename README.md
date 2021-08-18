# dvd-invent2

A database system for DVD:s, where DVD:s are packet in moving-boxes, with a many-to-many relation between cases and films. Have support for same films with several versions/languages/titles (same film having different names).
The coding is without any libraries or frameworks. So far the UI have swedish english blended.

A file should the user add, "connection.php", that should have the following values (db-related) in a 0-indexed array $conn_vals = ["hostname","database-name","username","password"]