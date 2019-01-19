<?php

class Uploader{
	private static $files;
	private static $file;
	private static $path;
	private static $set;
	private static $total;
	private static $tmpPath;
	private static $newPath;
	private static $return;
	private static $limit;
	private static $editPath;
	
	/*
	 *** Function for uploading the image files and editing them on the fly ***
	 * Parameters:
	 ** $files - array of files
	 ** $limit - if we want to limit uploaded files
	 ** $dimensions - array of widths to use for image resizing
	 ** $path - custom path
	 * Returns:
	 ** 700 - Success
	 ** 701 - Can't save uploaded images
	 ** 702 - Can't save edited images
	 */
	public static function uploadImage($files, $limit = NULL, $dimensions = NULL, $path = NULL){
		# Get array and filter empty values
		self::$files = array_filter($files);
		# Get total number of files
		self::$total = count(self::$files['upload']['name']);
		# Calculate limit (if set, then take the $limit, else set limit to the one big number)
		self::$limit = (isset($limit) && !empty($limit) && !is_null($limit)) ? $limit : 10000000;
		# Loop through files
		for($i=0; $i < self::$total; $i++){
			# Check if counter is smaller than $limit (if it is close the uploader)
			if($i < self::$limit){
				# Get the temportal path
				self::$tmpPath = self::$files['upload']['tmp_name'][$i];
				# Chech if path is empty, if not go to the next iteration auiutomatically
				if(self::$tmpPath != ""){
					# Get new path
					self::$newPath = Settings::get('home_path') . Settings::get('image_path') . self::$files['upload']['name'][$i]; // Debugging: return self::$newPath;
					# Move uploaded file
					if(move_uploaded_file(self::$tmpPath, self::$newPath)){
						# If we have an array of dimensions then resize the uploaded image and save it to the new files
						if(isset($dimensions) && $dimensions != NULL){
							try{
								# Edit and save a new image 
								for($j = 0; $j < count($dimensions); $j++){
									# Instantiate Imager
									$image = new Imager();
									# Put the dimension in to the image name
									$addition = $dimensions[$j] . "-";
									# Make a full path with image name for the save
									self::$editPath = Settings::get('home_path') . Settings::get('image_path') . $addition . self::$files['upload']['name'][$i];
									# Edit and save
									$image->from(self::$newPath)->edit("resize", [$dimensions[$j]])->toFile(self::$editPath);
								}
								# Return success
								self::$return['code'] = "700";
								return self::$return;
							}catch(Exception $e){
								# Return error if we can't save edited uploaded file
								self::$return['code'] = "702";
								return self::$return;	
							}
							
						}
						# Unset image
						unset($image);
						# Return success
						self::$return['code'] = "700";
						return self::$return;
					}else{
						# Return error if we can't save the uploaded file
						self::$return['code'] = "701";
						return self::$return;
					}
				}	
			}
		}
	}
	/*
	 *** Function for uploading the files ***
	 * Parameters:
	 ** $files - array of files
	 ** $limit - if we want to limit uploaded files
	 ** $path - custom path
	 * Returns:
	 ** 700 - On Success
	 ** 703 - Can't save uploaded file
	 */
	public static function upload($files, $limit = NULL, $path = NULL){
		# Get array and filter empty values
		self::$files = array_filter($files);
		# Get total number of files
		self::$total = count(self::$files['upload']['name']);
		# Calculate limit (if set, then take the $limit, else set limit to the one big number)
		self::$limit = (isset($limit) && !empty($limit) && !is_null($limit)) ? $limit : 10000000;	
		# Loop through files
		for($i=0; $i < self::$total; $i++){
			# Check if counter is smaller than $limit (if it is close the uploader)
			if($i < self::$limit){
				# Get the temportal path
				self::$tmpPath = self::$files['upload']['tmp_name'][$i];
				# Chech if path is empty, if not go to the next iteration auiutomatically
				if(self::$tmpPath != ""){
					# Get new path
					self::$newPath = Settings::get('home_path') . Settings::get('file_path') . self::$files['upload']['name'][$i]; // Debugging: return self::$newPath;
					# Move uploaded file
					if(!move_uploaded_file(self::$tmpPath, self::$newPath)){
						# Return error if we can't save the uploaded file
						self::$return['code'] = "703";
						return self::$return;
					}
				}	
			}
		}
		# Return success
		self::$return['code'] = "700";
		return self::$return;
	}
	
}

/*** Testing: passed ***/
/*
require "Settings.class.php";
require "Imager.class.php";
if(isset($_FILES['upload'])){
Uploader::upload($_FILES, 2);
}
*/
?>
<!--
<form action="" method="POST" enctype="multipart/form-data">
	<input type="file" name="upload[]" multiple="multiple">
	<input type="submit" value="Upload">
</form>
-->