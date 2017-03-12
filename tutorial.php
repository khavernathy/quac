<?php 

session_start();
date_default_timezone_set('America/New_York');
include('models/auth.php');
include('includes/calendar.php');
include('includes/database.php');
include('includes/datetime_functions.php');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Quac Tutorial Page</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>

<body class="tutorial">

	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	
	<div id="admin_content">
	
		<h1>Quac Tutorial &raquo; </h1>
			<hr />
		<div align="center">
			<iframe width="560" height="315" src="http://www.youtube.com/embed/DXlDv4BDeJU" frameborder="0" allowfullscreen></iframe>
		</div>

	

	</div>


</body>
</html>
<?php ob_end_flush(); ?>
