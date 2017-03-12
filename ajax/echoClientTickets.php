<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output = array();
if (!isset($_GET['selected_id']))
{
	echo json_encode('<tr><td colspan="5">No ID was specified (or no client was clicked)</td></tr>');
}
else
{
	$id = $_GET['selected_id'];
	$_SESSION['lastClientHtmlId'] = $id;
	$newid = str_replace("clientSel","",$id);
	$stmt = $Database->prepare("SELECT * FROM `tickets` WHERE `ClientID` = ? ORDER BY `DateScheduled` DESC");
	$stmt->bind_param("s", $newid);
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
		else echo json_encode('<tr><td colspan="5">This client has no tickets in the database.</td></tr>');
		$result->close();
	}
	else
	{
		echo json_encode('<tr><td colspan="5">Failed to retrieve individual client data for ID:'.$newid.'</td></tr>');
	}
}

?>