<?php
header('Content-type: application/json');
session_start();
$output = '';
if (!isset($_GET['selected_id']))
{
	echo 'No ID was specified (or no account was clicked)';
}
else
{
	$id = $_GET['selected_id'];
	$_SESSION['lastClientHtmlId'] = $id;
	$output = json_encode($_SESSION['lastClientHtmlId']);
}
echo $output;

?>