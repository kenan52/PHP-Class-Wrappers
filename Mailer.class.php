<?php
/**
  *@author Amar Bešlija (Alfa Solutions)
  */
/* Namespace alias for PHPMailer */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer{
	private static $instance;
	private static $connection;
	private static $mail;
	private static $template;
	private static $table;
	private static $language;
	private static $settings;
	private static $what;
	private static $return;
	
	/*
	 *** Function for getting the mailer ***
	 * Get the mailer inside our static variable
	 */
	public static function getMailer(){
			self::$mail = new PHPMailer(TRUE);
	}
	
	/*
	 *** Function for getting the template from the database ***
	 * parameters:
	 ** $templateName (which template to get)
	 * returns:
	 ** $template (if success)
	 ** 301 - If we can't get the template
	 */
	public static function get($templateName){
		try{
			# Get the template
			self::$template = Templater::get($templateName);
			# Return the template
			return self::$template;	
		}catch(Exception $e){
			# Return the error, we can't get the email template
			self::$return['code'] = "301";
		}
	}
	
	/*
	 *** Function for decoding the template directly here ***
	 * parameters:
	 ** $template (which template to decode)
	 ** $search (which values to search for)
	 ** $replace (which values are replacement for the searched values)
	 * returns:
	 ** $template (if success)
	 ** 302 - Error if we can't decode the template
	 */
	public static function decode($template, $search, $replace){
		try{
			# Call the templater and get him variables to decode the template
			self::$template = Templater::decode($template, $search, $replace);
			# Return the template
			return self::$template;	
		}catch(Exception $e){
			# Return the error, if we can't call the templater and decode the template
			self::$return['code'] = "302";
		}

	}
	
	/*
	 *** Function for sending the email ***
	 * parameters:
	 ** $from (from whom is email)
	 ** $fromDescription (describe the sender of the email)
	 ** $address (to who to send the email)
	 ** $name (name of the recipient of the email)
	 ** $subject (email subject)
	 ** $body (email body -> our templates)
	 ** $html (put TRUE if template is HTML, FALSE if it is not)
	 * returns:
	 ** 300 - Successfully sent email
	 ** 303 - PHPMailer Error
	 ** 304 - PHPException Error
	 */
	public static function send($from, $fromDescription, $address, $name, $subject, $body, $html){
		try{
			# Get mailer object
			self::getMailer();
			# Get the settings
			self::$settings = Settings::get("all"); // Debugging: return self::$settings;
			# Add from address
			self::$mail->setFrom($from, $fromDescription);
			# Add users address
			self::$mail->addAddress($address, $name);
			# Add subject
			self::$mail->Subject = $subject;
			# Add body 
			self::$mail->Body = $body;
			# Check is HTML
			self::$mail->isHTML($html);
			# Send the email
			self::$mail->send();	
			# Return success
			self::$return['code'] = "300";
			return self::$return;
		}catch (Exception $e){
			# Return PHPMailer Error
		  self::$return['code'] = "303";
			return self::$return;
		}catch (\Exception $e){
			# Return PHPException Error
			self::$return['code'] = "304";
			return self::$return;
		}
		
		
	}
	
}

/*** Testing: passed ***/

/*
require 'Connection.class.php';
require 'mailer/src/Exception.php';
require 'mailer/src/PHPMailer.php';
var_dump(Mailer::connect());
*/

/*
require 'Connection.class.php';
require 'Settings.class.php';
require 'Database.class.php';
require 'Templater.class.php';
$template = Mailer::get("something.html");
$template = Mailer::decode($template, ['name', 'email', 'logotip'], ['Amar Bešlija', 'amar@beslija.com', Settings::get('logotip')]);
echo ($template);
*/

/*
require "Settings.class.php";
require "Templater.class.php";
require 'mailer/src/Exception.php';
require 'mailer/src/PHPMailer.php';
$template = Mailer::get("something.html");
$template = Mailer::decode($template, ['name', 'email'], ['Amar Bešlija', 'amarbeslija13@gmail.com']);
var_dump (Mailer::send(Settings::get('registration'), 'Amar Bešlija Test', 'amar@beslija.com', 'Amar Bešlija', 'Just a test', $template, TRUE));
*/
?>







