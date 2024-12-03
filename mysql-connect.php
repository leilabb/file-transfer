<?php
$file = 'C:\xampp\htdocs\transactions_project\config.ini';
if (file_exists($file)) {
	$config = parse_ini_file($file); //parses config file
$conn = new mysqli('localhost', $config['username'], $config['password'], $config['dbname']); //makes connection using config file content
}
else {
	echo "Config file doesn't exist";
	error_log("Config file doesn't exist in mysql-connect.php.", 0);
}

global $conn;
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
	error_log("Connection failed in mysql-connect.php.", 0);
} 
else {
	echo "Succesfully connected to database ";
}
?>