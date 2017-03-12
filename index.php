<?php 
/*
	/admin/index.php
	admin area for Quac. Login and welcome page.
	Made by Douglas Franz, freelance PHP/MySQL/HTML/CSS/JS/jQuery-ist.
*/

session_start();
include('includes/database.php');
if (isset($_SESSION['uName']) && isset($_SESSION['loggedin']) && !isset($_GET['logout'])) { header("Location: manage.php"); }
ob_start();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Quac Login</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>

<body>

	<div id="admin_header">	
	</div>	
	
	<div id="admin_content">
	<?php
	if (!isset($_POST['submit']) || isset($_GET['wrongpass']) || isset($_GET['unauthorized']) || isset($_GET['logout']))
	{
	?>
	
		<h1>Quac - Admin Login</h1>
		
	<?php 
	if (isset($_GET['wrongpass'])) 
		{echo '<p class="notif">Invalid username/password combination. Try again.</p>'; unset($_GET['wrongpass']);}
	elseif (isset($_GET['unauthorized'])) 
		{echo '<p class="notif">You must log in to view that page.</p>'; unset($_GET['unauthorized']);} 
	elseif (isset($_GET['logout'])) 
		{session_destroy(); session_start(); echo '<p class="notif">Successfully logged out.</p>'; unset($_GET['logout']);}
	?>
	
	<form action="index.php" name="login" method="POST">
	<table cellpadding="0" cellspacing="2" border="0">
	<tr><td>
	Username: </td><td><input type="text" name="username" id="username" tabindex="1" /></td><td rowspan="2"><input type="submit" value="enter" name="submit" id="login_submit" tabindex="3" /></td></tr>
	<tr><td>
	Password: </td><td><input type="password" name="password" id="password" tabindex="2" /></td>
	</table>
	</form>
	
	<?php
	}
	else
	{
		$user = $_POST['username'];
		$pass = $_POST['password'];
		$salt = '92hra-?hI';

		// create query
		if ($stmt = $Database->prepare("SELECT * FROM users WHERE username = ? AND password = ?"))
		{
			$stmt->bind_param("ss", $user, md5($pass . $salt));
			$stmt->execute();
			$res = $stmt->get_result();
			$row = $res->fetch_array();
			
			
			// check for num rows
			if ($res->num_rows > 0)
			{
				// correct username/password
				$permissions = $row[3];
				
				$stmt->close();
				$_SESSION['loggedin'] = true;
				$_SESSION['uName'] = $user;
				$_SESSION['pLevel'] = $permissions;
				$_SESSION['start_time'] = time();
				$_SESSION['K_id'] = 'q_u_a_c123';
				header("Location: manage.php");
				
			}
			else
			{
				// failure
				$stmt->close();
				header("Location: index.php?wrongpass");
				
			}
		}
		else
		{
			die("ERROR: Could not prepare MySQLi statement.");
		}
	}
	
	?>

	</div>


</body>
</html>
<?php ob_end_flush(); ?>
