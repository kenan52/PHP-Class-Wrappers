<?php

class User{
	private static $username;
	private static $password;
  private static $userType;
	private static $users;
  private static $return;
  private static $user;
  private static $usernameDB;
  private static $passwordDB;
  private static $languageDB;
  private static $userID;
  private static $what;
  private static $where;
  private static $table = "database";
	private static $database = "database";
  private static $userStatus;
  private static $returnType;
	private static $data;
	private static $dataModified;
  private static $dataKeys;
  private static $check;
	private static $emailDB;
	private static $limit;
	private static $checkUser;
	private static $insertData;
	private static $confirmation;
	
	
	/*
	 *** Function for getting user language ***
	 * parameters:
	 ** $languageID (to get choosen language)
	 ** $table (from which table to get it)
	 * return:
	 ** $language data array on success
	 ** false on error
	 */
	public static function getLanguage($languageID, $table){
		# Get our language data
		$what = "*";
		$where = "WHERE lang_id = '{$languageID}'";
		$userLanguage = json_decode(Database::get($table, $what, $where), true);
		$userLanguage = $userLanguage[0];
		# On success return $language data array
		if(isset($userLanguage['lang_name'])){
			return $userLanguage;
		}
		# On error, return false
		return false;
	}
	
	/*
	 *** Function for creating registration link for any user type ***
	 * parameters:
	 ** $data - User data
	 ** $table - for getting the correct user
	 ** $type - for getting the correct user
	 * returns:
	 ** $registrationLink - on success
	 ** false - on error
	 */
	public static function createRegistrationLink($data, $table, $type){
		# Prepare query for getting the user from the database by its username
		$username = $data[$type . "_username"];
		$where = $type . "_username = '$username'";
		# Get the user if exists
		$user = self::getUser($table, $type, $where);
		# Prepare table for inserting confirmation link
		$table .= "_conf";
		# If we have user id, then proceed in creating confirmation link
		if($user[$type . "_id"]){
			# Create datetime and expiring datetime
			$datetime = $data[$type . "_date"] . "%20" . $data[$type . "_time"];
			$expirationTime = date("d-m-Y H:i:s", strtotime(date("d-m-Y H:i:s") . "+120 minutes"));
			$expirationDatetime = $expirationTime;
			$expirationDatetime = str_replace(" ", "%20", $expirationDatetime);
			# Create users name
			$name = $data[$type . "_name"];
			# Create registration code
			$registrationCode = md5(
				$name . 
				$user[$type . "_id"] . 
				$user[$type . "_username"] . 
				$user[$type . "_email"] . 
				$user[$type . "_date"] . 
				$user[$type . "_time"] . 
				$expirationDatetime);
			# Prepare array for inserting the confirmation link
			$data = [
				$type . "_conf_user_id"=>$user[$type . "_id"],
				$type . "_conf_user_name"=>$name,
				$type . "_conf_user_username"=>$user[$type . "_username"],
				$type . "_conf_user_email"=>$user[$type . "_email"],
				$type . "_conf_user_code"=>$registrationCode,
				$type . "_conf_used"=>0,
				$type . "_conf_expire_datetime"=>$expirationDatetime,
				$type . "_conf_datetime"=>$datetime
			];
			# Insert confirmation link
			$insert = Database::set($table, $data);
			# If registration link is inserted correctly, then return registration link
			if($insert['code'] == 100){
				# Create registration link and return it
				$registrationLink = "https://iweb.com/managment.php?" .
          "user_id=" . $user[$type . "_id"] . "&" .
          "user_name=" . $user[$type . "_username"] . "&" .
          "user_email=" . $user[$type . "_email"] . "&" .
          "user_code=" . $registrationCode . "&" .
          "user_used=1" . "&" .
          "user_datetime=" . $datetime . "&" .
          "user_expire_datetime=" . $expirationDatetime . "&" .
          "user_language=" . $user[$type . "_language"] . "&" .
					"user_type=" . $type . "&" .
					"operation=" . md5("registration_confirmation") . "";
				# Return registration link
				return $registrationLink;
			}else{
				# On error, return false
				return false;
			}
		}else{
			# User doesn't exists, return false
			return false;
		}
		
	}
	
	/*
	 *** Function for checking is user on our whitelist ***
	 * parameters:
	 ** $userType - string of user type: example->"company" or "user" or "admin", etc.
	 * returns:
	 ** true - on success
	 ** false - on false
	 */
	public static function checkList($userType){
		$users = (array) File::get("users.json");
		if(in_array($userType, $users)){
			# On success return true
			return true;
		}
		
		# On failure return false
		return false;
	}
	
