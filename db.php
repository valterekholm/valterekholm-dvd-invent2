<?php

class db
{

	private $user;
	private $pass;
	private $conn;
    private $db_name;

	function __construct($conn_vals)
	{
		$this->user = $conn_vals[2];
		$this->pass = $conn_vals[3];
        $this->db_name = $conn_vals[1];
		$host = $conn_vals[0];
		$options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
		$this->conn = new PDO("mysql:host=localhost;dbname=".$this->db_name.";charset=utf8", $this->user, $this->pass, $options);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //to get error-messages
	}

    function get_db_name(){
        return $this->db_name;
    }

	function getLastInsertId(){
		return $this->conn->lastInsertId();
	}

	function clear()
	{
		$this->conn = null;
	}

	function select_query($sql, $force_lower_case = true)
	{
		error_log("select_query, med $sql");
		if ($force_lower_case) {
			error_log("true");
			$sql = strtolower($sql);
		} else error_log("false");

		$stmt = $this->conn->prepare($sql);
		$stmt = $this->conn->query($sql);

		return $stmt;
	}

	//sql - a query with positional placeholders "?,?,?"
	//values an indexed array
	function insert_query($sql, $values = array(), $force_lower_case = true)
	{
		error_log("insert_query($sql)");
		error_log("values: " . print_r($values, true));
		if ($force_lower_case) {
			error_log("true");
			$sql = strtolower($sql);
		} else error_log("false");


		$stmt = $this->conn->prepare($sql);
		$res = $stmt->execute($values);
		error_log("stmt: " . print_r($stmt, true));
		error_log("res: " . print_r($res, true));
		$error = $stmt->errorInfo()[0];
		$detail = $stmt->errorInfo()[1];
		if ($error == 23000 && $detail == 1062) {
			//error_log("Place is taken");
			throw new DomainException('Dublicate error');
		}

		//error_log("errorInfo: " . print_r($stmt->errorInfo(), true));
		return $stmt->rowCount();
	}

	function update_query($sql, $values = array(), $force_lower_case = true)
	{
		error_log("update_query($sql)");
		error_log("values: " . print_r($values, true));
		if ($force_lower_case) {
			error_log("true");
			$sql = strtolower($sql);
		} else error_log("false");

		$stmt = $this->conn->prepare($sql);
		$res = $stmt->execute($values);
		error_log("stmt: " . print_r($stmt, true));
		error_log("res: " . print_r($res, true));
		return $stmt->rowCount();
	}

	function alter_db_query($sql, $values = array(), $force_lower_case = true)
	{
	}

	/*$fields should be array with key/values like "name" => "attributes" (string => string)
	examples: "name" => "varchar(100)"
	"id" => "int not null"
	"PRIMARY KEY" => "(id)"
	
	*/
	function create_table($table_name, $fields = array())
	{

		$sql = "CREATE TABLE $table_name ";

		if (count($fields) > 0) {
			$sql .= "(";

			$output = implode(', ', array_map(
				function ($v, $k) {
					return sprintf("%s  %s", $k, $v);
				},
				$fields,
				array_keys($fields)
			)); //from https://stackoverflow.com/questions/11427398


			$sql .= $output;
			$sql .= ")";
		}
		echo $sql;
		$stmt = $this->conn->prepare($sql);
		$stmt = $this->conn->query($sql);

		return $stmt;
	}


	function array_to_pdo_params($array)
	{
		$temp = array();
		foreach (array_keys($array) as $name) {
			$temp[] = "`$name` = ?";
		}
		return implode(', ', $temp);
	}

	function read_connection(){
		$fp = fopen("connect.txt", "r+");
		$keys = array("host", "database", "user", "password");
		$vals = array();
		$count = 0;
		while (($line = stream_get_line($fp, 1024 * 1024, "\n")) !== false) {
			//echo $keys[$count] . ":" . $line;
			$line = rtrim($line);
			$vals[]=$line;
			$count-=-1;
		}
		fclose($fp);

		return array_combine($keys,$vals);

	}
}