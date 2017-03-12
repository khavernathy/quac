<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output = array();
if (!isset($_GET['selected_id']))
{
	echo json_encode('<tr><td colspan="5">No ID was specified (or no vendor was clicked)</td></tr>');
}
else
{
	$id = $_GET['selected_id'];
	$_SESSION['lastVendorHtmlId'] = $id;
	$newid = str_replace("vendorSel","",$id);
	
	$stmt = $Database->prepare("SELECT * FROM `transactions` WHERE `VendorID` = ? ORDER BY `Date` DESC");
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
		else echo json_encode('<tr><td colspan="5">This vendor has no transactions in the database.</td></tr>');
		$result->close();
	}
	else
	{
		echo json_encode('<tr><td colspan="5">Failed to retrieve individual vendor data for ID:'.$newid.'</td></tr>');
	}
}

?>