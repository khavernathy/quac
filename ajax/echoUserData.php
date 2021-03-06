<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output = '';
if (!isset($_GET['selected_id']))
{
	echo 'No ID was specified (or no user was clicked)';
}
else
{
	$id = $_GET['selected_id'];
	$_SESSION['lastUserHtmlId'] = $id;
	$newid = str_replace("userSel","",$id);
	$stmt = $Database->prepare("SELECT * FROM `users` WHERE `id` = ?");
	$stmt->bind_param("s", $newid);
	if ($stmt->execute())
	{
		$res = $stmt->get_result();
		//$total_rows = $res->num_rows;
		$res->data_seek(0); //gets individual row
		$output = $res->fetch_array(); // $res->fetch_assoc() fetches the key names instead of index numbers
		echo json_encode($output);
		$res->close();
	}
	else
	{
		echo 'Failed to retrieve individual user data for ID:'.$newid;
	}
}

?>