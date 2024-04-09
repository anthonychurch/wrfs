<?php
/**
 * A collection of functions that are used for connecting and manipulating MySQL Databases.
 *
 * A collection of functions that are used for connecting and manipulating MySQL Databases.
 *
 * @category   MySQL
 * @package    PackageName
 * @author     Anthony Church <aw_church@yahoo.com.au>
 * @author     NA
 * @copyright  2015 Anthony Church
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    1.0.0
 * @link       NA
 * @see        NA
 * @since      File available since Release 1.0.12
 * @deprecated NA
 */


class sql
{
	public $host,$port,$user,$password;
	protected $conn;
	
	function __construct($hst,$prt,$usr,$pssword)
	{
		$this->host = $hst;
		$this->port = $prt;
		$this->user = $usr;
		$this->password = $pssword;
	}
	function connect($database)
	{
		// Create connection
		$this->conn = mysqli_connect($this->host,$this->user,$this->password,$database);
		
		// Check connection
		$success;
		if (mysqli_connect_errno())
			{
				$success = array(False,"class_sql_utilities :: "."Failed to connect to MySQL: " . mysqli_connect_error() . "<br>");
			}
		 else
			{
				$success = array(True,"class_sql_utilities :: "."Connected " . "<br>");
			}
		return $success;
	}

	function query($query)
	{
		$result = mysqli_query($this->conn,$query);
		return $result;
	}
	function echoArray($a)
	{
		for ($n = 0; $n < sizeof($a); $n++)
		{
			echo "class_sql_utilities :: "."echoArray[".$n."]: ".$a[$n]."<br>";
		}
	}
	//EXAMPLE : SELECT MAX(ID) FROM Customers;
	function getMaxColumnValue($column,$table)
		{
			//SELECT MAX(paging_id) AS maxnum FROM winmalee_facebook.paging;
			$queryMAX = "SELECT MAX(" . $column . ") AS maxnum FROM " . $table;
			$result = mysqli_query($this->conn,$queryMAX);
			$row = mysqli_fetch_assoc($result);
			return $row['maxnum'];
		}
	function get_next_free_number($id)
		{
			if($id == Null)
				{
					$id = 1;
				}
			else
				{
					$id += 1;//get first free row
				}
			return $id;
		}
		
	function un_null_number($id,$value)
		{
			if($id == Null)
				{
					$id = $value;
				}
			return $id;
		}
	//This is reduntant and needs to be phased out
	function getQuery($query)
		{
			$result = mysqli_query($this->conn,$query);
			return $result;
		}
	//Legacy for  gettRow($result) support Needs to be phased out. Replaced by getFirstRow($result)
	function getRow($result)
		{
			$row = mysqli_fetch_assoc($result);
			return $row;
		}
	function getFirstRow($result)
		{
			$row = mysqli_fetch_assoc($result);
			return $row;
		}
	function getAllRows($query)
		{
			$rows;
			$count = 0;
			$result = mysqli_query($this->conn,$query);
			while($row = $result->fetch_row()) 
				{
					if(sizeof($row) < 1)
						{
							$rows[$count] = $row[0];
						}
					else
						{
							$rows[$count] = $row;
						}
					$count+=1;
				}
			return $rows;
		}
	function insertValues($table,$columnArray,$valueArray)
		{
			$column;
			for ($i = 0; $i < sizeof($columnArray); $i++) 
				{
					$column .= $columnArray[$i] . ',';
				}
			$column	= rtrim($column, ",");
			$value;
			for ($i = 0; $i < sizeof($valueArray); $i++) 
				{
					echo "valueArray[".$i."]: ".$valueArray[$i]."<br>";
					$value .= "'".$valueArray[$i]."'" . ',';
				}
			$value	= rtrim($value, ",");
			$query = "INSERT INTO ". $table ."(".$column.")VALUES (".$value.")";
			$result = $this->query($query);
			return $result;
		}
	function insertValues_kv($table,$array,$print)
		{
			$str_key;
			$str_value;
			foreach ($array as $key => $value)
				{
				   if (is_array($value))
						{
							echo "ERROR :: insertValues_kv() :: ".$key." Value Is an Array<br>";
							break;
						}
					else
						{
							if($value != Null)
								{
									$str_key .= '`'.$key.'`' . ',';
									if( is_int ( $value ) )// or ($value==Null) )
										{
											$str_value .= $value . ',';
										}
									else
										{
											$str_value .= "'".$value."'" . ',';
										}
								}
						}
				}
			$str_key = substr($str_key, 0, -1); 
			$str_value = substr($str_value, 0, -1);
			$query = "INSERT INTO ". $table ."(".$str_key.")VALUES (".$str_value.")";
			$result = Null;
			if($print)
				{
					$result = $this->query($query);
				}
			else
				{
					file_put_contents("_DELETE_PHP__quey.txt", $query);
				}
			return $result;
		}
		
