<?php

class Database{
		private static $instance;
		private static $connection;
		private static $query;
		private static $result;
		private static $row;
		private static $resultArray;
		private static $finalResult;
		private static $finalReturn;
		private static $path;
		private static $useColumn;
		private static $useValue;
	/*
	 *** Function for calling the Connection class and connecting to the database ***
	 * It returns database connection to the static $connection inside this class
	 */
	public static function connect(){
		self::$instance = Connection::instance();
		self::$connection = self::$instance->connect();
	}
	
	/*
	 *** Function for getting the data from the database ***
	 * Parameters:
	 ** $table (from where to get data. It is smart to input here also a database name)
	 ** $what (what to get from the table. Example: "*", "city_id, city_name", etc.)
	 ** $where (here we can put something like: "WHERE user_id = '1'" or "WHERE city_name LIKE '%sarajevo%'")
	 ** $limit (if we want to limit the number of rows we get, just something like: "LIMIT 2")
	 ** $json (it can be true, or false. True means we want json output, and false means we want regular PHP array output)
	 ** $return (it can be "return" or file name. Return means we will just return choosen output, and file name means we will save choosen output to the file for later use)
	 * Returns:
	 ** 101 - No such data in database
	 ** Data array (in json or PHP array format (as we wish))
	 */
	public static function get($table, $what, $where = null, $limit = null, $json = true, $return = "return"){
		# Connect to the database
		self::connect();
		# Build query from the parameters
		$query = "SELECT " . $what . " FROM " . $table . " " . $where . " " . $limit; //Debugging: return self::$query;
		# Execute query
		$result = self::$connection->query($query);
		# If we have results, then proceed
		if($result && $result->num_rows > 0){
			# Put everything inside $resultArray[]
			while($row = $result->fetch_assoc()){
				$resultArray[] = $row;	
			}
			
			# Check if json is true or false
			$finalResult = ($json) ? json_encode($resultArray) : $resultArray;
			# Clone array for returning
			$finalReturn = $finalResult;
			# Check return parameter (if is not there, then save to the file, else just return)
			if($return != "return"){
				# Get the path of file where we need to save our data array
				$path = Settings::get('home_path') . $return;
				# Check is our data array json or not. If it is not, then serialize it
				if(is_array($finalResult)){
					$finalResult = serialize($finalResult);
				}
				# Put content in the given file and path
				file_put_contents($path, $finalResult);
				
				return $finalReturn;
			}
			
			# If return parameters equals "return" then just return json or array
			return $finalReturn;
			
			
		}else{
		# If we don't have results return error (we don't have results)
			$finalReturn['code'] = "101";
			return $finalReturn;
		}
	}
	
	/*
	 *** Function for setting/inserting data to the database ***
	 * Parameters:
	 ** $table (in which table we need to insert data)
	 ** $columns (it can be string with formatted columns names, or array with columns as keys and values as value of the array)
	 ** $values (if $columns is formatted column names string, then $values is formatted values string)
	 * Returns:
	 ** 100 - Data inserted succesfully
	 ** 102 - Wrong parameters sent
	 ** 103 - SQL related error
	 */
	public static function set($table, $columns, $values = null){
		# Connect to the database
		self::connect();
		# Check if parameters are in two strings or in one array
		if(is_array($columns)){
			# Create variables for columns and values
			$useColumn = "";
			$useValue = "";
			# If we have an array of key=>value, then we need to get two strings from it: one with columns names, and one with values
			foreach($columns as $key => $value){
				$useColumn .= $key . ",";
				$useValue .= "'" . $value . "',";
			}
			# Delete last "," in both columns and values
			$useColumn = substr($useColumn, 0, -1);
			$useValue = substr($useValue, 0, -1);
			# Build query
			$query = "INSERT INTO " . $table . " (" . $useColumn . ") VALUES (" . $useValue . ")";
		}else if(is_string($columns) && is_string($values)){
			#Build query
			$query = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $values . ")";
		}else{
			# Error: Wrong parameters sent
			$finalReturn['code'] = "102";
			return $finalReturn;
		}
		
		# Try to insert the data
		if(self::$connection->query($query) === TRUE){
			# Successfully inserted
			$finalReturn['code'] = "100";
			return $finalReturn;
		}else{
			# Error: We have a SQL error, please try again.
			$finalReturn['code'] = "103";
			return $finalReturn;
		}
		
	}
	
	/*
	 *** Function for updating data in the database ***
	 * parameters:
	 ** $table (where to update data)
	 ** $data (array of columns as key and values as array value)
	 ** $where (which row to update)
	 * returns:
	 ** 100 - Data successfuly updated
	 ** 103 - SQL related error
	 */
	public static function update($table, $data, $where){
		# Connect to the database
		self::connect();	
		# Define variable
		$useValue = "";
		# Transform $data array to the nice format string for SQL
		foreach($data as $key => $value){
			$useValue .= $key . "='" . $value . "', ";
		}
		# Delete last comma in the $useValue
		$useValue = substr($useValue, 0, -2);
		# Build query
		$query = "UPDATE " . $table . " SET " . $useValue . " WHERE " . $where; // Debugging: return self::$query;
		# Try to insert the data
		if(self::$connection->query($query) === TRUE){
			# Successfully updated
			$finalReturn['code'] = "100";
			return $finalReturn;
		}else{
			# Error: We have a SQL error, please try again.
			$finalReturn['code'] = "103";
			return $finalReturn;
		}
		
	}
	
