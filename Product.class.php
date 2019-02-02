<?php

/*
 * @author Amar Bešlija (Alfa Solutions)
 * Class created for getting, setting, updateing, deleting and searching products from database
 */

class Product{
	private static $database = "magicalb_dataman";
	private static $table = "product";
	
	/* 
	 * Method for getting products from the database
	 * This method can get you all or any number of products by setting limit parameter
	 * This method can get you product where some column has specific value
	 * parameters:
	 ** $where (get by specific column value)
	 ** $limit (get specific number of products)
	 * returns:
	 ** $products array on success
	 ** 801 - On error
	 */
	public static function get($where = null, $limit=null){
		# What we need
		$what = "*";
		# Where
		$where = ($where == null) ? null : "WHERE " . self::$table . "_" . $where[0] . "='" . $where[1] . "'";
		# Limit
		$limit =  ($limit == null) ? null : "LIMIT " . $limit;
		# Database
		$database = self::$database . "." . self::$table;
		# Get our products
		$products = Database::get($database, $what, $where, $limit);
		if($products['code'] == '101'){
			# On error, return error code
			$return['code'] = '801';
			return $return;
		}else{
			# On success return products
			return $products;
		}
	}
	
	/*
	 * Method for getting products by specific search through the most of the database columns
	 * parameters:
	 ** $where (associative array of columns and their values)
	 ** $limit (how many products to get)
	 * returns:
	 ** 802 - On error getting products (no such product in the database)
	 ** $products array - On succes
	 */
	public static function search($where, $limit = null){
		# What we need
		$what = "*";
		# Limit
		$limit = ($limit == null) ? null : "LIMIT " . $limit;
		# Building where
		$whereSearch = "WHERE ";
		# Counter so we don't add " AND " after the last column in where
		$i = 0;
		# Number of columns in where (for use with counter)
		$array = count($where);
		# Loop to make a proper where strubg
		foreach($where as $column => $value){
			# Build the column in the form column = value
			$whereSearch .= self::$table . "_" . $column . " = '" . $value . "'";
			# Check the counter value, so we don't add " AND " after the last column
			if($i < $array-1){
				$whereSearch .= " AND ";
			}
			# Increase counter for one
			$i++;
		}
		# Database
		$database = self::$database . "." . self::$table;
		# Get products
		$products = Database::get($database, $what, $whereSearch, $limit);
		# Check if we got products
		if($products['code'] == '101'){
			# On error getting products return 802
			$return['code'] = '802';
			return $return;
		}else{
			# On succes return products
			return $products;
		}
	}
	
	/* 
	 * Method for inserting new product 
	 * parameters
	 ** $data (all columns name and their values)
	 * returns:
	 ** 800 - On sucess
	 ** 803 - On error
	 */
	public static function set($data){
		# Database
		$database = self::$database . "." . self::$table;
		# Secure data
		$data = Security::secureArray($data);
		# Add prefix
		$data = Database::modify($data, "product");
		# Try to insert it
		$product = Database::set($database, $data);
		return $product;
		# Check did we inserted it
		if($product['code'] == '100'){
			# Success, return 800
			$return['code'] = '800';
		}else{
			# Error, can't insert product = 803
			$return['code'] = '803';
			return $return;
		}
	}
	
	/*
	 * Method for editing product
	 * parameters:
	 ** $data( minimal user id, and data to edit)
	 * returns:
	 ** 800 - On success
	 ** 804 - On error
	 */
	public static function edit($data){
		# Database
		$database = self::$database . "." . self::$table;
		# Take id so we know what to edit
		$where = "product_id = " . $data['id'];
		# What
		$what = "*";
		# Secure data
		$data = Security::secureArray($data);
		# Clear array of the data we don't need
		$data = Security::clearArray($data, ['user', 'code', 'type', 'id', 'operation']);
		# Add prefix
		$data = Database::modify($data, "product");	
		# Finally update data
		$updateData = Database::update($database, $data, $where);
		if($updateData['code'] == '100'){
				# Return success
				$return['code'] = "800";
				return $return;
			}else{
				# Error - Can't update user
				$return['code'] = "804";
				return $return; 
			}
	}