	/*
	 *** Function for getting the data for one user ***
	 * parameters:
	 ** $table (from which table to get the user)
	 ** $type (which user type we are getting)
	 ** $where (if !null get user by some column and its row value, or if null get the last user)
	 * returns:
	 ** $user on success
	 ** false on error
	 */
	public static function getUser($table, $type, $where = null){
		# We need all the data
		$what = "*";
		# Get concrete user by some column, or get the last user in the table
		$where = ($where != NULL && isset($where)) ? "WHERE " . $where : "ORDER BY " . $type . "_id DESC";
		# Get only one user
		$limit = "LIMIT 1";
		# Get the user
		$user = json_decode(Database::get($table, $what, $where, $limit));
		# Convert multidimensional array to the onedimension array
		$user = (array) $user[0];
		
		if(isset($user[$type . "_email"])){
			# return user
			return $user;	
		}
		
		return false;
		
	}
	
	/*
	 *** Function for checking is user already registered by username and email
	 * parameters:
	 ** $user (data user sent us)
	 ** $users (array of users we got from the database
	 ** $type ($user type to deal with prefixes from the database
	 * returns:
	 ** true (when we don't have a match) - User is safe to register
	 ** false (when we have a match) - User is not safe to register
	 */
	public static function checkUser($user, $checkUsers, $type){
		foreach($checkUsers as $checkUser){
			if($checkUser[$type . '_username'] === $user['username']){
				return false;
			}else if($checkUser[$type . '_email'] === $user['email']){
				return false;
			}
		
		}
		return true;
	}
	
	/*
   *** Function for user login ***
   * This method is checking the data user provided, and (if success) it logs it in the system
   * parameters:
   ** $username (users username)
   ** $password (users password)
   ** $userType (this concept means we store different user type in different tables -> example: regular users, companies, etc.)
   ** and before we proceed with database work we need to get the list of possible user types and see if our user type is in there
   ** $returnType ("array" will return $user data, and something other will just return success code)
   * returns:
   ** Array of $user data
   ** 500 - Success Code
   ** 501 - No user type error
   ** 502 - Can't get data error
   ** 503 - User data and database data are not match
   ** 504 - User deleted
   ** 505 - User blocked
   ** 506 - User unconfirmed
   */
	public static function login($username, $password, $userType, $returnType = "array"){
		# Clean inputs
		self::$username = Security::secureString($username);
		self::$password = Security::secureString($password);
		self::$userType = Security::secureString($userType);
		
		# Hash password
		self::$password = Security::secureHash(self::$password);
		
		# Get the list of the user types
    self::$users = (array) File::get("users.json");
    
    # Check is user type we get in the list, if is proceed, else return error
		if(in_array(self::$userType, self::$users)){
      
			# Build what we need from Database:
			/* Deprecated: We need to get all data
      # username column -> example: user_username
      self::$usernameDB = $userType . "_username";
      # password column -> example: user_password
      self::$passwordDB = $userType . "_password";
      # language id column -> example: user_language
      self::$languageDB = $userType . "_language";
      # user id column -> example: user_id
      self::$userID = $userType . "_id";
      # user status columm -> example: user_status
      self::$userStatus = $userType . "_status";
			*/
			
      # Database and table -> example: application.user
      self::$table = self::$table . "." . self::$userType;
			
			/* Deprecated: We need to get all data 
      # Concatenate columns -> example: user_id, user_username, user_password, user_language
      self::$what = self::$userID . "," . self::$usernameDB . "," . self::$passwordDB . "," . self::$languageDB . "," . self::$userStatus;
			*/
			
			# Get all data
			self::$what = "*";
      # Which row to get -> example: WHERE user_username = 'firstandlastname'
      self::$where = "WHERE " . self::$userType . "_username = '" . self::$username . "'";
      
      # See if is user in there
      try{
        # Get the user (as array -> that true parameter means)
        self::$user = json_decode(Database::get(self::$table, self::$what, self::$where), true); 
        # Get rid of JSON's syntax where he puts array data inside another array (so we get clean, one-dimensional array)
        self::$user = self::$user[0];
      }catch(Exception $e){
        # If no such user, return error
        $return['code'] = "502";
        return $return;
      }
      
      # Get the database data
      self::$usernameDB = self::$user[self::$userType . '_username'];
      self::$passwordDB = self::$user[self::$userType . '_password'];
      self::$userStatus = self::$user[self::$userType . '_status'];
      
      # Check if user data and database data are match
      if(self::$username === self::$usernameDB && self::$password === self::$passwordDB){
         # Switch user status to see if it is ok to login him
         switch(self::$userStatus){
           case "1":
             # Return error because user is deleted
             $return['code'] = "504";
             return $return;
             break;
           case "2":
             # Return error because user is blocked
             $return['code'] = "505";
             return $return;
             break;
           case "4":
             # Return error because user is unconfirmed
             $return['code'] = "506";
             return $return;
             break;
           case "8":
           case "16":
           case "32":
           case "64":
           case "128":
           case "256":
           case "512":
             # Return what client wants (user data or just confirmation of success)
             if($returnType == "array"){
              # Return user array
              return self::$user; 
             }else{
              # Return success
              $return['code'] = "500";
              return $return;
              break;   
             }
             
         }
        
      }else{
        # Return error if data is not a match
        $return['code'] = "503";
        return $return;  
      }
      
    }else{
      # Return error if we can't get the user type
			$return['code'] = "501";
			return $return;
    }
	}
	
