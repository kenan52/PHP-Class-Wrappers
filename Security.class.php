<?php
/**
  *@author Amar Bešlija (Alfa Solutions)
  */

class Security{
	/*
	 *** Function for cleaning the string input ***
	 * Split string to array of characters.
	 * Go one by one and check it out.
	 * If characters is ', ", or ; sanitaze it with \.
	 * Implode array of sanitized characters back to the string.
	 * Return cleaned string to place of call.
	 */
	public static function secureString(string $thingToClean){
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
}

/*** Testing: passed ***/

/*
include "Settings.class.php";
echo Security::secureString("mojstring;!'");
echo Security::secureHash("password");
*/
?>