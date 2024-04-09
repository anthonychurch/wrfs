<?php
class sql
{
	public $host,$port,$user,$password,$database;
	protected $conn;
	
	function __construct($hst,$prt,$usr,$pssword)
	{
		$this->host = $hst;
		$this->port = $prt;
		$this->user = $usr;
		$this->password = $pssword;
		//$this->database = $databse;
	}
	function connect($database)
	{
		// Create connection
		//$msge;
		$this->conn = mysqli_connect($this->host,$this->user,$this->password,$database);
		// Check connection
		/*if (mysqli_connect_errno())
			{
				echo "class_sql_utilities :: "."Failed to connect to MySQL: " . mysqli_connect_error() . "<br>";
				$msge = "class_sql_utilities :: "."Failed to connect to MySQL: " . mysqli_connect_error() . "<br>";
			}
		 else
			{
				echo "class_sql_utilities :: "."Connected " . "<br>";
				 "class_sql_utilities :: "."Connected " . "<br>";
			}*/
		return $this->conn;
	}
	function query($query)
	{
		$result = mysqli_query($this->conn,$query);
		return $result;
	}
	function disconnect($database)
	{
		mysqli_close($this->conn);
	}
}
?>