	/*
	 *** Function for registering user ***
	 * parameters:
	 ** $data array with users data
	 * returns:
	 ** 500 - Success
	 ** 507 - No such user type in the list
	 ** 508 - Already registered
	 ** 509 - Can't insert user
	 ** 510 - User inserted, but can't get send confirmation link
	 */
	public static function register($data){
		# Clear the data array in automatic way
		self::$data = Security::secureArray($data); //Debugging: return self::$data;
		# Secure password
		self::$data['password'] = Security::secureHash(self::$data['password']);
		# Set date and time
		$date = date("d-m-Y");
  	$time = date("H:i:s");
		self::$data['date'] = $date;
		self::$data['time'] = $time;
		# Get user type
		self::$user = self::$data['user'];
		# Check user
		self::$check = self::checkList(self::$user);
		# Database and table -> example: application.user
		self::$table = self::$table . "." . self::$user;
		# Check is user type we get in the list, if is proceed, else return error
		if(self::$check){
			# Get the user by username
			self::$where = self::$user . "_username = '" . self::$data['username'] . "'";
			self::$usernameDB = self::getUser(self::$table, self::$user, self::$where);

			# Get the user by email
			self::$where = self::$user . "_email = '" . self::$data['email'] . "'";
			self::$emailDB = self::getUser(self::$table, self::$user, self::$where);
			
			# If we get empty user array we don't need to check is user registered, else check all
			if(self::$usernameDB[self::$user . "_username"] != false && self::$emailDB[self::$user . "_username"] != false){
				# Check if user is already registered
				self::$users[0] = self::$usernameDB;
				self::$users[1] = self::$emailDB;
				self::$checkUser = self::checkUser(self::$data, self::$users, self::$user);	
			}else{
				# User is not registered, so it is true
				self::$checkUser = true;
			}
			
			# If user is not already in the database then proceed with registration
			if(self::$checkUser){
				# Clear array of the data we don't need
				self::$data = Security::clearArray(self::$data, ['user', 'code', 'type', 'id', 'operation']);
				# Modify data key for the input
				self::$data = Database::modify(self::$data, self::$user);
				# Finally insert data
				self::$insertData = Database::set(self::$table, self::$data);
				# If user registered, procced to the creating confirmation link
				if(self::$insertData['code'] == 100){
					# Get confirmation link
					$confirmation = self::createRegistrationLink(self::$data, self::$table, self::$user);
					# Check is confirmation link real and not false
					if($confirmation != false){
						# Get language data
						$language = self::getLanguage(self::$data[self::$user . "_language"], "database.lang");
						# Get Settings for logotip
						$settings = Settings::get('all');
						# Get email template
						$template = Mailer::get("registration-" . self::$data[self::$user . '_language'] . ".html");
						# Decode template
						$search = ['logotip', 'logotip_alt', 'name', 'registration_link', 'phone', 'email', 'username', 'language', 'time', 'date'];
						$replace = [$settings['data']['logotip'], $settings['data']['logotip_alt'], self::$data[self::$user . '_name'], $confirmation, self::$data[self::$user . '_phone'], self::$data[self::$user . '_email'], self::$data[self::$user . '_username'], $language['lang_name'], $time, $date];
						$template = Mailer::decode($template, $search, $replace);
						# Send email
						$mail = Mailer::send($settings['data']['registration'], "iweb Registration Confirmation", self::$data[self::$user . "_email"], self::$data[self::$user . "_name"], "Registration and Welcome Email", $template, true);
						if($mail['code'] == "300"){
							# Return 500 on success
							$return['code'] = "500";
							return $return;
						}else{
							# Something is wrong, please try again to send confirmation link
						$return['code'] = "510";
						return $return;	
						}
					}else{
						# Something is wrong, please try again to send confirmation link
						$return['code'] = "510";
						return $return;	
					}
				}else{
					# Error - User is not inserted
					$return['code'] = "509";
						return $return;	
				}
			}else{
				# User already registered, please change data
				$return['code'] = "508";
				return $return;
			}
		}else{
			# Error - No such user type
			$return['code'] = "507";
			return $return;
		}
	}
	