	/*
	 *** Function for deleting the data ***
	 * parameters:
	 ** $table (in which table to delete data)
	 ** $where (in which row to delete data)
	 * returns:
	 ** 100 - Sucessfully deleted
	 ** 103 - SQL related error
	 */
	public static function delete($table, $where){
		# Connect to the database
		self::connect();	
		# Building query
		$query = "DELETE FROM " . $table . " WHERE " . $where; // Debugging: return self::$query;
		# Try to delete the data
		if(self::$connection->query($query) === TRUE){
			# Successfully deleted
			return $finalReturn['code'] = "100";
		}else{
			# Error: We have a SQL error, please try again.
			return $finalReturn['code'] = "103";
		}
	}
	
	/*
	 *** Function for updating status in the database ***
	 * parameters:
	 ** $table (where to update status)
	 ** $status (string which contains status column and status value - nice formatted)
	 ** $where (which row to update)
	 * returns:
	 ** 100 - Status successfuly updated
	 ** 103 - SQL related error
	 */
	public static function status($table, $status, $where){
		# Connect to the database
		self::connect();	
		# Build query
		$query = "UPDATE " . $table . " SET " . $status . " WHERE " . $where; // Debugging: return self::$query;
		# Try to insert the data
		if(self::$connection->query($query) === TRUE){
			# Successfully updated
			$finalReturn['code'] = "100";
			return $finalReturn;
		}else{
			# Error: We have a SQL error, please try again.
			$finalReturn['code'] = "103";
			return $finalReturn;
		}
		
	}
	
	/*
	 *** Function for modifying data keys, as they need to be prepared for database input ***
	 * parameters:
	 ** $data (key-value array)
	 ** $fix (prefix or sufix, which we need to add at keys)
	 ** $place (where to place fix - prefix or sufix; true means prefix, false means sufix)
	 * returns:
	 ** Modified data array
	 */
	public static function modify($data, $fix, $place = true){
		# Counter and foreach loop to go through whole array
		$i = 0;
		foreach($data as $key=>$value){
			if($place){
				$dataModified[$fix . "_" . $key] = $value;
			}else{
				$datModified[$key . "_" . $fix] = $value;
			}	
			$i++;
		}
		# Return modified data
		return $dataModified;
	}
	
	/*
	 *** Function for cleaning database datetime ***
	 * We need this function because we store datetime in the database
	 * as a string pattern: date + %20 + time, so we need to get rid
	 * of the %20, and replace it with regular space for the checks
	 * parameters:
	 ** $datetime
	 * returns:
	 ** Clean datetime
	 */
	public static function cleanDateTime($datetime){
		# Explode datetime by "%20"/space
		$datetimeCleared = explode("%20", $datetime);
		# Concatenate date and time rows
		$datetimeCleared = $datetimeCleared[0] . " " . $datetimeCleared[1];
		# Return datetime
		return $datetimeCleared;
	}
	
	/*
	 *** Function for cleaning user datetime ***
	 * Same as the cleanDateTime, but with additional check,
	 * because sometimes "%20" doesn't occure, because these 
	 * parameters are provided by link (POST or GET)
	 * parameters:
	 ** $datetime
	 * returns:
	 ** Cleaned datetime
	 */
	public static function cleanUserDateTime($datetime){
		# Check if '%20' occurs
		$datetimeSearch = strpos($datetime, '%20');	
		# If occurs the clean, else just return $datetime
		if($datetimeSearch != false){
			# Explode datetime by "%20"
			$datetimeCleared = explode('%20', $datetime);
			# Concatenate date and time rows
			$datetimeCleared = $datetimeCleared[0] . " " . $datetimeCleared[1];
			# Return cleaned datetime
			return $datetimeCleared;
		}else{
			# Return datetime because '%20' is not there
			return $datetime;
		}
	}
	
}

/*** Testing: passed ***/

/* Database::get()
include "Connection.class.php";
include "Settings.class.php";
echo Database::get('magicalb_dataman.city', '*', "", "LIMIT 3", true, "return");
echo "<br><br><br>";
var_dump(Database::get('magicalb_dataman.bih', '*', "", "LIMIT 1", false, "return"));
*/

/*
include "Connection.class.php";
include "File.class.php";
include "Settings.class.php";
var_dump (Database::set('magicalb_dataman.test', 'test_id, test_name, test_desc', "NULL, 'Bosna', 'Ovo je Bosnia, ovo je Bosnia!'"));
var_dump (Database::set('magicalb_dataman.test', ["test_id" => NULL, "test_name" => "Bosnia Bro", "test_desc" => "I ovo je Bosnia!"]));
*/

/*
include "Connection.class.php";
echo Database::update('magicalb_dataman.test', ["test_name" => "Bosnia BroBroBro"], "test_id = '1'");
*/

/*
include "Connection.class.php";
echo Database::delete('magicalb_dataman.test', "test_id = '1'");
*/
/*
include "Connection.class.php";
echo Database::status('magicalb_dataman.test', "test_status = '10'", "test_id = '0'");
*/