	/*
	 * Method for deleting product
	 * parameters
	 ** $data (product id)
	 * returns:
	 ** 800 - On sucess
	 ** 805 - On error
	 */
	public static function delete($data){
		# Database
		$database = self::$database . "." . self::$table;
		# Take id so we know what to edit
		$where = "product_id = " . $data['id'];
		# Delete is always status 1
		$status = "product_status = '1'";
		# Try to delete product
		$block = Database::status($database, $status, $where);
		# See if block is success
		if($block['code'] == "100"){
			# Return success
			$return['code'] = "800";
			return $return;
		}else{
			# Error - Can't update user
			$return['code'] = "805";
			return $return; 
		}		
	}
	
	/*
	 * Method for changing the product status
	 * parameters:
	 ** $data (product id and product status)
	 * returns:
	 ** 800 - On success
	 ** 806 - On error
	 ** 807 - On unknown status error
	 */
	public static function status($data){
		# Database
		$database = self::$database . "." . self::$table;
		# Take id so we know what to edit
		$where = "product_id = " . $data['id'];
		# Statuses
		$statuses = ['8', '16', '32', '64', '128', '256', '512'];
		# Get the status
		$status = "product_status = '" . $data['status'] . "'";
		# Check is status in array
		if(in_array($data['status'], $statuses)){
			# Try to change status
			$changeStatus = Database::status($database, $status, $where);
			# See if change status is success
			if($changeStatus['code'] == "100"){
				# Return success
				$return['code'] = "800";
				return $return;
			}else{
				# Error - Can't update status
				$return['code'] = "806";
				return $return; 
			}		
		}else{
			# Error - Can't update status (because is not in the array)
				$return['code'] = "807";
				return $return; 
		}	
	}
	
	/*
	 * Method for staring or unstaring the product
	 * parameters:
	 ** $data (product id, and true for star or false for unstar)
	 * returns:
	 ** 800 - On success
	 ** 809 - On error 
	 */
	public static function star($data){
		# Database
		$database = self::$database . "." . self::$table;
		# Take id so we know what to edit
		$where = "product_id = " . $data['id'];	
		# Star on unstar
		$star = ($data['star'] == true) ? "product_star = product_star + 1" : "product_star = product_star - 1";
		# Finally update data
		$updateStar = Database::status($database, $star, $where);
		if($updateStar['code'] == '100'){
				# Return success
				$return['code'] = "800";
				return $return;
			}else{
				# Error - Can't update user
				$return['code'] = "809";
				return $return; 
			}
	}
	
	
}
require "Autoloader.php";
/* Testing: */

/* Get (multiple, with limit, without limit, only one, where is integer, where is string): passed 
var_dump(Product::get(['category', '5'], '2'));
*/
/* Search (get all producst where the columns have specific values): passed
var_dump(Product::search(['user_type'=>'user', 'category'=>'5'], '1'));
*/
/* Insert (we can send everything as string, it will work): passed
var_dump(Product::set([
	"name"=>"Moj proizvod",
	"description"=>"Opis 1",
	"text"=>"Text about it",
	"cost"=>"20",
	"link"=>"moj link.com",
	"phone"=>"123123123",
	"email"=>"moj@email.com",
	"address"=>"Mars",
	"user_id"=>38,
	"user_type"=>"company",
	"language"=>"20",
	"category"=>23,
	"hours"=>"09-17",
	"promoted"=>"1",
	"datetime_expire"=>"15.02.2019 16:00:00",
	"datetime"=>"01.02.2019 16:00:00"
]));
*/
/* Edit data by id: passed
var_dump(Product::edit(['id'=>'7', 'cost'=>'50', 'hours'=>'08-15']));
*/
/* Delete product by id:
var_dump(Product::delete(['id'=>'7']));
 */
/* Change product status (other than delete): passed
var_dump(Product::status(['id'=>'6', 'status'=>'128']));
 */
/* Star on unstar the product:
var_dump(Product::star(['id'=>'7', 'star'=>'true']));
 */