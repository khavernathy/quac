<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output = '';
if (!isset($_GET['selected_id']))
{
	echo 'No ID was specified (or no employee was clicked)';
}
else
{
	$id = $_GET['selected_id'];
	$_SESSION['lastEmployeeHtmlId'] = $id;
	$newid = str_replace("employeeSel","",$id);
	
	// get the employee's name first
	$stmt = $Database->prepare("SELECT `FirstName`,`LastName` FROM `employees` where `EmployeeID` = $newid");
	if  ($stmt->execute())
	{
		$res = $stmt->get_result();
		$res->data_seek(0);
		$na = $res->fetch_array();
		$name = $na[0].' '.$na[1];
	}
	else
	{
		echo 'Failed to retrieve employee name';
		$name = 'unknown; employee ID is'.$newid;
		exit;
	}
	
	$stmt = $Database->prepare("SELECT * FROM `transactions` WHERE `Name` = ? ORDER BY `Date` DESC");
	$stmt->bind_param("s", $name);
	if ($stmt->execute())
	{
		$result = $stmt->get_result();
		for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
		{
			$result->data_seek($n);
			$row = $result->fetch_array();
			$output[$n] = $row;
		}
		
		if (!empty($output)) {echo json_encode($output); }
		else echo json_encode('<tr><td colspan="8">No transactions found for this employee in the database (query by name).</td></tr>');
		$result->close();
	}
	else
	{
		echo 'Failed to retrieve individual employee data for ID:'.$newid;
	}
}

?>