	/* 
	 *** Function for social registration/login ***
	 * This function login or register user via social networks.
	 * It checks for the email. If email is already in there,
	 * then we add a social network to it. Else, we register him
	 * with random username and password.
	 * parameters:
	 ** $provider (which social network he is using)
	 ** $language (the language he choose, from the cookies)
	 ** $filecode (so we can save the user login in the way so JavaScript knows which user is logged in)
	 * Returns:
	 ** 500 - On success
	 ** 531 - User not inserted
	 ** 532 - User not updated
	 ** 533 - User not saved
	 ** 534 - Hybridauth Exception
	 */
	public static function social($provider, $language, $filecode){
		#Include configuration file for the selected provider
		require "hybridauth/src/config" . $provider . ".php";

		#Include Hybridauth's basic autoloader
		require 'hybridauth/src/autoload.php';

		try {
			#Feed correct configuration array to Hybridauth (Correct from the side of the provider)
			$hybridauth = new Hybridauth\Hybridauth($config);

			#Attempt to authenticate users with a provider by name
			$adapter = $hybridauth->authenticate($provider); 

			#Returns a boolean of whether the user is connected with provider
			$isConnected = $adapter->isConnected();

			#Retrieve the user's profile
			$user = $adapter->getUserProfile();
			# Convert to array for easier use
			$user = (array) $user;
			#Inspect profile's public attributes and then see is he already registered
			if(isset($user['identifier']) && !empty($user['identifier'])){
				# lowercase provider
				$provider = strtolower($provider);
				# Get email
				$email = $user['email'];
				# Get identifier
				$identifier = $user['identifier'];
				# What we need to get
				$what = "*";
				# What to search for
				$where = "WHERE user_email = '{$email}'";
				# Which database and table
				$database = self::$database . ".user";
				# Get the user
				$userCheck = Database::get($database, $what, $where);
				# Check if we got the user
				if($userCheck['code'] == '101'){
					# Proceed to the registration
					# Get date and time
						$date = date("d-m-Y");
						$time = date("H:i:s");
						# Create random password
						$password = Security::secureHash($date.$time);
						# Create username
						$dateUsername = date("dmY");
						$username = strtolower($user['displayName'] . $dateUsername);
						$username = preg_replace('/\s+/', '', $username);
						# Social
						$social = $provider;
						# Language 
						$language = $language;
						# Create user
						$insertUser = [
							"user_name" => $user['firstName'],
							"user_lastname" => $user['lastName'],
							"user_phone" => "",
							"user_email" => $email,
							"user_username" => $username,
							"user_password" => $password,
							"user_" . $provider => $identifier,
							"user_status" => "8",
							"user_language" => $language,
							"user_date" => $date,
							"user_time" => $time 
						];	
						# Insert user
						$insert = Database::set($database, $insertUser);
						if($insert['code'] == '100'){
							# User is inserted
							$return['code'] = '500';
							return $return;
						}else{
							# User is not inserted
							$return['code'] = '531';
							return $return;
						}
				}else{
					# Encode the user array and start checks
					$userCheck = json_decode($userCheck, true);
					$userCheck = $userCheck[0];
					# Get userdb email
					$emailDB = $userCheck['user_email'];
					# Get main identifier (user provided)
					$identifierDB = $userCheck['user_' . $provider];
					# If we got the match on identifiers, than save the user and get hell out of here
					if($identifier === $identifierDB){
						# Save user to the file
						$userCheck['user_' . $provider] = $identifier;
						$userCheck['user_password'] = "";
						$saveUser = File::set("user-" . $filecode . ".json", $userCheck, true, "assets/json/login/");
						# On save succes, return 500, else 534
						if($saveUser['code'] == '400'){
							$return['code'] = '500';
							return $return;
						}else{
							# User is not saved
							$return['code'] = '533';
							return $return;	
						}	
					}else{
						# This email is already used, so we can add social network to it
						$userID = $userCheck['user_id'];
						$what = "*";
						$where = "user_id = '{$userID}'";
						$updateUserData = ['user_' . $provider => $identifier];
						$updateUser = Database::update($database, $updateUserData, $where);
						if($updateUser['code'] == '100'){
							# Save user to the file
							$userCheck['user_' . $provider] = $identifier;
							$userCheck['user_password'] = "";
							$saveUser = File::set("user-" . $filecode . ".json", $userCheck, true, "assets/json/login/");
							# On save succes, return 500, else 534
							if($saveUser['code'] == '400'){
								$return['code'] = '500';
								return $return;
							}else{
								# User is not saved
								$return['code'] = '533';
								return $return;	
							}
						}else{
							# User is not updated
							$return['code'] = '532';
							return $return;	
						}	
					}					
				}
			}
			#Disconnect the adapter 
			$adapter->disconnect();	

		}catch(\Exception $e){
				# Hybridauth Error
				$return['code'] = '534';
				return $return;
		}
	
	}
	
