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
	$transID = $_GET['selected_id'];
	$stmt = $Database->prepare('DELETE FROM transactions WHERE TransID = "'.$transID.'"');
	if ($stmt->execute()) 
	{
		$output = 'Deleted the transaction successfully';
		$stmt->close();
	}
	else
	{
		$output = 'error (ajax function will display the error)';
	}
}
echo json_encode($output);

?>