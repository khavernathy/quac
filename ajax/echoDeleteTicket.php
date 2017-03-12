<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output = '';
if (!isset($_GET['tid']))
{
	$output =  'nada (ajax function will display the error)';
}
else
{
	$tid = $_GET['tid'];
	// first delete ticket
	$stmt = $Database->prepare('DELETE FROM `tickets` WHERE TicketID = "'.$tid.'"');
	if ($stmt->execute())
	{
		$output = '1'; // confirms ticket delete
		$stmt->close();
	}
	else
	{
		$output = 'error (ajax function will display the error)';
	}

	// now delete ticket details
	$stmt = $Database->prepare('DELETE FROM `ticketdetails` WHERE TicketID = "'.$tid.'"');
	if ($stmt->execute())
	{
		$output .= '1'; // confirms ticket row
		$stmt->close();
	}
	else 
	{
		$output = 'error...ajax';
	}
}
echo json_encode($output); // '11' serves as confirmation. Otherwise there's an error.

?>
