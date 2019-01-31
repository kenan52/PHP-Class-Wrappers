<?php

/**
  * @author Amar Bešlija (Alfa Solutions)
	* 
	* This is a Singleton-type class for connecting to the database
	* It has two methods:
	* instance() which returns the instance of our connection
	* connect() which returns connection object for latter use
	*
	* @example How to create a connection:
	* $instance = Connection::instance();
	* $connection = $instance->connect();
	*/

class Connection{
	private static $instance = null;
	private $connection;
	
	private $serverDB = "mysql1005.mochahost.com";
	private $usernameDB = "magicalb_superad";
	private $passwordDB = "MindBreake130317101994!";
	
	private function __construct(){
		$this->connection = new mysqli($this->serverDB, $this->usernameDB, $this->passwordDB);
		$this->connection->set_charset("utf8");
	}
	
	/*
	 *** Function for creating instance of the class ***
	 * It checks is it already instance of the class there.
	 * If is not it creates one and store it to the $instance.
	 * Then it returns $instance of the class.
	 */
	public static function instance(){
		if(!self::$instance){
			self::$instance = new Connection();
		}
		return self::$instance;
	}
	
	/*
	 *** Function for connecting to the database ***
	 * It returns this object with connection to the database
	 */
	public function connect(){
		return $this->connection;
	}
	
}

/*** Testing: passed ***/
/*
$instance = Connection::instance();
$connection = $instance->connect();
var_dump($connection);

$instance2 = Connection::instance();
$connection = $instance2->connect();
var_dump($connection);

$instance3 = Connection::instance();
$connection = $instance3->connect();
var_dump($connection);
*/

?>