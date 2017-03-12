<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output='';
if (!isset($_GET['FirstName']) && !isset($_GET['LastName']) && !isset($_GET['Phone']))
{
	echo 'No search query was specified';
}
else
{
	$col=array();
	$query=array();
	
	if (!array_key_exists('Phone',$_GET))
	{
		if (count($_GET) == 2) {
			$x=0;
			foreach ($_GET as $key => $value) {
				$col[$x] = $key;
				$query[$x] = preg_replace("/[^A-Za-z0-9]/", "", $value);
				$query[$x] = $Database->real_escape_string($query[$x]);
				$x++;
			}
			$stmt = $Database->prepare('SELECT `ClientID`,`FirstName`,`LastName`,`CellPhone`,`HomePhone`,`WorkPhone`,`PrimaryPhone` FROM `clients` WHERE '.$col[0].' LIKE "%'.$query[0].'%" AND '.$col[1].' LIKE "%'.$query[1].'%" ORDER BY
			CASE WHEN '.$col[0].' LIKE "'.$query[0].'%"
			AND '.$col[1].' LIKE "'.$query[1].'%"
			THEN 0 ELSE 1 END, '.$col[0].', '.$col[1].'');
		}
		elseif (count($_GET) == 1) {
			$x=0;
			foreach ($_GET as $key => $value) {
				$col[$x] = $key;
				$query[$x] = preg_replace("/[^A-Za-z0-9]/", "", $value);
				$query[$x] = $Database->real_escape_string($query[$x]);
				// $x++;
			}
			$mysqlQuery = 'SELECT `ClientID`,`FirstName`,`LastName`,`CellPhone`,`HomePhone`,`WorkPhone`,`PrimaryPhone` FROM `clients` WHERE '.$col[0].' LIKE "%'.$query[0].'%" ORDER BY 
					CASE WHEN '.$col[0].' LIKE "'.$query[0].'%" THEN 0 ELSE 1 END, '.$col[0].'';
			if ($col[0] == "FirstName") { $mysqlQuery = $mysqlQuery.", `LastName`";}
			elseif ($col[0] == "LastName") { $mysqlQuery = $mysqlQuery.", `FirstName`";}
			$stmt = $Database->prepare($mysqlQuery);
		}
	}
	else
	{
		// search with phone
		/* the mysql function returnNumericOnly was written for this in the mysql folder. */
		if (count($_GET) == 1)
		{
			$query = preg_replace("/[^0-9]/", "", $_GET['Phone']);
			$query = $Database->real_escape_string($query);
			$stmt = $Database->prepare('SELECT `ClientID`,`FirstName`,`LastName`,
				`CellPhone`,
				`HomePhone`,
				`WorkPhone`,
				`PrimaryPhone` 
				FROM `clients` WHERE returnNumericOnly(CellPhone) LIKE "%'.$query.'%" OR returnNumericOnly(HomePhone) LIKE "%'.$query.'%" OR returnNumericOnly(WorkPhone) LIKE "%'.$query.'%" OR returnNumericOnly(PrimaryPhone) LIKE "%'.$query.'%" ORDER BY 
			CASE WHEN (CellPhone LIKE "'.$query.'%" OR HomePhone LIKE "'.$query.'%" OR WorkPhone LIKE "'.$query.'%" OR PrimaryPhone LIKE "'.$query.'%")
			THEN 0 ELSE 1 END'); 
		}
		elseif (count($_GET) == 2)
		{
			foreach ($_GET as $key => $value) {
				if ($key == "Phone") {  
					$query['phone'] = preg_replace("/[^0-9]/", "", $value);
					$query['phone'] = $Database->real_escape_string($query['phone']);
				} else {
					$col['other'] = $key;
					$query['other'] = preg_replace("/[^A-Za-z0-9]/", "", $value);
					$query['other'] = $Database->real_escape_string($query['other']);
				}
			}
			$stmt = $Database->prepare('SELECT `ClientID`,`FirstName`,`LastName`,`CellPhone`,`HomePhone`,`WorkPhone`,`PrimaryPhone` FROM `clients` WHERE 
			((returnNumericOnly(CellPhone) LIKE "%'.$query["phone"].'%" OR retunNumericOnly(HomePhone) LIKE "%'.$query["phone"].'%" OR returnNumericOnly(WorkPhone) LIKE "%'.$query["phone"].'%" OR returnNumericOnly(PrimaryPhone) LIKE "%'.$query["phone"].'%") AND '.$col["other"].' LIKE "%'.$query["other"].'%") ORDER BY 
			CASE WHEN ((CellPhone LIKE "'.$query.'%" OR HomePhone LIKE "'.$query.'%" OR WorkPhone LIKE "'.$query.'%" OR PrimaryPhone LIKE "'.$query.'%") AND '.$col["other"].' LIKE "'.$query["other"].'%") THEN 1 
			WHEN ('.$col["other"].' LIKE "'.$query["other"].'%" AND (CellPhone LIKE "%'.$query["phone"].'%" OR HomePhone LIKE "%'.$query["phone"].'%" OR WorkPhone LIKE "%'.$query["phone"].'%" OR PrimaryPhone LIKE "%'.$query["phone"].'%")) THEN 2 ELSE 3 END, '.$col["other"].''); 
		}
		elseif (count($_GET) == 3)
		{
			foreach ($_GET as $key => $value) {
				if ($key == "Phone") {  
					$query['phone'] = preg_replace("/[^0-9]/", "", $value);
					$query['phone'] = $Database->real_escape_string($query['phone']);
				} elseif ($key=="FirstName") {
					$col['FirstName'] = $key;
					$query['FirstName'] = preg_replace("/[^A-Za-z0-9]/", "", $value);
					$query['FirstName'] = $Database->real_escape_string($query['FirstName']);
				} elseif ($key=="LastName") {
					$col['LastName'] = $key;
					$query['LastName'] = preg_replace("/[^A-Za-z0-9]/", "", $value);
					$query['LastName'] = $Database->real_escape_string($query['LastName']);
				}
			}
			$stmt = $Database->prepare('SELECT `ClientID`,`FirstName`,`LastName`,`CellPhone`,`HomePhone`,`WorkPhone`,`PrimaryPhone` FROM `clients` WHERE 
			((returnNumericOnly(CellPhone) LIKE "%'.$query["phone"].'%" OR returnNumericOnly(HomePhone) LIKE "%'.$query["phone"].'%" OR returnNumericOnly(WorkPhone) LIKE "%'.$query["phone"].'%" OR returnNumericOnly(PrimaryPhone) LIKE "%'.$query["phone"].'%") AND '.$col["FirstName"].' LIKE "%'.$query["FirstName"].'%" AND '.$col["LastName"].' LIKE "%'.$query["LastName"].'%") ORDER BY 
			CASE WHEN (FirstName LIKE "'.$query["FirstName"].'%" AND LastName LIKE "'.$query["LastName"].'%" AND (CellPhone LIKE "%'.$query["phone"].'%" OR HomePhone LIKE "%'.$query["phone"].'%" OR WorkPhone LIKE "%'.$query["phone"].'%" OR PrimaryPhone LIKE "%'.$query["phone"].'%")) THEN 0
			 ELSE 1 END, `FirstName`,`LastName`'); 
		}
	}
	
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
				echo 'Failed to run search query for '.$_GET['searchQuery'].'. (Modified to: '.$query.')';
			}
}

?>