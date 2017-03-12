<?php 
/*
	/admin/settings.php
	allows user to configure settings for the program
	Made by Douglas Franz, freelance PHP/MySQL/HTML/CSS/JS/jQuery-ist.
*/

session_start();
date_default_timezone_set('America/New_York');
ob_implicit_flush(true);
include('models/auth.php');
include('includes/calendar.php');
include('includes/database.php');
include('includes/datetime_functions.php');
include('includes/get_image_ext.php');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<title>Quac Admin Area</title>
	<script type="text/javascript" src="js/jquery.js"></script>	
	<script type="text/javascript">
		function confirmDelete()
		{
			return confirm('Are you sure you want to delete your business logo? This action cannot be undone.');
		}
		
		function confirmDeleteUser()
		{
			if (typeof userHTMLid === 'undefined' || userHTMLid == '')
			{ alert('No user is selected!'); return false; }
			else {
				var delUserName = $("#"+userHTMLid + " td:nth-child(2)").html();
				return confirm('Are you sure you want to delete '+delUserName+'? This action cannot be undone.');
			}
		}
		<?php if (isset($_GET['manageUsers'])) {
		?>	
		function scrollTo(HTMLid)
		{
			var row = document.getElementById(HTMLid);
			var c = row.getAttribute("class");
			c = parseInt(c.replace("r",""));
			$('#users-list-box').scrollTop((c*18)-45);
		}
		<?php } ?>
		
		function displayUserData(userHTMLid)
		{
			<?php
			// clear $_GET['new'] from form action to avoid accidental saving errors
			if (isset($_GET['new']))
			{
				?>
					document.userInfoForm.action = "settings.php?manageUsers&save";
				<?php
			}
			?>
			
			// always hide the password change by default when this function fires
			changePassword("hide");
			
			if (window.check) { $(".highlightRed").removeClass("highlightRed"); }
			$('#'+userHTMLid).addClass("highlightRed");
			window.check = true;
			window.userHTMLid = userHTMLid;

			$.ajax(
			{
				url: "ajax/echoUserData.php?selected_id=" + userHTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					$('#userID').val(data[0]);
					$('#Username').val(data[1]);
					// skip data[2], which is the MD5 encrypted password
					if (data[3] == "0") {
						$('#privs0').prop('checked',true);
					}
					else if (data[3] == "1") {
						$('#privs1').prop('checked',true);
					}
					$('#FirstName').val(data[4]);
					$('#LastName').val(data[5]);
					$('#Comments').val(data[6]);
					
					// save the username to a JS "session" var to maintain it for password changes
					window.usernameVal = data[1];
				}
			});
		}
		
		function checkForm()
		{
			if ($('#Username').val() == '' || $('#FirstName').val() == '' || $('#LastName').val() == '')
			{
				alert("Username, first name, and last name cannot be blank.");
				return false;
			}
			
			if (($('#Password').length > 0 && $('#Password2').length > 0) && $('#Password').val() != $('#Password2').val())
			{
				alert("The new passwords you entered do not match.");
				return false;
			}
			
			if (!($('#privs0').is(':checked')) && !($('#privs1').is(':checked')))
			{
				alert("You must select a priviledge level for the user.");
				return false;
			}
			
			return true;
		}
		
		function changePassword(op)
		{
			if (op == "show") {
				$('#passwordPart').html('<tr><td>Old Password</td><td><input type="password" name="OldPassword" id="OldPassword" /></td></tr><tr><td>New Password</td><td><input type="password" name="Password" id="Password" /></td></tr><tr><td>Confirm New Password</td><td><input type="password" name="Password2" id="Password2" /></td></tr><tr><td></td><td><input type="button" name="changePass" id="changePass" onclick="changePassword(&quot;hide&quot;);" value="cancel password change" /></td></tr>');
				
				// make sure the password fields are blank in case the browser has the pass saved
				// only works sometimes?
				// also make the username unchangeable
				$('#OldPassword').val('');
				$('#Password').val('');
				$('#Password2').val('');
				
				$('#Username').val(window.usernameVal);
				$('#Username').addClass('rogray');
				$('#Username').attr('readonly',true);
				
			} else if (op == "hide") {
				$('#passwordPart').html('<tr><td>Password</td><td><input type="button" name="changePass" id="changePass" onclick="changePassword(&quot;show&quot;);" value="change password" /></td></tr>');
				
				$('#Username').removeClass('rogray');
				$('#Username').attr('readonly',false);
			}
		}
		
		$(document).ready(function() {
			<?php
			// load the last viewed user by default (unless $_GET['new'] is set ) -- that is, making a new user.
			if (isset($_SESSION['lastUserHtmlId']) && $_SESSION['lastUserHtmlId'] != '' && !isset($_GET['new']) && isset($_GET['manageUsers']))
			{
				$lastUserId = $_SESSION['lastUserHtmlId'];
				?>
					displayUserData(<?php echo json_encode($lastUserId); ?>);
					scrollTo(<?php echo json_encode($lastUserId); ?>);
				<?php
			}
			
			// make sure the fields are blank if adding a new user (because browser settings could have them saved)
			if (isset($_GET['manageUsers']) && isset($_GET['new']))
			{
				?>
					$('#Username').val('');
					$('#Password').val('');
				<?php
			}
			?>
		});
		
	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>