	/*
	 *** Function for changing forgotten password and username ***
	 * This function will create link for forgotten username and password,
	 and send email to that user (if exists) *
	 * parameters:
	 ** $data (user data with user type and user email)
	 * returns:
	 ** 500 - On success
	 ** 519 - Can't get user
	 ** 520 - Can't insert forgotten link
	 ** 521 - Can't send email
	 */
	public static function createForgottenLink($data){
		# Clear data
		$data = Security::secureArray($data);
		# Get user from the table
		$database = self::$database . "." . $data['type'];
		$what = "*";
		$where = "WHERE " . $data['type'] . "_email = '" . $data['email'] . "'"; //Debugging: return $database . " " . $where;
		$user = json_decode(Database::get($database, $what, $where), true);
		$user = $user[0];
		# Check do we have user here, if we do then proceed, else throw error
		if($user != NULL && isset($user[$data['type'] . "_email"])){
			# Get user type
			$type = $data['type'];
			# Create datetime and expiring datetime
			$datetime = date("d-m-Y") . "%20" . date("H:i:s");
			$datetimeExpire = date("d-m-Y H:i:s", strtotime(date("d-m-Y H:i:s") . "+120 minutes"));
			$datetimeExpire = str_replace(" ", "%20", $datetimeExpire);
			# Create users name
			$name = $user[$type . "_name"];
			# Create forgotten code
			$forgottenCode = md5(
				$name . 
				$user[$type . "_id"] . 
				$user[$type . "_username"] . 
				$user[$type . "_email"] . 
				$user[$type . "_date"] . 
				$user[$type . "_time"] . 
				$datetimeExpire);
			# Prepare array for inserting the forgotten link
			$data = [
				$type . "_reset_user_id"=>$user[$type . "_id"],
				$type . "_reset_name"=>$name,
				$type . "_reset_username"=>$user[$type . "_username"],
				$type . "_reset_email"=>$user[$type . "_email"],
				$type . "_reset_code"=>$forgottenCode,
				$type . "_reset_used"=>0,
				$type . "_reset_expire_datetime"=>$datetimeExpire,
				$type . "_reset_datetime"=>$datetime
			];
			# Insert forgotten link
			$table = self::$database . "." . $type . "_reset";
			$insert = Database::set($table, $data);
			# If forgotten link is inserted correctly, then return forgotten link
			if($insert['code'] == '100'){
				# Create forgotten link and return it
				$forgottenLink = "https://iweb.com/managment.php?" .
          "user_id=" . $user[$type . "_id"] . "&" .
          "user_name=" . $user[$type . "_username"] . "&" .
          "user_email=" . $user[$type . "_email"] . "&" .
          "user_code=" . $forgottenCode . "&" .
          "user_used=1" . "&" .
          "user_datetime=" . $datetime . "&" .
          "user_expire_datetime=" . $datetimeExpire . "&" .
          "user_language=" . $user[$type . "_language"] . "&" .
					"user_type=" . $type . "&" .
					"operation=" . md5("forgotten_confirmation") . "";
				# Try sending email
				# Get language data
				$language = self::getLanguage($user[$type . "_language"], "database.lang");
				# Get Settings for logotip
				$settings = Settings::get('all');
				# Get email template
				$template = Mailer::get("forgotten-" . $user[$type . '_language'] . ".html");
				# Clean datetime
				$datetime = Database::cleanDateTime($datetime);
				# Decode template
				$search = ['logotip', 'logotip_alt', 'name', 'forgotten_link', 'email', 'username', 'datetime'];
				$replace = [$settings['data']['logotip'], $settings['data']['logotip_alt'], $user[$type . '_name'], $forgottenLink, $user[$type . '_email'], $user[$type . '_username'], $datetime];
				$template = Mailer::decode($template, $search, $replace);;
				# Send email
				$mail = Mailer::send($settings['data']['recovery'], "iweb Forgotten Password Confirmation", $user[$type . "_email"], $user[$type . "_name"], "Password Reset Email", $template, true);
				if($mail['code'] == "300"){
					# Return 500 on success
					$return['code'] = "500";
					return $return;
				}else{
					# Something is wrong, please try again to send forgotten link (can't send mail)
				$return['code'] = "521";
				return $return;	
				}
			}else{
				# Error - Can't insert forgotten link
			$return['code'] = "520";
			return $return;
			}	
		}else{
			# Error - Can't get user (doesn't exist)
			$return['code'] = "519";
			return $return; 
		}
	}
	/*
	 *** Function for editing user data ***
	 * If we want to change password, we need add 'password_change' key with value '1',
	 so we know that password is changed. If we don't have this key, then password will 
	 be removed from the update array 
	 * parameters:
	 ** $data (all user data, where we need minimal: user id, user type, and what to change)
	 * returns:
	 ** 500 - On success
	 ** 507 - Error: no such user type
	 ** 511 - Error: can't update user
	 */
	public static function edit($data){
		# Clear the data array in automatic way
		$data = Security::secureArray($data);
		# If changed secure password
		if(isset($data['password_change']) && $data['password_change'] == '1'){
			# Secure password
			$data['password'] = Security::secureHash($data['password']);	
		}else{
			# Remove password, because it wasn't edited
			$data = Security::clearArray($data, ['password']);
		}
		# Set date and time
		$date = date("d-m-Y");
  	$time = date("H:i:s");
		$data['date'] = $date;
		$data['time'] = $time;
		# Get user type
		$type = $data['user'];
		# Check user
		$check = self::checkList($type);
		# Database and table -> example: application.user
		$database = self::$database . "." . $type;
		# Check is user type we get in the list, if is proceed, else return error
		if($check){
			# Which user to update
			$where = $type . "_id = " . $data['id'];
			# Clear array of the data we don't need
			$data = Security::clearArray($data, ['user', 'code', 'type', 'id', 'password_change', 'operation']);
			# Modify data key for the input
			$data = Database::modify($data, $type);
			# Finally update data
			$updateData = Database::update($database, $data, $where);
			if($updateData['code'] == '100'){
				# Return success
				$return['code'] = "500";
				return $return;
			}else{
				# Error - Can't update user
				$return['code'] = "511";
				return $return; 
			}
		}else{
			# Error - No such user type
			$return['code'] = "507";
			return $return;
		}
	}
	
