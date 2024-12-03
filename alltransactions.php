<?php
include_once 'mysql-connect.php';
$result = $conn->query("SELECT * FROM all_transactions");

?>

<html>
<head>
<title> All Transactions </title>
</head>

<body>
<table width = "900" border = "1" cellpadding = "1" cellspacing = "1" >
<tr>
<th>Transaction ID</th>
<th>Transaction Date</th>
<th>Amount</th>
<th>Description</th>
<th>Start Date</th>
<th>End Date</th>
<th>Filename</th>
<tr>

<?php


if ($result && $result->num_rows > 0) {
	while($table = $result->fetch_object()) { 
		echo "<tr>";
		//insert in hmtl table
		echo "<td>$table->TransactionID</td>";
		echo "<td>$table->TransactionDate</td>";
		echo "<td>$table->Amount</td>";
		echo "<td>$table->Description</td>";
		echo "<td>$table->StartDate</td>";
		echo "<td>$table->EndDate</td>";
		echo "<td>$table->Filename</td>";
		
		echo "<tr>";	
	}
} else {
    echo "No results found or query failed!";
}



?>

</table>
</body>
</html>