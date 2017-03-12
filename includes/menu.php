<?php 
if (isset($_SESSION['uName']) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true && $_SESSION['K_id'] == 'q_u_a_c123') 
{

// check permissions of user
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
							echo 'MySQL ERROR: Failed to retrieve priviledges for user.';
						}

if ($priv_level == "1") {

 
echo '
<div id="admin_stuff">
<span class="login-out">Hi <b>'.$_SESSION['uName'].' - </b></span>
		[<a href="index.php?logout">logout</a>] - Quac Admin Area v1.0
		<br />

<ul class="topnav">
<li><a id="clients" href="clients.php">Clients</a>&nbsp;&nbsp;&nbsp;</li> 
 
<li><a id="appt-viewer" href="tickets.php">Tickets</a>&nbsp;&nbsp;&nbsp;</li> 
 
<li><a id="manage" href="manage.php">Appointments</a>&nbsp;&nbsp;&nbsp;</li> 
 
<li><a id="products" href="products.php">Products</a>&nbsp;&nbsp;&nbsp;</li>  
<li><a id="services" href="services.php">Services</a>&nbsp;&nbsp;&nbsp;</li>  
 
<li><a id="vendors" href="vendors.php">Vendors</a>&nbsp;&nbsp;&nbsp;</li>
<li><a id="employees" href="employees.php">Employees</a>&nbsp;&nbsp;&nbsp;</li>
 
<li><a id="accounts" href="accounts.php">Accounts</a>&nbsp;&nbsp;&nbsp;</li>

<li><a id="reports" href="reports.php">Reports</a>&nbsp;&nbsp;&nbsp;</li>
 
<li><a id="settings" href="settings.php">Settings</a>&nbsp;&nbsp;&nbsp;</li>
 
<li><a id="tutorial" href="tutorial.php">Watch tutorial</a>&nbsp;&nbsp;&nbsp;</li>
 
</ul>

<hr />
</div>'; 
}

else {
	// limited priviledge user. restrict menu options.
	echo '
<div id="admin_stuff">
<span class="login-out">Hi <b>'.$_SESSION['uName'].' - </b></span>
		[<a href="index.php?logout">logout</a>] - Quac Admin Area v1.0
		<br />

<ul class="topnav">
<li><a id="clients" href="clients.php">Clients</a>&nbsp;&nbsp;&nbsp;</li> 
 
<li><a id="appt-viewer" href="tickets.php">Tickets</a>&nbsp;&nbsp;&nbsp;</li> 
 
<li><a id="manage" href="manage.php">Appointments</a>&nbsp;&nbsp;&nbsp;</li> 
 
<li><a id="products" href="products.php">Products</a>&nbsp;&nbsp;&nbsp;</li>  
<li><a id="services" href="services.php">Services</a>&nbsp;&nbsp;&nbsp;</li>  
 
<!-- <li><a id="vendors" href="vendors.php">Vendors</a>&nbsp;&nbsp;&nbsp;</li>
<li><a id="employees" href="employees.php">Employees</a>&nbsp;&nbsp;&nbsp;</li>
 
<li><a id="accounts" href="accounts.php">Accounts</a>&nbsp;&nbsp;&nbsp;</li>

<li><a id="reports" href="reports.php">Reports</a>&nbsp;&nbsp;&nbsp;</li>
 
<li><a id="settings" href="settings.php">Settings</a>&nbsp;&nbsp;&nbsp;</li>
--> 
<li><a id="tutorial" href="tutorial.php">Watch tutorial</a>&nbsp;&nbsp;&nbsp;</li>
 
</ul>

<hr />
</div>';

}


} 
?>