	/*
	 *** Function for confirming user registration ***
	 * It checks parameters user send and confirm user in the table *
	 * parameters:
	 ** $data (with confirmation parameters: id, code, type, ...)
	 * returns:
	 ** 500 - On success
	 ** 512 - Datetimes don't match
	 ** 513 - Confirmation link expired
	 ** 514 - Wrong data sent
	 ** 515 - Already confirmed
	 ** 516 - Can't update confirmation table
	 ** 518 - Can't update user table
	 */
	public static function confirmRegistration($data){
		# Get ID
		$userID = $data["user_id"];
		# Get user code (because it is unique)
		$code = $data['user_code'];
		# Get type
		$type = $data["user_type"];
		# Get user confirmation from database
		$what = "*";
		$where = "WHERE " . $type . "_conf_user_code = '{$code}' ORDER BY " . $type . "_conf_id DESC";
		$limit = "LIMIT 1";
		$database = self::$database . "." . $type . "_conf";
		$confirmationDB = json_decode(Database::get($database, $what, $where), true);
		$confirmationDB = $confirmationDB[0];
		# Check is he already confirmed this
		if($confirmationDB[$type . "_conf_used"] != '1'){
			# Get database datetimes
			$datetimeDB = $confirmationDB[$type . "_conf_datetime"];
			$datetimeExpireDB = $confirmationDB[$type . "_conf_expire_datetime"];
			# Clean database datetimes
			$datetimeDB = Database::cleanDateTime($datetimeDB);
			$datetimeExpireDB = Database::cleanDateTime($datetimeExpireDB);
			# Get user datetimes
			$datetimeUser = $data['user_datetime'];
			$datetimeExpireUser = $data['user_expire_datetime'];
			# Clean user datetime
			$datetimeUser = Database::cleanUserDateTime($datetimeUser);
			$datetimeExpireUser = Database::cleanUserDateTime($datetimeExpireUser);
			# First check do we have a match between these datetimes, if not return error
			if($datetimeDB == $datetimeUser && $datetimeExpireDB == $datetimeExpireUser){
				# Get current datetime and check that againt database/user datetime to is confirmation link expired
				$datetimeCurrent = date("d-m-Y H:i:s");
				# Check is confirmation link expired
				if($datetimeCurrent <= $datetimeExpireDB){
					# If is not expired, check if all variables are match (DB - User)
					if(
						$data['user_id'] === $confirmationDB[$type . "_conf_user_id"] &&
						$data['user_name'] === $confirmationDB[$type . "_conf_user_username"] &&
						$data['user_email'] === $confirmationDB[$type . "_conf_user_email"] && 
						$data['user_code'] === $confirmationDB[$type . "_conf_user_code"]
					){
						# Update table row, so we can know that this confirmation link is used
						$update[$type . "_conf_used"] = '1';
						$where = $type . "_conf_user_code = '{$code}'";
						$confirmationUpdate = Database::update($database, $update, $where);
						# If updated then update user table and confirm that he is officialy registered
						if($confirmationUpdate['code'] == '100'){
							# Check user type so we can set correct user status. If we get some other status, then set it to the '4' - Unconfirmed
							if($data['user_type'] == 'user'){
								$status = '8';
							}else if($data['user_type'] == 'company'){
								$status = '16';
							}else{
								$status = '4';
							}
							# Update user status
							$changeData = ['id'=>$userID, 'type'=>$type, 'status'=>$status];
							$changeStatus = self::changeStatus($changeData);
							# Check is status is true. If is true, then user is confirmed, else try again
							if($changeStatus){
								# Success
								$return['code'] = "500";
								return $return;	
							}else{
								# Can't update user, try again sending confirmation link
								$return['code'] = "518";
								return $return;		
							}
						}else{
							# Can't update table
							$return['code'] = "516";
							return $return;	
						}
					}else{
						# Wrong data sent
						$return['code'] = "514";
						return $return;
					}
				}else{
					# Confirmation link expired
					$return['code'] = "513";
					return $return;
				}
			}else{
				# Error: Datetimes doesn't match
				$return['code'] = "512";
				return $return;
			}	
		}else{
				# Error: Already confirmed
				$return['code'] = "515";
				return $return;
		}
	}
	
