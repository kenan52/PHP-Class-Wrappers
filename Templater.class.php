<?php
/**
  *@author Amar Bešlija (Alfa Solutions)
  */

/*** CAUTION ***
 * PLEASE, DON'T USE ' QUOTEMARK INSIDE TEMPLATES BECAUSE YOU WILL GET THE ERROR
 * WHILE INTERPOLATING THE STRING OR SETTING IT. USE " QUOTEMARK EVERYWHERE,
 * AND FOR THE TEMPLATE VARIABLES USE {{variable}} WHICH WILL LATER BE REPLACED
 * WITH REAL VARIABLE, THAT IS WITH CONTENT IN THE PLACE OF THE EXECUTION. 
 * YOU DON'T NEED TO PUT $ MARK INSIDE {{}}, IT IS A CUSTOM TEMPLATING ENGINE.
 * THANKS!
 */

class Templater{
	private static $settings;
	private static $template;
	private static $path;
	private static $file;
	private static $search;
	private static $replace;
	private static $return;
	
	/*
	 *** Function for getting the settings ***
	 * Store the settings inside self::$settings
	 */
	public static function settings(){
		self::$settings = Settings::get('all');
	}
	
	/*
	 *** Function for getting the template ***
	 * parameters:
	 ** $template (which template to get)
	 ** $where (where to get template. If it is empty use the default home path, and the rest of it hardcoded here)
	 * returns:
	 ** Template
	 ** 201 - Error if we can't get the template
	 */
	public static function get($template, $where = NULL){
		# Try to get the template, if can't return error
		try{
			# Get the settings
			self::settings();
			# Build the path
			self::$path = ($where == NULL) ? self::$settings['data']['home_path'] . "assets/templates/" : $where;
			# Build the template path
			self::$file = self::$path . $template; // Debugging: return self::$file;
			# Get the template
			self::$template = file_get_contents(self::$file);
			# Return the template
			return self::$template;	
		}catch(Exception $e){
			# Return error
			self::$return['code'] = "201";
			return self::$return;
		}	
	}
	
	/*
	 *** Function for decoding the template ***
	 * parameters:
	 ** $template (which template to decode)
	 ** $search (array of values to search in template)
	 ** $replace (array of values which will be the replace for the searched values)
	 * returns:
	 ** Decoded array (if successful)
	 ** 202 - Error if we can't decode the array
	 */
	public static function decode($template, $search, $replace){
		# Try to decode template, if can't return error
		try{
			# Store input variables to the inside variables
			self::$template = $template;
			self::$search = $search;
			self::$replace = $replace;
			
			# Add {{ and }} to search values array
			foreach(self::$search as &$value){
				$value = "{{" . $value . "}}";
			}
			
			# Replace search array with replace array
			self::$template = str_replace(self::$search, self::$replace, self::$template);
			
			# Return $template to the output
			return self::$template;	
		}catch(Exception $e){
			# Return error
			self::$return['code'] = "202";
			return self::$return;
		}
		
	}
	
	/*
	 *** Function for setting the template ***
	 * parameters:
	 ** $template (content of the template)
	 ** $name (file name of the template)
	 ** $where (where to store it. If empty, use home_path and hardcoded value inside)
	 * returns:
	 ** 200 - Successfully added
	 ** 203 - Error (can't add it)
	 */
	public static function set($template, $name, $where = NULL){
		# Try to set the template, if can't return error
		try{
			# Get the settings
			self::settings();
			# Build the path
			self::$path = ($where == NULL) ? self::$settings['data']['home_path'] . "assets/templates/" : $where;
			# Build the template path
			self::$file = self::$path . $name; // Debugging: return self::$file;
			# Set the template
			file_put_contents(self::$file, $template);
			# Return the success code
			self::$return['code'] = "200";
			return self::$return;
		}catch(Exception $e){
			# Return error
			self::$return['code'] = "203";
			return self::$return;
		}			
	}
}


/*** Testing: passed ***/

/*
require "Settings.class.php";
$template = Templater::get("registration.html");
$template = str_replace("{{name}}", "Amar Bešlija", $template);
echo $template;
*/

/*
$template = Templater::decode($template, ['email', 'username'], ['amarbeslija13@gmail.com', 'amarbeslija']);
echo $template;
*/

/*
require "Settings.class.php";
var_dump(Templater::set('<div style="width:100%; height:100%; background-color: #222222; padding: 20px;">
	<img style="width:400px; display: block; margin: 10px auto;" src="{{logotip}}" alt="{{logotip_alt}}">
	<h1 style="color: white; text-align: center; margin: 20px;"> <span style="color: #33bdff;">{{name}}</span>, welcome to appName Application World!</h1>
	<p style="color: white;"> We are very happy that you choose to enter the world of appName Applications. Before you start enjoying it,
	please confirm your registration by clicking on the link below: </p>
	<div style="width:100%; text-align: center;">
		<a href="{{registration_link}}" target="_blank" style="display: inline-block; margin: 10px; color:white; padding: 10px; font-weight: bold; text-align: center; font-size: 18px; text-decoration: none; background: #f1aa63;" >Link to confirmation</a>
		</div>
	<p style="color:white;">Or you can copy this link to your browser, and confirm your email and your registration to our system: </p>
	<p style="color: white!important;"><a href="#" style="color: #33bdff;; text-decoration: none;">{{registration_link}}</a></p> 

	<p style="color: white;"> Because we want for you to have all the information you entered to our system, here they are: </p>
	<ul style="color: white; font-size: 12px;">
		<li>Your name: <span style="color: #33bdff;">{{name}}</span></li>
		<li>Phone number: <span style="color: #33bdff;">{{phone}}</span></li>
		<li>Email: <a href="mailto:{$email}" style="color: #33bdff!important; text-decoration: none!important;">{{email}}</a></li>
		<li style="color: white;"><strong>Your username: <span style="color: #33bdff;">{{username}}</span></strong> - You will need it for login </li>
		<li style="color: white;">Choosen language: <span style="color: #33bdff;">{{language}}</span></li>
	</ul>
	<p style="color: white;"> Time of registration: {{time}} {{date}}.</p>
	<p style="color: white;">If you want to change some of the data, please go here: </p>
	<div style="width:100%; text-align: center;">
		<a href="appName.ba/user.php?user_name={{username}}&user_email={{email}}&language={{language}}" target="_blank" style="display: inline-block; margin: 10px; color:white; padding: 10px; font-weight: bold; text-align: center; font-size: 18px; text-decoration: none; background: #f1aa63;">Edit Personal Informations</a>
	</div>', 'something.html'));
	
*/
	
	

?>