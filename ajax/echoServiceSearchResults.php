<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output='';
if (!isset($_GET['query']))
{
	echo 'No search query was specified';
}
else
{
	$query = $_GET['query'];
	$stmt = $Database->prepare('SELECT `ServiceID`,`CustomID`,`Category`,`Name`,`Duration`,`Price` FROM `services` WHERE `Name` LIKE "%'.$query.'%" ORDER BY `Name` ASC');
	
	if ($stmt->execute())
	{
		$output = array();
		$result = $stmt->get_result();
		$total_rows = $result->num_rows;
		for ($x = 0; $x <= 29; $x++) //send 30 max results
		{
			$result->data_seek($x); //gets individual row
			$row = $result->fetch_array(); // $res->fetch_assoc() fetches the key names instead of numbers
			if ($row) array_push($output,$row);
			
		}
		echo json_encode($output); // $output should now be an array of arrays
			$result->close();
	}
	else
	{
		echo 'Failed to run search query for '.$_GET['query'];
	}
}

?>