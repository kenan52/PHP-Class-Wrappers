<?php

/*
 * @author Amar Bešlija (Alfa Solutions)
 * Author of Simple Image class: Claviska (Thanks!);
 *
 * Wrapper class for working with the images 
 * Requirements: PHP 5.6+, GD Extension and Settings wrapper class if you want to use it here
 */

require Settings::get('home_path') . "assets/imager/src/claviska/SimpleImage.php";
class Imager extends \claviska\SimpleImage{
	protected $_file;
	protected $_extension;
	public static $return;
	
	/*
	 *** Constructor in which we can get the image without the from() method ***
	 */
	public function __construct($image = null){
		parent::__construct($image);
	}
	
	/*
	 *** Function for loading the image into the $imager ***
	 * There are few ways to load the image.
	 ** 1. Loading image from the File
	 ** 	 If we don't send the $path parameter, it will use $image parameter, as it will think that there is full path and image name 
	 **		 and that is good for using within Uploader class (there we have full path and image name)
	 **		 If we send the $path parameter it will combine home path, user path and image name,
	 **		 so we don't need to send full path, just the relative one and the image name (as we already know home path)
	 ** 2. Loading image from the String
	 **		 We are trying to load image using the File::get() method declared inside File.class.php
	 **		 First paramater is just $image name (always), 
	 **		 second parameter with "false" say that we need non-json file (we need regular file loaded with file_get_contents),
	 **		 and third part is saying where to get image.
	 **		 If we don't input third parameter it will try to get the image from home path + file path + image name combined,
	 **		 otherwise if we put it in, it will try to load image from the home path + user path + image.
	 **		 In conclusion, we don't need to send it full path, just the relative one and image name (always)
	 ** 3. Loading image from the URI
	 **		 It will load the image from the link provided in the first $image parameter. 
	 **		 This needs to be full link (it will not work with relative link)
	 * Parameters:
	 ** $image (Image name, Image full path, or Image full link)
	 ** $path (Where to find image)
	 ** $getter (Do we want to load image fromFile, fromString or fromDataUri)
	 * Returns:
	 ** Image loaded if success
	 ** 601 - We can't load the file (possibly it isn't there)
	 */
	public function from($image, $path = NULL, $from = "file"){
		try{
			# Set path file just for $image->fromFile() method: if $path is NULL then just use $image -> it will contain path and image name, else combine home_path, user path and image name
			$this->_file = ($path != NULL) ? Settings::get('home_path') . $path . $image : $image;
			switch($from){
				case "string":
					# Get the image from the string using File.class.php (if $path is NULL, then it will try to get the image from 'file_path')
					$this->fromString(File::get($image, false, $path));
					break;
				case "uri":
					# Get the image from the URL (Just input the image URL)
					$this->fromDataUri($image);
					break;
				case "file":
				default:
					# Get the image from the file (using _file)
					$this->fromFile($this->_file);
					break;
			}
			
			# Return object with loaded image if success
			return $this;
		}catch(Exception $e){
			# Return error if we can't get the image
			self::$return['code'] = "601";
			return self::$return;		
		}
		
	}
	
	/*
	 *** Function for calling editing methods on the image ***
	 * This is nice method if we want to use it inside some loop
	 * Parameters:
	 ** $method - which method to call on object
	 ** $values - array of values for called method
	 * Returns:
	 ** Modified image (object) on success
	 ** 602 - We can't get the method
	 */
	public function edit($method, $values){
		try{
			switch($method){
				case "resize":
					$this->resize($values[0], $values[1]);
					break;
				case "orient":
					$this->autoOrient();
					break;
				case "fit":
					$this->bestFit($values[0], $values[1]);
					break;
				case "crop":
					$this->crop($values[0], $values[1], $values[2], $values[3]);
					break;
				case "flip":
					$this->flip($values[0]);
					break;
				case "colors":
					$this->maxColors($values[0], $values[1]);
					break;
				case "rotate":
					$this->rotate($values[0], $values[1]);
					break;
				case "thumbnail":
					$this->thumbnail($values[0], $values[1], $values[2]);
					break;
				default:
					break;
			}		
			
			return $this;
		}catch(Exception $e){
			# Return error if we can't get the method
			self::$return['code'] = "602";
			return self::$return;		
		}
	}
	
	/*
	 *** Function for calling filters on image ***
	 * Parameters:
	 ** $filter - which filter to call on image
	 ** $values - values for the called filter
	 * Returns:
	 ** Modified image (with filter) on success
	 ** 603 - Can't get the filter working
	 */
	public function filter($filter, $values){
		try{
			switch($filter){
				case "blur":
					$this->blur($values[0], $values[1]);
					break;
				case "brighten":
					$this->brighten($values[0]);
					break;
				case "colorize":
					$this->colorize($values[0]);
					break;
				case "contrast":
					$this->contrast($values[0]);
					break;
				case "darken":
					$this->darken($values[0]);
					break;
				case "desaturate":
					$this->desaturate();
					break;
				case "opacity":
					$this->opacity($values[0]);
					break;
				default:
					break;
			}
			
			return $this;
		}catch(Exception $e){
			# Return error if we can't get the method
			self::$return['code'] = "603";
			return self::$return;		
		}
	}
	/*
	 *** Function for different export types ***
	 * NOTICE: It is using default file extension.
	 * If you wish to convert image to the other file type, then you need to use default image methods.
	 * Parameters:
	 ** $type - the type of image export
	 ** $file - Image name, Image full path
	 ** $path - Image path if $file is just Image name
	 ** $mimeType - If we want to convert
	 ** $quality - If we want to change the image quality
	 * Returns:
	 ** Edited image on success
	 ** 604 - Error if we can't save the image
	 */
	public function export($type = "file", $file = NULL, $path = NULL, $mimeType = NULL, $quality = NULL){
		try{
			$this->_extension = $this->getMimeType();
			$this->_extension = explode("/", $this->_extension);
			$this->_extension = $this->_extension[1];
			
			switch($type){
				case "uri":
					$this->toDataUri($mimeType, $quality);
					break;
				case "download":
					$this->toDownload($file . "." . $this->_extension, $mimeType, $quality);
					break;
				case "screen":
					$this->toScreen($mimeType, $quality);
					break;
				case "string":
					$this->toString($mimeType, $quality);
					break;
				case "file":
				default:
					# Set path file just for $image->fromFile() method: if $path is NULL then just use $image -> it will contain path and image name, else combine home_path, user path and image name
					$this->_file = ($path != NULL) ? Settings::get('home_path') . $path . $file : $file;
					$this->toFile($this->_file . "." .$this->_extension, $mimeType, $quality);
					break;
			}	
			
			return $this;
		}catch(Exception $e){
			# Return error if we can't save the file
			self::$return['code'] = "604";
			return self::$return;		
		}	
	}
}

/*** Testing: passed ***/


$image = new Imager();
$image->from("Screenshot.png", "assets/images/")->edit("resize", [1200])->resize(1000)->filter("opacity", [0.1])->export("file", "Screen", "assets/images/");
$mime = $image->getMimeType();
echo $mime;
var_dump($image);