	function updateValues_kv($table,$array,$where,$print)
		{
			$str_key;
			$str_value;
			$str_set = '';
			$str_where = '';
			foreach ($array as $key => $value)
				{
				   if (is_array($value))
						{
							echo "Is an Array<br>";
							break;
						}
					else
						{
							$str_key = '`'.$key.'`';
							if( is_int ( $value ) )// or ($value==Null) )
								{
									$str_value = $value . ',';
								}
							else
								{
									$str_value = "'".$value."'";
								}
						}
				   
				   if ($key == $where)
						{
							$str_value = substr($str_value, 0, -1); 
							$str_where = " WHERE ".$str_key." = ".$str_value;
						}
					else
						{
							
							$str_set = $str_set.$str_key." = ".$str_value . ',';
							echo "updateValues_kv :: "."str_set = ".$str_set."<br>";
						}
				}
			$str_set = substr($str_set, 0, -1); 
			$query = "UPDATE ". $table ." SET ".$str_set.$str_where;
			$result = Null;
			if($print)
				{
					$result = $this->query($query);
				}
			else
				{
					file_put_contents("_DELETE_PHP__quey.txt", $query);
				}
			return $result;
		}
	/**
	* This method uses an Array of multiple values that are mapped to a constant set of fields
	* NOTE: $primKeyID is the primary key index which can be ignored by having it = -1
	**/
	function insertValuesMultiple($table,$primKeyID,$columnArray,$array)
		{
			$result;
			if($primKeyID == Null)
				{
					$primKeyID = 1;
				}
			//$i needs to equal $primKeyID-1 becasue SQL column id start at 1 where php arrays start at 0
			for ($i = $primKeyID-1; $i < sizeof($array); $i++) 
				{
					$valueArray = array();
					if($primKeyID != -1)
						{
							array_push($valueArray,$primKeyID);
							$primKeyID += 1;
						}
					for ($j = 0; $j < sizeof($array[$i]); $j++)
						{
							array_push($valueArray,$array[$i][$j]);//Add ID value to SQL row
						}
					if(sizeof($columnArray) == sizeof($valueArray))
						{
							$result = $this->insertValues($table,$columnArray,$valueArray);
						}
					else
						{
							echo "class_sql_utilities :: "."ERROR :: Size of columnArray != valueArray<br>";
						}
					
				}
			return $result;
		}
	function createTable($colValArray,$fkArray,$table)
		{
		$query = "CREATE TABLE IF NOT EXISTS ".$table."(";
		//Get Value and data types
		for ($i = 0; $i < sizeof($colValArray); $i++) 
			{
				$query .= $colValArray[$i][0]." ".$colValArray[$i][1].", ";
			}
		//Get the Foreign Key Values if they exist	
		
		if($fkArray != Null)
			{
				//Add Foriegn Keys
				$query_ConName = "CONSTRAINT ";
				$query_ConFK = " FOREIGN KEY (";
				$query_ConRef = ") REFERENCES ";
				$query_ConEnd = ") ON DELETE SET NULL ON UPDATE CASCADE,";
				foreach($fkArray as $p)
					{
						$query_ConName .= $p[0];//Name
						$query_ConFK .= $p[1];
						$query_ConRef .= $p[3]."(".$p[2];
						$query .= $query_ConName.$query_ConFK.$query_ConRef.$query_ConEnd;

						//Reset vars
						$query_ConName = "CONSTRAINT ";
						$query_ConFK = " FOREIGN KEY (";
						$query_ConRef = ") REFERENCES ";
						$query_ConEnd = ") ON DELETE SET NULL ON UPDATE CASCADE,";/**/
					}
			}
		$query = rtrim($query,", ");
		$query .= ")";
		$result = array($this->query($query),$query);
		return $result;
	}	
	function disconnect($database)
		{
			mysqli_close($this->conn);
		}

}
?>