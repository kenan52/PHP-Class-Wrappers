<?php


class Security{
	private static $data;
	private static $keys;
	
	/*
	 *** Function for cleaning the string input ***
	 * Split string to array of characters.
	 * Go one by one and check it out.
	 * If characters is ', ", or ; sanitaze it with \.
	 * Implode array of sanitized characters back to the string.
	 * Return cleaned string to place of call.
	 */
	public static function secureString($thingToClean){
		$thingToCleanToArray = str_split($thingToClean);
		for($i = 0; $i < count($thingToCleanToArray); $i++){
			if($thingToCleanToArray[$i] == "'" || $thingToCleanToArray[$i] == '"' || $thingToCleanToArray[$i] == ";"){
				$thingToCleanToArray[$i] = '\\' . $thingToCleanToArray[$i];
			}
		}
		$thingCleaned = implode($thingToCleanToArray);
		return $thingCleaned;	
	}
	
	/*
	 *** Function for custom hashing and encrypting the data ***
	 * Takes the $password, but really it can be anything.
	 * Takes the global settings to get the hash.
	 * Double md5($password . $hash)
	 * Return $password / data which needs to be encrypted
	 */
	public static function secureHash(string $password){
		$settings = Settings::get();
		$password =  md5(md5($password . $settings['data']['hash']));
		return $password;		
	}
	
	/*
	 *** Function for automatic way of cleaning the array of data ***
	 */
	public static function secureArray($data){
		self::$data = $data;
		$dataKeys = array_keys(self::$data);
		for($i = 0; $i < count(self::$data); $i++){
			self::$data[$dataKeys[$i]] = self::secureString(self::$data[$dataKeys[$i]]);
		}  
		# Return cleaned array of data
		return self::$data;
	}
	
	/*
	 *** Clear array of unnecessary data ***
	 */
	public static function clearArray($data, $keys){
		# Clear complete array
		foreach($data as $value){
			# Check all data we want to get rid of against the array
			foreach($keys as $key){
				# Check if data we want to get rid of is in array
				if(array_key_exists($key, $data)){
					# If is in there, unset the key
					unset($data[$key]);
				}	
			}	
		}
		
		# Return cleared data array
		return $data;
	}
}

/*** Testing: passed ***/

/*
include "Settings.class.php";
echo Security::secureString("mojstring;!'");
echo Security::secureHash("password");
*/

/*
include "Settings.class.php";
var_dump(Security::secureArray(['one'=>"amar!;''", "two"=>";Am;ar;"]));
*/

/*
var_dump(Security::cleanArray(['one'=>'Amar', 'two'=>'Bešlija', 'three'=>'amarbeslija'], ['one', 'three']));
*/
?>






