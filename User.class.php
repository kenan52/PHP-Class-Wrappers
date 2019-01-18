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
  private static $table = "magicalb_userman";
  private static $userStatus;
  private static $returnType;
  
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
      # Database and table -> example: application.user
      self::$table = self::$table . "." . self::$userType;
      # Concatenate columns -> example: user_id, user_username, user_password, user_language
      self::$what = self::$userID . "," . self::$usernameDB . "," . self::$passwordDB . "," . self::$languageDB . "," . self::$userStatus;
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
        self::$return['code'] = "502";
        return self::$return;
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
             self::$return['code'] = "504";
             return self::$return;
             break;
           case "2":
             # Return error because user is blocked
             self::$return['code'] = "505";
             return self::$return;
             break;
           case "4":
             # Return error because user is unconfirmed
             self::$return['code'] = "506";
             return self::$return;
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
              self::$return['code'] = "500";
              return self::$return;
              break;   
             }
             
         }
        
      }else{
        # Return error if data is not a match
        self::$return['code'] = "503";
        return self::$return;  
      }
      
    }else{
      # Return error if we can't get the user type
			self::$return['code'] = "501";
			return self::$return;
    }
	}
	
	public static function register(){
		
	}
	
	public static function social(){
		
	}
	
	public static function forgotten(){
		
	}
	
	public static function edit(){
		
	}
	
	public static function confirm(){
		
	}
	
	public static function block(){
		
	}
	
	public static function delete(){
		
	}
	
	public static function get(){
		
	}
	
	public static function file(){
		
	}
	
}

/*** Testing: ***/

/* Login: passed
require "Settings.class.php";
require "File.class.php";
require "Security.class.php";
require "Database.class.php";
require "Connection.class.php";
var_dump(User::login("amarbeslija", "12345678", "user"));
*/


