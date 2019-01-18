<?php

/**
  * @author Amar Bešlija (Alfa Solutions)
	* Use this .php class to work with getting and setting the files inside server
	* Of course, you can update using the set with modified data,
	* or delete using the set with empty data
	*/

class File{
	private static $file;
	private static $path;
	private static $home;
	private static $data;
	private static $return;
	private static $get;
	private static $set;
	
	/*
	 *** Function for setting the correct absolute path for the files ***
	 * This function is automatically called from get() and set() methods
	 * parameters:
	 ** $file (name of the file we want to use)
	 ** $path (if we want to use custom folder on the server, otherwise that will be home path + self::$path stored here)
	 */
	public static function path($file, $path){
		#Clear the variables
		self::$path = "assets/json/";
		self::$file = "";
		self::$home = "";
		# Get home path
		self::$home = Settings::get('home_path');	
		# Check and get the path
		self::$path = ($path == NULL) ? self::$home . self::$path : self::$home . $path;
		# Store file variable
		self::$file = $file;
		# Combine path and file
		self::$path = self::$path . self::$file;
		# Return $path
		return self::$path;
	}
	
	/*
	 *** Function for getting the data from the file ***
	 * parameters:
	 ** $file (name of the file we want to get)
	 ** $json (if true, we get json decoded data from the file, else just the files content)
	 ** $path (if we want to file from the custom folder not the one from path method)
	 * returns:
	 ** Data from the file
	 ** 401 - We can't get and return the file's data
	 */
	public static function get($file, $json = true, $path = NULL){
		try{
			# Get path
			self::$get = self::path($file, $path);
			# Get the content of the file (json or text)
			self::$data = ($json == true) ? json_decode(file_get_contents(self::$get, true)) : file_get_contents(self::$get, true);
			# Return data
			return self::$data;
		}catch(Exception $e){
			# Return error if we can't get the file
			self::$return['code'] = "401";
			return self::$return;
		}
	}
	
	/*
	 *** Function for setting the data to the file ***
	 * parameters:
	 ** $file (name of the file)
	 ** $data (data which we want to store to the file)
	 ** $json (if true we store json encoded data, if not we store normal text)
	 ** $path (if we want to use custom folder, not the one from the path() method)
	 * returns:
	 ** 400 - If success
	 ** 402 - If we can't set the data
	 */
	
	public static function set($file, $data, $json = true, $path = NULL){
		try{
			# Get path
			self::$set = self::path($file, $path);
			# Set the content of the file (json or text)
			self::$data = ($json == true) ? file_put_contents(self::$set, json_encode($data)) : file_put_contents(self::$set, $data);
			# Check if we created folder
			if(self::$data != FALSE){
				# Return success if we can set file
				self::$return['code'] = "400";
				return self::$return;	
			}
		}catch(Exception $e){
			# Return error if we can't set the file
			self::$return['code'] = "402";
			return self::$return;
		}
	}
}

/*** Testing: passed ***/

/*
require "Settings.class.php";
var_dump(File::get("globalSettings.json", true));
var_dump(File::set("settings.json", "Moj sadržaj", false));
*/
?>