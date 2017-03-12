<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output = '';
if (!isset($_GET['selected_id']))
{
	$output =  'nada (ajax function will display the error)';
}
else
{
	$servID = str_replace("serviceSel","",$_GET['selected_id']);
	
	$stmt = $Database->prepare('DELETE FROM services WHERE ServiceID = "'.$servID.'"');
	if ($stmt->execute()) 
	{
		$output = 'Deleted the service successfully';
		$stmt->close();
	}
	else
	{
		$output = 'error (ajax function will display the error)';
	}
}
echo json_encode($output);

?>