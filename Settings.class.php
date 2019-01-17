<?php

/**
  * @author Amar Bešlija (Alfa Solutions)
  *
  * Use this .php class to work with adding, updateing and deleting settings item in the globalSettings.json
  * globalSettings.json is hidden with .htaccess
  *
  * @example: $array['data'] = array("hash"=>"ivanavoliamaraiamarvoliivanu"); -> data is first array, and everything else is underneath 
  */

class Settings{
	private static $file = "globalSettings.json";
	private static $path = "/home/magicalb/public_html/assets/json/";
	private static $fullFile;
	/*
	 *** Function for getting all (or one) setting(s) from .json file ***
	 * If we don't use parameter, the function will automatically get all settings.
	 * If we use parameter, the function will get only one, selected setting.
	 */
	public static function get(string $whichOne = "all"){
		self::$fullFile = self::$path . self::$file; // Debugging: return self::$fullFile;
		$theSettingsData = json_decode(file_get_contents(self::$fullFile), true);
		if($whichOne == "all"){
			return $theSettingsData;
		}else{
			return $theSettingsData['data'][$whichOne];
		}

		//example of calling: $settings = getSetting("hash");	
	}
	
	/*
	 *** Function for setting one setting ***
	 * We have to use parameters for this one.
	 * First parameter is which setting to set (it doesn't matter is it already in there, or is it new one)
	 * Second parameter is value of setting to set.
	 */
	
	public static function set(string $whichOne, string $whatValue){
		$theSettingsData = json_decode(file_get_contents(self::$fullFile), true);
		$theSettingsData['data'][(string)$whichOne] = (string)$whatValue;
		file_put_contents(self::$fullFile, json_encode($theSettingsData));
		return $theSettingsData;

		//example of calling: $settingsSet  = setSettings("charset", "utf-8");	
	}
	
	/*
	 *** Function for deleting one setting ***
	 * We have to use parameter for this one.
	 * In the parameter we just have to say which setting we are unseting.
	 */
	public static function delete(string $whichOneToDelete){
		$theSettingsData = json_decode(file_get_contents(self::$fullFile), true);
		$datetime = date("h-i-s-d-m-Y");
		file_put_contents("/home/magicalb/public_html/assets/json/backup/" . $datetime . ".". self::$file, json_encode($theSettingsData));
		if($whichOneToDelete == "all"){
			unset($theSettingsData['data']);
		}else{
			unset($theSettingsData['data'][(string)$whichOneToDelete]);    
		}
		file_put_contents(self::$fullFile, json_encode($theSettingsData));
		return $theSettingsData;
		//example of calling: deleteSettings("title");	
	}
}

/*** Testing: passed ***/
/*
echo Settings::get('registration');
var_dump( Settings::set('charset', 'utf-8'));
var_dump( Settings::set('color', 'black'));
var_dump( Settings::delete('color'));
*/
?>