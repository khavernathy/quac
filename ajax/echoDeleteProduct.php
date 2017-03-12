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
	$prodID = str_replace("productSel","",$_GET['selected_id']);
	
	$stmt = $Database->prepare('DELETE FROM products WHERE ProductID = "'.$prodID.'"');
	if ($stmt->execute()) 
	{
		$output = 'Deleted the product successfully';
		$stmt->close();
	}
	else
	{
		$output = 'error (ajax function will display the error)';
	}
}
echo json_encode($output);

?>