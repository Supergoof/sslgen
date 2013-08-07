<?php

// Receive zip file

require_once("database_conn.php");

$target_path = "/home/dandomain/apache/www/sslgen/ssl-certs-temp/";

// Check uploaded file

if($_FILES["zip_file"]["name"]) {
	$filename = $_FILES["zip_file"]["name"];
	$source = $_FILES["zip_file"]["tmp_name"];
	$type = $_FILES["zip_file"]["type"];
	$size = $_FILES["zip_file"]["size"];
	
	$name = explode(".", $filename);
	$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
	foreach($accepted_types as $mime_type) {
		if($mime_type == $type) {
			$okay = true;
			break;
		} 
	}
	$continue = strtolower($name[1]) == 'zip' ? true : false;
	if(!$continue) {
		$message = "Det var ikke en zip fil, din hat! (mime type var '$type')";
		echo $message;
		exit;
	}

// Extract to storage

$sanitized_filename = $name[0];
$sanitized_filename = str_replace("_",".",$sanitized_filename);

$target_fullpath = $target_path . "/" . $sanitized_filename . "/" . $filename;  // change this to the correct site path
if(move_uploaded_file($source, $target_fullpath)) {
	$message = "Zip filen blev uploaded korrekt til " . $target_path . "<br />";
} else {	
	$message = "Zip filen blev ikke uploaded - tag lige en snak med en i drift (BRI / JJ)";
	}
}
echo "<br />" . $message;

// Upload to database

if($_FILES["zip_file"]["name"]) {


  $sql_insert = "INSERT into certstor ('cn','status','uploaddaate','zipfile','kayako_ref') VALUES ($sanitized_filename,1,date('Y-m-D',mktime()),$target_fullpath,'ABC-123-4567')";
  mysql_query($sql_insert);
  var_dump($sql_insert);

}

// Generate vhost

$templatefile = file_get_contents("vhost-template");
$new_vhost = str_replace("template-cn-name",$sanitized_filename,$templatefile);

// Insert vhost to production on dsslproxy01 (this machine), if checkmark == enabled

?>
