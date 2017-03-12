<?php

// make sure database is pre-loaded
include('includes/database.php');

// first make sure viewer is logged in.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true)
{
	header('Location: index.php?unauthorized');
}

// get user permissions
$query = "SELECT `priv_level` FROM `users` WHERE `username` = ?";
						$stmt = $Database->prepare($query);
						$stmt->bind_param('s',$_SESSION['uName']);
						if ($stmt->execute())
						{
							$result = $stmt->get_result();
							$row = $result->fetch_array();
							$priv_level = $row[0];
						}
						else
						{
							$priv_level = '';
							echo 'MySQL ERROR: Failed to retrieve privledges for user.';
						}




// Checked, works.
$fn = basename($_SERVER['SCRIPT_NAME']);
if ($priv_level == "0" && ($fn == "vendors.php" || $fn == "accounts.php" || $fn == "reports.php" || $fn == "settings.php")) {
	//echo $_SERVER['REQUEST_URI']; echo '<br />'; // equivalent to echo $_SERVER['PHP_SELF'] and $_SERVER['SCRIPT_NAME'] e.g.  /_quac/manage.php ;
	// auto-redirect to the calendar viewer.
	header('Location: manage.php');
}
