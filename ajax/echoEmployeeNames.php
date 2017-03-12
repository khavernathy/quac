<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');

$stmt = $Database->prepare("SELECT `EmployeeID`,`FirstName`,`LastName` FROM `employees` WHERE `Active` = '1' ORDER BY `FirstName` ASC");
if ($stmt->execute())
{
	$output = '<select name="Employee" id="Employee" size="1" style="width:170px;"><option disabled hidden value=""></option><option value="(none)">(none)</option>';
	$result = $stmt->get_result();
	$total_rows = $result->num_rows;
	for ($x = 0; $x <= ($total_rows - 1); $x++)
	{
		$result->data_seek($x); //gets individual row
		$row = $result->fetch_array(); // $res->fetch_assoc() fetches the key names instead of numbers
		$output .= '<option id="empSel'.$row[0].'" value="'.$row[1].' '.$row[2].'">'.$row[1].' '.$row[2].'</option>';
	}
	$output .= '</select>';
}
else
{
	$output = 'DB query error';
}

echo json_encode($output);
?>