	/*
	 *** Function for confirming user registration ***
	 * This function will on check did user send all the right data,
	 * in on success it will allow it to change its password (if forgotten).
	 * The actual change of password will be executed with edit method() later.
	 * parameters:
	 ** $data (array of user data from createForgottenLink() method)
	 * returns:
	 ** 500 - On sucess
	 ** 522 - Already used link
	 ** 523 - Datetimes doesn't match
	 ** 524 - Link expired
	 ** 525 - Wrong data sent
	 ** 526 - Can't update reset table
	 */
	public static function confirmForgotten($data){
		# Get ID
		$userID = $data["user_id"];
		# Get user code (because it is unique)
		$code = $data['user_code'];
		# Get type
		$type = $data["user_type"];
		# Get user forgotten from database
		$what = "*";
		$where = "WHERE " . $type . "_reset_code = '{$code}' ORDER BY " . $type . "_reset_id DESC";
		$limit = "LIMIT 1";
		$database = self::$database . "." . $type . "_reset";
		$forgottenDB = json_decode(Database::get($database, $what, $where), true);
		$forgottenDB = $forgottenDB[0];
		# Check if this forgotten link is already used
		if($forgottenDB[$type . "_reset_used"] != '1'){
			# Get database datetimes
			$datetimeDB = $forgottenDB[$type . "_reset_datetime"];
			$datetimeExpireDB = $forgottenDB[$type . "_reset_expire_datetime"];
			# Clean database datetimes
			$datetimeDB = Database::cleanDateTime($datetimeDB);
			$datetimeExpireDB = Database::cleanDateTime($datetimeExpireDB);
			# Get user datetimes
			$datetimeUser = $data['user_datetime'];
			$datetimeExpireUser = $data['user_expire_datetime'];
			# Clean user datetime
			$datetimeUser = Database::cleanUserDateTime($datetimeUser);
			$datetimeExpireUser = Database::cleanUserDateTime($datetimeExpireUser);
			# First check do we have a match between these datetimes, if not return error
			if($datetimeDB == $datetimeUser && $datetimeExpireDB == $datetimeExpireUser){
				# Get current datetime and check that againt database/user datetime to is confirmation link expired
				$datetimeCurrent = date("d-m-Y H:i:s");
				# Check is confirmation link expired
				if($datetimeCurrent <= $datetimeExpireDB){
					# If is not expired, check if all variables are match (DB - User)
					if(
						$data['user_id'] === $forgottenDB[$type . "_reset_user_id"] &&
						$data['user_name'] === $forgottenDB[$type . "_reset_username"] &&
						$data['user_email'] === $forgottenDB[$type . "_reset_email"] && 
						$data['user_code'] === $forgottenDB[$type . "_reset_code"]
					){
						# Update table row, so we can know that this forgotten link is used
						$update[$type . "_reset_used"] = '1';
						$where = $type . "_reset_code = '{$code}'";
						$forgottenUpdate = Database::update($database, $update, $where);
						# If updated return 500, and give him a chance to reset his password
						if($forgottenUpdate['code'] == '100'){
							# Success
							$return['code'] = "500";
							return $return;				
						}else{
							# Error: Can't update reset table
							$return['code'] = "526";
							return $return;	
						}
					}else{
						# Error: Wrong data sent
						$return['code'] = "525";
						return $return;		
					}
				}else{
					# Error: Link expired
					$return['code'] = "524";
					return $return;		
				}
			}else{
				# Error: Datetimes doesn't match
				$return['code'] = "523";
				return $return;	
			}
		}else{
			# Error: Already used link
			$return['code'] = "522";
			return $return;
		}
	}
	
	/*
	 *** Function for blocking user ***
	 * parameters:
	 ** $data (User data with user id and type)
	 * returns:
	 ** true on success
	 ** false on error
	 */
	public static function block($data){
		# Get user ID
		$userID = $data['id'];
		# Get user type
		$userType = $data['type'];
		# Get user status
		$userStatus = $userType . "_status = '2'";
		# Get correct database and table
		$database = self::$database . "." . $userType;
		# Create where clause
		$where = $userType . "_id = '{$userID}'";
		#return $database . $where;
		# Try to block user
		$block = Database::status($database, $userStatus, $where);
		# See if block is success
		if($block['code'] == "100"){
			# On success return true
			return true;
		}else{
			# On error return false
			return false;
		}
	}
	/*
	 *** Function for deleting user ***
	 * parameters:
	 ** $data (User data with user id and type)
	 * returns:
	 ** true on success
	 ** false on error
	 */
	public static function delete($data){
		# Get user ID
		$userID = $data['id'];
		# Get user type
		$userType = $data['type'];
		# Get user status
		$userStatus = $userType . "_status = '1'";
		# Get correct database and table
		$database = self::$database . "." . $userType;
		# Create where clause
		$where = $userType . "_id = '{$userID}'";
		#return $database . $where;
		# Try to block user
		$block = Database::status($database, $userStatus, $where);
		# See if block is success
		if($block['code'] == "100"){
			# On success return true
			return true;
		}else{
			# On error return false
			return false;
		}	
	}
	/*
	 *** Function for changing user status ***
	 * Don't use this function for deleting or blocking users 
	 * parameters:
	 ** $data (User data with user id, type and status)
	 * returns:
	 ** true on success
	 ** false on error
	 */
	public static function changeStatus($data){
		$statuses = ['8', '16', '32', '64', '128', '256', '512'];
		# Get user ID
		$userID = $data['id'];
		# Get user type
		$userType = $data['type'];
		# Get status
		$status = $data['status'];
		# Check is status in array
		if(in_array($status, $statuses)){
			# Get user status
			$userStatus = $userType . "_status = '{$status}'";
			# Get correct database and table
			$database = self::$database . "." . $userType;
			# Create where clause
			$where = $userType . "_id = '{$userID}'";
			#return $database . $where;
			# Try to block user
			$block = Database::status($database, $userStatus, $where);
			# See if block is success
			if($block['code'] == "100"){
				# On success return true
				return true;
			}else{
				# On error return false
				return false;
			}		
		}else{
			# Status not in array
			return false;
		}
		
	}
	
