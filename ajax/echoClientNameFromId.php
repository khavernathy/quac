<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');

$clientID = $_GET['cid'];

// retrieve client name from id

$getclientname = "SELECT `FirstName`,`LastName` FROM `clients` WHERE `ClientID` = $clientID";
		$stmt = $Database->prepare($getclientname);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
			$row = $result->fetch_array();
			$cname = $row[0].' '.$row[1];
			echo json_encode($cname);
		}
		else
		{
			echo json_encode('failed to retrieve client name from database.');
		}


?>