<body class="settings">

	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	
	<div id="admin_content">
	<h1>Settings &raquo;
		<a class="buttonLink" href="settings.php">
		<img class="button" src="images/settingsGears.png" alt="general settings" title="general settings" height="32px" width="32px" /></a>
		
		<a class="buttonLink" href="settings.php?manageUsers">
		<img class="button" src="images/users.png" alt="manage users" title="manage users" height="32px" width="32px" /></a>
		
		<a class="buttonLink" href="settings.php?manageUsers&new">
		<img class="button" src="images/addClient2.png" alt="create new user" title="create new user" height="32px" width="32px" /></a>
		
		<a class="buttonLink" href="settings.php?manageUsers&delete" onclick="return confirmDeleteUser();">
		<img class="button" src="images/deleteClient.png" alt="delete selected user" title="delete selected user" height="32px" width="32px" /></a>
	</h1>
	<hr />
	<div id="statusBox"></div>
	<?php
	
	if (!isset($_GET['manageUsers']))
	{
		$bn = ''; $ba = ''; $bp = ''; $bf = ''; $be = ''; $bw = ''; $bl = '';$la = '';
		if (isset($_GET['deleteLogo']))
		{
			$getfilename = "SELECT `BusinessLogo` FROM `settings` WHERE `SettingID` = '1'";
			$stmt = $Database->prepare($getfilename);
			if ($stmt->execute())
			{
				$result = $stmt->get_result();
				$result->data_seek(0);
				$row = $result->fetch_array();
				
				$file_name = $row[0];
				// delete it
				if (unlink('images/uploaded_logo/'.$file_name))
				{
					// now delete previous logo name / status from DB.
					$deletelogo = "UPDATE `settings` SET `BusinessLogo` = ?, `LogoActive` = ? WHERE `SettingID` = '1'";
					$stmt = $Database->prepare($deletelogo);
					$stmt->bind_param("ss",$a='',$b='0');
					if ($stmt->execute())
					{
						?>
						<script type="text/javascript">
						document.getElementById('statusBox').innerHTML = "Deleted logo file and database info successfully.";
						$("#statusBox").fadeIn();
						setTimeout(function(){
							$("#statusBox").fadeOut();
						}, 2500);
						</script>
						<?php
					}
					else
					{
						?>
						<script type="text/javascript">
						document.getElementById('statusBox').innerHTML = "Failed to delete logo. MySQL statement failed.";
						$("#statusBox").fadeIn();
						setTimeout(function(){
							$("#statusBox").fadeOut();
						}, 7500);
						</script>
						<?php
					}
				}
				else
				{
					?>
					<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "Failed to delete logo. unlink() failed.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7500);
					</script>
					<?php
				}
			}
			else
			{
				?>
				<script type="text/javascript">
				document.getElementById('statusBox').innerHTML = "Failed to delete logo. Filename not retrieved successfully.";
				$("#statusBox").fadeIn();
				setTimeout(function(){
					$("#statusBox").fadeOut();
				}, 7500);
				</script>
				<?php
			}
		}
		
		// default view
		$getsettings = "SELECT * FROM `settings` WHERE `SettingID` = '1'";
		$stmt = $Database->prepare($getsettings);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
				$result->data_seek(0);
				$row = $result->fetch_array();
				$bn = $row[1];
				$ba = $row[2];
				$bp = $row[3];
				$bf = $row[4];
				$be = $row[5];
				$bw = $row[6];
				$bl = $row[7];
				$la = $row[8];
		}
		
	?>
		<h3>General business info &raquo;</h3>
		
		<form name="SettingsForm" id="SettingsForm" method="post" action="settings.php?save" enctype="multipart/form-data">
		<table class="settings-table" border="0" cellspacing="1" cellpadding="2">
			<tr><td>Business Name:</td><td><input type="text" name="BusinessName" id="BusinessName" value="<?php echo $bn; ?>" size="35" /></td></tr>
			
			<tr><td>Business Address:</td><td><input type="text" name="BusinessAddress" id="BusinessAddress" value="<?php echo $ba; ?>" size="60" /></td></tr>
			
			<tr><td>Business Phone:</td><td><input type="text" name="BusinessPhone" id="BusinessPhone" value="<?php echo $bp; ?>" /></td></tr>
			
			<tr><td>Business Fax:</td><td><input type="text" name="BusinessFax" id="BusinessFax" value="<?php echo $bf; ?>" /></td></tr>
			
			<tr><td>Business E-mail:</td><td><input type="text" name="BusinessEmail" id="BusinessEmail" value="<?php echo $be; ?>" size="35" /></td></tr>
			
			<tr><td>Business Website:</td><td><input type="text" name="BusinessWebsite" id="BusinessWebsite" value="<?php echo $bw; ?>" size="35" /></td></tr>
			
			<?php
			if ($bl == '' && $la == '0')
			{
				?>
				<tr><td>Business Logo:</td><td><input type="file" name="BusinessLogo" id="BusinessLogo" /><span class="smallText">Supported image types are .png, .jpg, .bmp, and .gif</span></td></tr>
				<?php
			}
			elseif ($bl != '' && $la == '1')
			{
				echo '
					<tr><td colspan="2">Business Logo: [<a class="dl" href="settings.php?deleteLogo" onclick="return confirmDelete();">Delete this logo</a>]<br />
						<img src="images/uploaded_logo/'.$bl.'" title="Business logo" alt="Business logo" /> </td></tr>';
			}
			?>
			
			<tr><td colspan="2"><input type="submit" value="save settings" id="SettingsSubmit" name="SettingsSubmit" /></td></tr>
		</table>
		</form>
			
		<?php
		// if saving
		if (isset($_POST['SettingsSubmit']) && isset($_GET['save']))
		{
			unset($_GET['save']); // prevent screen refresh re-save
			// save the settings to the DB
			$bn = $_POST['BusinessName'];
			$ba = $_POST['BusinessAddress'];
			$bp = $_POST['BusinessPhone'];
			$bf = $_POST['BusinessFax'];
			$be = $_POST['BusinessEmail'];
			$bw = $_POST['BusinessWebsite'];
			
			// do update (not insert) by default. The software will start out with a blank row with ID = 1
			$savesettings = "UPDATE `settings` SET `BusinessName` = ?, `BusinessAddress` = ?, `BusinessPhone` = ?, `BusinessFax` = ?, `BusinessEmail` = ?, `BusinessWebsite` = ? WHERE `SettingID` = '1'";
			$stmt = $Database->prepare($savesettings);
			$stmt->bind_param("ssssss", $bn, $ba, $bp, $bf, $be, $bw);
			if ($stmt->execute())
			{
				// great
			}
			else
			{
				?>
				<script type="text/javascript">
				document.getElementById('statusBox').innerHTML = "Failed to save general business details. There was an error.";
				$("#statusBox").fadeIn();
				setTimeout(function(){
					$("#statusBox").fadeOut();
				}, 7500);
				</script>
				<?php
			}
			
			// DEALING WITH IMAGE
			// I had to chown the uploaded_logo and /tmp directories to "www-data" for permissions to work.
			$img_exts = 
			array('.gif','.jpeg','.png','.swf','.psd','.bmp','.tiff','.tiff','.jpc','.jp2','.jpf','.jb2','.swc','.aiff','.wbmp','.xbm', '.jpg', '.tif');
			
			// check if an image is uploaded
			foreach($_FILES as $file_name => $file_array) 
			{
			  if (is_uploaded_file($file_array['tmp_name']) && in_array(get_image_extension($file_array['tmp_name']), $img_exts)) 
			  { 
				$image_uploaded = 'true'; 
				?>
					<script type="javascript" language="text/javascript">
						console.log("image was uploaded.")
					</script>
				<?php
			  }
			  else if (is_uploaded_file($file_array['tmp_name']) && !in_array(get_image_extension($file_array['tmp_name']), $img_exts)) 
			  { 
				$image_uploaded = 'false'; 
				die('<br />The uploaded file was not a supported image type and was not saved.');
			  }
			  else 
			  { 
				$image_uploaded = 'false';
			  }
			}
			
			// deal with image file if it's there
			foreach($_FILES as $file_name => $file_array) 
			{
			  if ($image_uploaded == 'true')
			  {
				  $file_dir = "images/uploaded_logo"; // the directory to save the image to
				  echo "<br /><br />";
				  if (is_uploaded_file($file_array['tmp_name'])) 
				  {
					  print "temp path: ".$file_array['tmp_name']."<br />";
					  
					  $ext = get_image_extension($file_array['tmp_name']); // get extension of the image
					  // change the name of the file
					  $file_array['name'] = 'business_logo'.$ext;
					  print "name: ".$file_array['name']."<br />";
					  print "type: ".$file_array['type']."<br />";
					  print "size: ".$file_array['size']."<br />";
					
					  if (move_uploaded_file($file_array['tmp_name'],"$file_dir/".$file_array['name']))
					  {
						echo 'Image file uploaded and saved successfully.';
						
						// add image name to db
						$savesettings = "UPDATE `settings` SET `BusinessLogo` = ?, `LogoActive` = '1' WHERE `SettingID` = '1'";
						$stmt = $Database->prepare($savesettings);
						$stmt->bind_param("s", $file_array['name']);
						if ($stmt->execute())
						{
							echo 'Image name added to database.<br />';
						}
						else
						{
							echo 'There was an error adding the image name to the database.<br />';
						}
					  }
					  else
					  {
						echo 'There was an error uploading the image file.';
					  }
				  }
			  }
			}

			// Redirect to ?css to confirm the save
			?>
				<script type="text/javascript">
					window.location = "settings.php?css";
				</script>
			<?php
		}
	}
	elseif (isset($_GET['manageUsers']))
	{
		// user management menu (add or remove users)
		echo '<div id="users-list-box">';
	
		$getusers = "SELECT `id`,`username` FROM `users` ORDER BY `username` ASC";
		$stmt = $Database->prepare($getusers);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
			
			echo '<table class="users" cellpadding="0" border="0" cellspacing="0"><thead>';
			echo '<tr><th>id</th><th>Name</th></thead><tbody>';
			for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
			{
				$result->data_seek($n);
				$row = $result->fetch_array();
				
				$htmlID = 'userSel'.$row[0];
				echo '<tr id="'.$htmlID.'" class="r'.($n+1).'" onclick="displayUserData(this.id);">
				<td>'.$row[0].'</td>
				<td>'.$row[1].'</td></tr>';
			}
			$result->close();
			echo '</tbody></table>';
		}
		else
		{
			echo "Query to retrieve all users' info failed";
		}
		echo '</div>';
		
			// Settings form for users
			?>
			<div id="users-form-box">
			<h3>Manage users</h3>
			<form id="userInfoForm" name="userInfoForm" method="POST" action="settings.php?manageUsers&save<?php echo ((isset($_GET['new'])) ? '&new' : '' ); ?>" onsubmit="return checkForm();">
				<table class="user-details" cellpadding="0" border="0" cellspacing="0">
				<tbody>
					<tr><td>ID</td><td class="ht"><input type="text" id="userID" name="userID" readonly="readonly" class="rogray" size="3" /><span class="tooltip">If you are creating a new user, the ID will be assigned automatically.</span></td></tr>
					<tr><td>Username</td><td><input type="text" name="Username" id="Username" /></td></tr>
					
					<tr><td colspan="2">
						<table id="passwordPart">
						<?php 
						if (isset($_GET['new']))
						{
							?>
							<tr><td>Password</td><td><input type="password" name="Password" id="Password" /></td></tr>
							<tr><td>Confirm Password:</td><td><input type="password" name="Password2" id="Password2" /></td></tr>
							<?php
						}
						else
						{
							?>
							<tr><td>Password</td><td><input type="button" name="changePass" id="changePass" onclick="changePassword('show');" value="change password" /></td></tr>
							<?php
						}
						?>
						</table>
					</td></tr>
					
					<tr><td>First Name</td><td><input type="text" name="FirstName" id="FirstName" /></td></tr>
					<tr><td>Last Name</td><td><input type="text" name="LastName" id="LastName" /></td></tr>
					<tr><td>Comments</td><td><textarea name="Comments" id="Comments"></textarea></td></tr>
					<tr><td>Privileges</td><td><input type="radio" name="privs[]" id="privs0" value="0" />Limited (general user) <input type="radio" name="privs[]" id="privs1" value="1" />Full (admin user)</td></tr> 
					<tr><td></td><td><input type="submit" name="userFormSubmit" id="userFormSubmit" value="save user details" /></td></tr>
				</tbody>
				</table>
			</form>
			</div>
		</div>
		<?php
		
		// finally check for save
		if (isset($_GET['save']))
		{
			if (isset($mysqlPass)) { unset($mysqlPass); } // just in case it's defined somehow
			$salt = '92hra-?hI';
			$userID = $_POST['userID'];
			$Username = $_POST['Username'];
			
			if (isset($_POST['Password']) && isset($_POST['Password2']))
			{
				$Password = $_POST['Password'];
				$Password2 = $_POST['Password2'];
				if ($Password == $Password2) { // should already be true from the JS validation form function
					$mysqlPass = md5($Password.$salt); // HERE $mysqlPass gets defined. If there is no password change from the form then no password change will be made in the DB
				}
				
				if (isset($_POST['OldPassword']))
				{
					$OldPassword = $_POST['OldPassword'];
					$md5op = md5($OldPassword.$salt);
					$checkPassQuery = "SELECT * FROM users WHERE `username` = ? AND `password` = ?";
					$stmt = $Database->prepare($checkPassQuery);
					$stmt->bind_param('ss',$Username,$md5op);
					$stmt->execute();
					$res = $stmt->get_result();
					$row = $res->fetch_array();
			
					// check for num rows
					if ($res->num_rows > 0)
					{
						// correct old password
						// $mysqlPass is defined
						$oldPassCheck = true;
					}
					else
					{
						$oldPassCheck = false;
					}
					// don't go any further if the old password is wrong.
					if ($oldPassCheck == false)
					{
						?>
						<script type="text/javascript">
							// need to refresh the page with invalid old password notice
							window.location = "settings.php?manageUsers&opw";
						</script>
						<?php
						die('Old password was incorrect. Failed to save user details'); // just in case JS fails
					}
				}
				
			}
			$FirstName = $_POST['FirstName'];
			$LastName = $_POST['LastName'];
			$Comment = $_POST['Comments'];
			
			if ($_POST['privs'][0] == "0") {
				$perm = "0";
			} elseif ($_POST['privs'][0] == "1") {
				$perm = "1";
			}
			
			//var_dump($_POST);
			
			if (!isset($_GET['new'])) {
				if (isset($mysqlPass)) {
					$saveUser = 'REPLACE INTO users
					SET `id` = ?, `username` = ?, `password` = ?, `priv_level` = ?, `FirstName` = ?, `LastName` = ?, `Comment` = ?';
				} else {
					$saveUser = 'REPLACE INTO users
					SET `id` = ?, `username` = ?, `priv_level` = ?, `FirstName` = ?, `LastName` = ?, `Comment` = ?'; // skip over the password column
				}
			} else {
				$saveUser = 'INSERT INTO users VALUES (?,?,?,?,?,?,?)';
			}
			$stmt = $Database->prepare($saveUser);
			
			if (isset($mysqlPass)) {
				$stmt->bind_param('sssssss', $userID, $Username, $mysqlPass, $perm, $FirstName, $LastName, $Comment);
			} else {
				$stmt->bind_param('ssssss', $userID, $Username, $perm, $FirstName, $LastName, $Comment); // skip over the password column
			}
			
			if ($stmt->execute())
			{
				// save the id to session to reload the user details automatically after this window.location change
				if (isset($_GET['new'])) {
					$_SESSION['lastUserHtmlId'] = 'userSel'.$Database->insert_id;
				}
				?>
				<script type="text/javascript">
					// need to refresh the page with save confirmation notice
					window.location = "settings.php?manageUsers&usc";
				</script>
				<?php
				$stmt->close();
			}
			else
			{
				?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. User info was not successfully saved.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
				</script>
				<?php
			}
		} // end saving partition
		
		// check for deleting a user
		elseif (isset($_GET['delete']))
		{
			unset($_GET['delete']);
			if (isset($_SESSION['lastUserHtmlId'])) 
			{
				$htmlID = $_SESSION['lastUserHtmlId'];
				$userID = str_replace("userSel","",$htmlID);
				
				$stmt = $Database->prepare("DELETE FROM users WHERE id = ".$userID);
				if ($stmt->execute()) 
				{
					unset($_SESSION['lastUserHtmlId']);
					?>
					<script type="text/javascript">
					delete window.userHTMLid;
					window.location = "settings.php?manageUsers&udc";
					</script>
					<?php
					$stmt->close();
				}
				else
				{
					?>
					<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. User was not deleted.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
				    </script>
				<?php
				}
			} 
		} // end deletion mechanism
		
		if (isset($_GET['usc']))
		{
			unset($_GET['usc']);
			?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "User info was successfully saved.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 2500);
				</script>
			<?php
		}
		elseif (isset($_GET['udc']))
		{
			unset($_GET['udc']);
			?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "User was successfully deleted.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 2500);
				</script>
			<?php
		}
		elseif (isset($_GET['opw']))
		{
			unset($_GET['opw']);
			?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "Incorrect old password. User details were not saved.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7500);
				</script>
			<?php
		}
		
	} // end user management partition
	
	if (isset($_GET['css']))
	{
		unset($_GET['css']); // prevent refresh status
		?>
		<script type="text/javascript">
		document.getElementById('statusBox').innerHTML = "Saved general business details.";
		$("#statusBox").fadeIn();
		setTimeout(function(){
			$("#statusBox").fadeOut();
		}, 2500);
		</script>
		<?php
	}
	?>
	</div>
	
</body>
</html>