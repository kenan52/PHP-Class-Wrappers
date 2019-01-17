<?php
/**
  *@author Amar Bešlija (Alfa Solutions)
  */
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
		self::$query = "SELECT " . $what . " FROM " . $table . " " . $where . " " . $limit; // Debugging: echo self::$query;
		# Execute query
		self::$result = self::$connection->query(self::$query);
		# If we have results, then proceed
		if(self::$result && self::$result->num_rows > 0){
			# Put everything inside $resultArray[]
			while(self::$row = self::$result->fetch_assoc()){
				self::$resultArray[] = self::$row;	
			}
			
			# Check if json is true or false
			self::$finalResult = ($json) ? json_encode(self::$resultArray) : self::$resultArray;
			# Clone array for returning
			self::$finalReturn = self::$finalResult;
			# Check return parameter (if is not there, then save to the file, else just return)
			if($return != "return"){
				# Get the path of file where we need to save our data array
				self::$path = Settings::get('home_path') . $return;
				# Check is our data array json or not. If it is not, then serialize it
				if(is_array(self::$finalResult)){
					self::$finalResult = serialize(self::$finalResult);
				}
				# Put content in the given file and path
				file_put_contents(self::$path, self::$finalResult);
				
				return self::$finalReturn;
			}
			
			# If return parameters equals "return" then just return json or array
			return self::$finalReturn;
			
			
		}else{
		# If we don't have results return error (we don't have results)
			return self::$finalReturn['error'] = "101";
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
			# If we have an array of key=>value, then we need to get two strings from it: one with columns names, and one with values
			foreach($columns as $key => $value){
				self::$useColumn .= $key . ",";
				self::$useValue .= "'" . $value . "',";
			}
			# Delete last "," in both columns and values
			self::$useColumn = substr(self::$useColumn, 0, -1);
			self::$useValue = substr(self::$useValue, 0, -1);
			# Build query
			self::$query = "INSERT INTO " . $table . " (" . self::$useColumn . ") VALUES (" . self::$useValue . ")";
		}else if(is_string($columns) && is_string($values)){
			#Build query
			self::$query = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $values . ")";
		}else{
			# Error: Wrong parameters sent
			return self::$finalReturn['error'] = "102";
		}
		
		# Try to insert the data
		if(self::$connection->query(self::$query) === TRUE){
			# Successfully inserted
			return self::$finalReturn['success'] = "100";
		}else{
			# Error: We have a SQL error, please try again.
			return self::$finalReturn['error'] = "103";
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
		# Transform $data array to the nice format string for SQL
		foreach($data as $key => $value){
			self::$useValue .= $key . "='" . $value . "', ";
		}
		# Delete last comma in the $useValue
		self::$useValue = substr(self::$useValue, 0, -2);
		# Build query
		self::$query = "UPDATE " . $table . " SET " . self::$useValue . " WHERE " . $where; // Debugging: return self::$query;
		# Try to insert the data
		if(self::$connection->query(self::$query) === TRUE){
			# Successfully updated
			return self::$finalReturn['success'] = "100";
		}else{
			# Error: We have a SQL error, please try again.
			return self::$finalReturn['error'] = "103";
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
		self::$query = "DELETE FROM " . $table . " WHERE " . $where; // Debugging: return self::$query;
		# Try to delete the data
		if(self::$connection->query(self::$query) === TRUE){
			# Successfully deleted
			return self::$finalReturn['success'] = "100";
		}else{
			# Error: We have a SQL error, please try again.
			return self::$finalReturn['error'] = "103";
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
		self::$query = "UPDATE " . $table . " SET " . $status . " WHERE " . $where; // Debugging: return self::$query;
		# Try to insert the data
		if(self::$connection->query(self::$query) === TRUE){
			# Successfully updated
			return self::$finalReturn['success'] = "100";
		}else{
			# Error: We have a SQL error, please try again.
			return self::$finalReturn['error'] = "103";
		}
		
	}
	
}

/*** Testing: passed ***/

/* Database::get()
include "Connection.class.php";
include "Settings.class.php";
echo Database::get('city', '*', "", "LIMIT 3", true, "return");
echo "<br><br><br>";
var_dump(Database::get('bih', '*', "", "LIMIT 1", false, "return"));
*/

/*
include "Connection.class.php";
echo Database::set('test', 'test_id, test_name, test_desc', "NULL, 'Bosna', 'Ovo je Bosnia, ovo je Bosnia!'");
echo Database::set('test', ["test_id" => NULL, "test_name" => "Bosnia Bro", "test_desc" => "I ovo je Bosnia!"]);
*/

/*
include "Connection.class.php";
echo Database::update('test', ["test_name" => "Bosnia BroBroBro"], "test_id = '1'");
*/

/*
include "Connection.class.php";
echo Database::delete('test', "test_id = '1'");
*/
/*
include "Connection.class.php";
echo Database::status('test', "test_status = '10'", "test_id = '0'");
*/