	/*
	 *** Function for getting all users ***
	 * We can get all users regardless of user status *
	 * parameters:
	 ** $type (User type)
	 ** $status (Get user by status, or get users regardless of its statuses)
	 * returns:
	 ** Array of users data on success
	 ** False on error
	 */
	public static function getAll($type, $status = NULL){
		# What to get
		$what = "*";
		# Get status
		$where = "WHERE " . $type . "_status LIKE '%{$status}%'";
		# Get database and table
		$database = self::$database . "." . $type;
		# Get users
		$get = json_decode(Database::get($database, $what, $where), true);
		# Return users array on success
		if($get['code'] != "101"){
			return $get;
		}else{
			# Return false on error
			return false;
		}
	}
	
}

/*** Testing: ***/


require "Security.class.php";
require "Connection.class.php";
require "Database.class.php";
require "File.class.php";
require "Settings.class.php";
require "Mailer.class.php";
require "Templater.class.php";
require 'mailer/src/Exception.php';
require 'mailer/src/PHPMailer.php';

/* confirmForgotten: passed
var_dump(User::confirmForgotten(
	[
		"user_id"=>"35",
		"user_name"=>"amarbeslija",
		"user_email"=>"amarbeslija13@gmail.com",
		"user_code"=>"d1a33ed847c5ee61b4c6e6e856a1bac7",
		"user_used"=>"1",
		"user_datetime"=>"28-01-2019%2009:54:39",
		"user_expire_datetime"=>"28-01-2019%2011:54:39",
		"user_language"=>"20",
		"user_type"=>"company",
		"operation"=>"871521fe13bbc421b2410c07342e18a3"
	]
));
*/
/* createForgottenLink: passed 
var_dump(User::createForgottenLink(['type'=>'company', 'email'=>'amarbeslija13@gmail.com']));
*/
/* confirmRegistration: passed 
var_dump(User::confirmRegistration(
	[
		"user_id"=>"76",
		"user_name"=>"amarbeslija",
		"user_email"=>"amarbeslija13@gmail.com",
		"user_code"=>"b446d3dcf0cddd27910085b806e7471b",
		"user_used"=>"1",
		"user_datetime"=>"26-01-2019%2012:20:54",
		"user_expire_datetime"=>"26-01-2019%2014:20:54",
		"user_language"=>"20",
		"user_type"=>"user",
		"operation"=>"64351e6835ea3c305d0806aa8d04f635"
	]
));
*/
/* Edit: passed
var_dump(User::edit(['user'=>'user', 'username'=>'amarbeslija1713', 'id'=>'73', 'password'=>'130319941111', 'password_change'=>'1']));
*/
/* getAll: passed
var_dump(User::getAll('company', ''));
*/
/* Change Status: passed 
var_dump(User::changeStatus(['id'=>'35', 'type'=>'company', 'status'=>'16']));
*/
/* Delete: passed
var_dump(User::delete(['id'=>'73', 'type'=>'user']));
*/
/* Block: passed
var_dump(User::block(['id'=>'35', 'type'=>'company']));
*/

/* Login: passed 
var_dump(User::login("firma1", "12345678", "company"));
*/

/* Registration: passed 
var_dump(User::register([
	'id'=>null,
	'name'=>'Amar',
	'lastname'=>'Bešlija',
	'phone'=>'+387661234567',
	'email'=>'amarbeslija13@gmail.com',
	'username'=>'amarbeslija',
	'password'=>'12341234',
	'social'=>'',
	'auth'=>'',
	'status'=>4,
	'language'=>'20',
	'user'=>'user'
]));
*/
/*
var_dump(User::register([
	'id'=>null,
	'name'=>'Amar',
	'reg'=>'1234123412341234',
	'address'=>"Žunovačka BB",
	'phone'=>'+387661234567',
	'email'=>'amarbeslija133@gmail.com',
	'web'=>'alfasolutions.co',
	'username'=>'amarbeslija133',
	'password'=>'12341234',
	'status'=>4,
	'paid'=>0,
	'package'=>0,
	'language'=>'20',
	'user'=>'company'
]));
*/

#$user = ['user'=>'user', "email"=>"amarbeslija13444@gmail.com", "name"=>"Amar", "lastname"=>"Bešlija", "username"=>"amarbeslijaaaa"];
#$user1 = User::getUser('database.user', 'user', "user_username = 'amarbeslijaaaa'");
#$user2 = User::getUser('database.user', 'user', "user_email = 'amarbeslija13444@gmail.com'");
#$users[0] = $user1;
#$users[1] = $user2;
#var_dump(User::checkUser($user, $users, 'user'));