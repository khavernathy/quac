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
	$stmt = $Database->prepare("SELECT * FROM `employees` WHERE `EmployeeID` = ?");
	$stmt->bind_param("s", $newid);
	if ($stmt->execute())
	{
		$res = $stmt->get_result();
		//$total_rows = $res->num_rows;
		$res->data_seek(0); //gets individual row
		$output = $res->fetch_array(); // $res->fetch_assoc() fetches the key names instead of numbers
		echo json_encode($output);
		$res->close();
	}
	else
	{
		echo 'Failed to retrieve individual employee data for ID:'.$newid;
	}
}

?>