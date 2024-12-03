<?php
include_once 'mysql-connect.php';

//UPLOADING
//Allows user to upload chosen file to the "uploads" folder
if(isset($_POST['submit'])) {
$name = $_FILES['file']['name'];
$tmp_name = $_FILES['file']['tmp_name'];

 if (isset($name)) {
	if(!empty($name)) {
		$uploads= 'uploads/';
		$move = move_uploaded_file($tmp_name, $uploads.$name); //uploads file to 'uploads' folder
		if 	($move) {
			echo 'Files uploaded.';
	}
		else {
		echo 'Upload failed.';		
		error_log("Upload failed in upload.php", 0);
		}		
	} else
		{
	echo 'Please choose a file before submitting.';
		}
}
}

//Prepares parsing and parses uploaded file.
if(isset($_POST['parse'])) {
	$path ='C:\xampp\htdocs\transactions_project\foldersconfig.ini';
	if (is_file($path)) {
		$folders = parse_ini_file($path);
		$uploads = $folders['uploads'];
		$processing = $folders['processing'];
		$history = $folders['history'];
		if (count(scandir($uploads)) !== 2){ //if uploads not empty
			$files = glob("$uploads/*.csv"); //array of uploads
			array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files); //sorted (according to date) array of the files  
			while((count($files) !== 0)){ //while not empty
				$file_to_be_processed = $files[0]; //file to be processed (oldest file)
				copy($file_to_be_processed, $processing.basename($file_to_be_processed));//copy current processing file to processing folder
				parse($file_to_be_processed);//parse file
				rename($file_to_be_processed, $history.basename($file_to_be_processed)); //move parsed file to history folder
				array_shift($files); //remove parsed element from files array
				empty_dir($processing); //empty processing folder
				}
			empty_dir($uploads);//empty uploads folder
			echo "Data uploaded succesfully.";
			}
		else {echo "There are no files to upload!";}
	}
	else {
		echo "The file you're trying to parse doesn't exist!";
		error_log("The file to be parsed doesn't exist in upload.php", 0);
	}
	
	}

	
//Empties given directory
function empty_dir($files) {
	$files = glob($files."*");
	foreach($files as $file) {
		if (is_file($file))
			unlink($file);
	}
}
	
//Parses given file.
function parse($file) {	
if(is_file($file)) { //check if file exists
global $conn;

$dateRegex = '|^\d{2}/\d{1}/\d{2}$|'; // Date format
$dateFormat = 'Y-m-d H:i:s';

$file_array = file($file); //array of the file
$file_name = basename($file);
$rows_with_errors = 0;
$total_nr_of_rows = count($file_array);
$start_date = date($dateFormat, time()); //parse initation

$file_handle = fopen($file, "r"); //opens CSV
fgetcsv($file_handle);
while (($row = fgetcsv($file_handle, 1000, ",")) !== false){

 if (!(is_numeric($row[0]) && //checks if row contains errors => checks for wrong types
			preg_match($dateRegex, $row[1]) && 
            is_string($row[2]) &&
            is_numeric($row[3]))) 
			{
				//Increment the error
                ++$rows_with_errors;
}
$transaction_id = $row[0];
$transaction_date=$row[1];
$amount=$row[3];
$description = $row[2];

//FILE TABLE
$sql_file = "INSERT INTO file (Filename, TotalNumberOfRows, RowsWithErrors, StartDate, EndDate, CreationDate) 
		VALUES('$file_name','$total_nr_of_rows', '$rows_with_errors', '$start_date', NULL, NOW())";
	
$conn->query($sql_file);

//TRANSACTION DESCRIPTION TABLE
$sql_transaction_description = "INSERT INTO transaction_description (TransactionDescriptionPK, Description ,CreationDate) 
VALUES(0, '$description', NOW())";

$conn->query($sql_transaction_description);

//TRANSACTION TABLE
$last_trans_des = 'SELECT TransactionDescriptionPK FROM transaction_description ORDER BY TransactionDescriptionPK DESC LIMIT 1'; //last inserted TransactionDescriptionPK
$sql_transaction = "INSERT INTO transaction (TransactionId ,TransactionDate, Amount, CreationDate, TransactionDescriptionPK, FilePK) 
VALUES('$transaction_id', '$transaction_date','$amount', NOW(), ($last_trans_des), '$file_name')";

$conn->query(($sql_transaction));

//UPDATE enddate in table FILE
$sql_update = "UPDATE file SET EndDate=NOW() WHERE Filename = '". $file_name. "'";

$conn->query(($sql_update));

//INSERT in all_transactions table
$sql_alltransactions = "INSERT INTO all_transactions (TransactionID, TransactionDate, Amount, Description, StartDate, EndDate, Filename) 
VALUES('$transaction_id', '$transaction_date','$amount', '$description', '$start_date', NULL, '$file_name')";

$conn->query(($sql_alltransactions));
}
//UPDATE enddate in alltransactions
$enddate_from_table = "SELECT EndDate FROM file WHERE Filename = '". $file_name. "'"; // grab last enddate from table FILE
$sql_update_enddate = "UPDATE all_transactions SET EndDate=($enddate_from_table) WHERE Filename = '". $file_name. "'"; 

$conn->query($sql_update_enddate);

if (!($sql_file && $sql_transaction &&  $sql_transaction_description && $sql_alltransactions && $sql_update_enddate))
	{ 
		
	    echo "Database inserts failed.";
		error_log("Database inserts failed in function parse().");
		}
}
else {
echo "Invalid file.";
error_log("Invalid file in function parse()", 0);
}
}


//Redirects to the page where you can see all transactions, if the table is not empty.
if(isset($_POST['alltransactions'])) {
	$result = $conn->query("SELECT * FROM all_transactions");
	if (is_null($result)) {
header('Location: http://localhost/transactions_project/notransactions.php'); //if all_transactions table is empty
  exit();		
	}
header('Location: http://localhost/transactions_project/alltransactions.php');//if all_transactions table is populated
  exit();
	}

	
?>

<form action = "upload.php" method = "POST" enctype = "multipart/form-data">
	<input type = "file" name ="file"> <br><br>
	<input type = "submit" name = "submit" value = "Submit">
	<input type = "submit" name = "parse" value = "Parse uploaded files">
	<input type = "submit" name = alltransactions value = "View All Transactions">
	</form>
	


