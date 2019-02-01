<?php
/*
 * PHP Autoloader class *
 */

spl_autoload_register(function ($class){
	$home = "/home/magicalb/public_html/";
	include $home . "assets/" . $class . ".class.php";
});
