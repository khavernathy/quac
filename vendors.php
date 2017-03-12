<?php 
/*
	vendors.php
	Made by Douglas Franz, freelance PHP/MySQL/HTML/CSS/JS/jQuery-ist.
*/

session_start();
date_default_timezone_set('America/New_York');
ob_implicit_flush(true);
include('models/auth.php');
include('includes/calendar.php');
include('includes/database.php');
include('includes/datetime_functions.php');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<title>Quac Admin Area</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/__jqft.js"></script>
	<script type="text/javascript">
	
		function convertdate (date,func) 
		{
			if (date == '')
			{ return ''; }
			else
			{
				if (func == 'tomysql')
				{ //insert conversion to MySQL database
				var atoms = date.split("-");
				var month = atoms[0]; var day = atoms[1]; var year = atoms[2];
				date = "" + year + "-" + month + "-" + day + "";
				return date;
				}
				else if (func == 'touser')
				{ //output conversion to User
				atoms = date.split("-");
				year = atoms[0]; month = atoms[1]; day = atoms[2];
				date = "" + month + "-" + day + "-" + year + "";
				return date;
				}
			}
		}
	
		function convertphone (pn,func)
		{
			output = '';
			if (pn != '' && pn.length == 10)
			{
				pn = pn.toString();
				if (func == "touser") {
					output = '('+pn[0]+pn[1]+pn[2]+') '+pn[3]+pn[4]+pn[5]+'-'+pn[6]+pn[7]+pn[8]+pn[9];
				} else if (func == "tomysql") {
					pn = String(pn);
					output = pn.replace(/[^\d]/g, "");
				}
			}
			else if (pn.length != 10) {
				if (pn.length == 7) { 
					pn = pn.toString();
					output = pn[0]+pn[1]+pn[2]+'-'+pn[3]+pn[4]+pn[5]+pn[6]; } 
				else {
				output = pn; }
			}
			return output;
		}
		
		function checkForm()
		{
			email_regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			if ($("#Name").val() == '')
			{ alert('You must at least include a vendor name.'); return false;}
			else if ($("#E-mail").val() != '' && !(email_regex.test($("#E-mail").val())))
			{ alert('Please enter a valid e-mail address.'); return false;}
			else {return true;}
		}
		
		function deleteConfirm()
		{
			if (typeof vendHTMLid === 'undefined' || vendHTMLid == '')
			{ alert('No vendor is selected!'); return false; }
			else {
				var delVendName = $("#"+vendHTMLid).children('td').first().html()
				return confirm('Are you sure you want to delete '+delVendName+'? This action cannot be undone.');
			}
		}
		
		function displayVendorData(vendHTMLid) {
			if (window.check) { $(".highlightRed").removeClass("highlightRed"); }
			$('#'+vendHTMLid).addClass("highlightRed");
			window.check = true;
			window.vendHTMLid = vendHTMLid;
			document.vendorInfoForm.action = "vendors.php?save";

			$.ajax(
			{
				url: "ajax/echoVendorData.php?selected_id=" + vendHTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					$('#VendorID').val(data[0]);
					$('#Name').val(data[2]);
					$('#Balance').val(parseFloat(data[3]).toFixed(2));
					$('#Contact').val(data[5]);
					$('#AltContact').val(data[6]);
					$('#Address').val(data[4]);
					$('#Phone').val(convertphone(data[7],"touser"));
					$('#AltPhone').val(convertphone(data[8],"touser"));
					$('#Fax').val(convertphone(data[9],"touser"));
					
					$('#E-mail').val(data[10]);
					$('#CheckName').val(data[11]);
			
					$('#AcctNumber').val(data[12]);
					
					$('#Terms').val(data[13]);
					$('#TaxID').val(data[14]);
					$('#Note').val(data[16]);
					
					
					if (data[15] == "1") {$('#Elig1099').each(function(){ this.checked = true; });}
					else if (data[15] == "0") {$('#Elig1099').each(function(){ this.checked = false; });}
					
					if (data[1] == "1") {$('#Inactive').each(function(){ this.checked = true; });}
					else if (data[1] == "0") {$('#Inactive').each(function(){ this.checked = false; });}
					
					
				}
			});
			
			var htmlString = '';
			$.ajax(
			{
				url: "ajax/echoVendorTrans.php?selected_id=" + vendHTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					for (x=0; x <= (data.length - 1); x++)
					{
						htmlString = htmlString + '<tr><td>'+data[x][0]+'</td><td>'+convertdate(data[x][4],"touser")+'</td><td>'+data[x][2]+'</td><td>'+data[x][3]+'</td><td>'+data[x][5]+'</td><td>'+data[x][6]+'</td><td>'+data[x][7]+'</td><td>$'+data[x][9]+'</td></tr>';
					}
					
					$("#vendor-transactions-box table.vendor-transactions-table tbody").html(htmlString);
					
					// float table headers
						var $table = $('table.vendor-transactions-table');
						$table.floatThead({
							useAbsolutePositioning: false,
							scrollContainer: function($table) {
								return $table.closest('#vendor-transactions-box');
							}
						});
						$table.floatThead('reflow');
				}
			});
		}
		
		function scrollTo(vendHTMLid)
		{
			vendHTMLid = vendHTMLid.replace("csr","vendorSel");
			var row = document.getElementById(vendHTMLid);
			var c = row.getAttribute("class");
			c = parseInt(c.replace("r",""));
			$('#vendor-list-box').scrollTop((c*18)-(18*3));
		}
		
		$(document).ready(function() {
			//float table headers
			var $table = $('table.vendors');
			$table.floatThead({
				useAbsolutePositioning: false,
				scrollContainer: function($table) {
					return $table.closest('#vendor-list-box');
				}
			});
			
			//pull up previous vendor if any
			// not if entering new vendor
			<?php
			if (isset($_SESSION['lastVendorHtmlId']) && $_SESSION['lastVendorHtmlId'] != '' && !isset($_GET['new']))
			{
				$lastVendorId = $_SESSION['lastVendorHtmlId'];
				?>
					displayVendorData(<?php echo json_encode($lastVendorId); ?>);
					scrollTo(<?php echo json_encode($lastVendorId); ?>);
				<?php
			}
			?>
		});
	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>
<body class="vendors">
	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	<div id="admin_content">
		<h1>Vendors &raquo;
		<a class="buttonLink" href="vendors.php?new">
		<img class="button" src="images/addVendor.png" alt="create new vendor" title="create new vendor" height="32px" width="32px" /></a>
		
		<a class="buttonLink" href="vendors.php?delete" onclick="return deleteConfirm();">
		<img class="button" src="images/deleteVendor.png" alt="delete selected vendor" title="delete selected vendor" height="32px" width="32px" /></a>
		</h1>
			<hr />
			<div id="statusBox"></div>
			<?php
			echo '<div id="vendor-list-box">';
		
			$getvendors = "SELECT `VendorID`,`Name`,`Balance` FROM `vendors` ORDER BY `Name` ASC";
			$stmt = $Database->prepare($getvendors);
			if ($stmt->execute())
			{
				$result = $stmt->get_result();
				
				echo '<table class="vendors" cellpadding="0" border="0" cellspacing="0"><thead>';
				echo '<tr><th>Name</th><th>Balance</th></thead><tbody>';
				for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
				{
					$result->data_seek($n);
					$row = $result->fetch_array();
					
					//if ($n <200) { // only do 200 clients for development phase
					$htmlID = 'vendorSel'.$row[0];
					echo '<tr id="'.$htmlID.'" class="r'.($n+1).'" onclick="displayVendorData(this.id);">
					<td>'.$row[1].'</td>
					<td>'.number_format((float)$row[2], 2, '.', '').'</td></tr>'; //}
				}
				$result->close();
				echo '</tbody></table>';
			}
			else
			{
				echo "Query to retrieve all vendors' info failed";
			}
			echo '</div>';
			
			// Individual Vendor data box
			echo '
			<div id="vendor-detail-box">
			<form name="vendorInfoForm" id="VIF" method="post" onsubmit="return checkForm();" action="vendors.php?save'.((isset($_GET['new']) || !isset($_SESSION['lastVendorHtmlid']))?'&new':'').'">
			<table class="vendor-details" cellpadding="0" border="0" cellspacing="0">
			
			<tr><td>ID</td><td><span class="ht"><input id="VendorID" class="rogray" type="text" name="VendorID" value="" size="7" readonly="readonly" /><span class="tooltip">If creating a new vendor, an ID will be auto-assigned upon saving.</span></span></td><td></td><td></td>
			<td>Balance</td><td><input id="Balance" class="rogray" type="text" name="Balance" value="" readonly="readonly" size="7"</td>
			</tr>
			
			<tr><td>Name</td><td><input id="Name" type="text" name="Name" value="" tabindex="1" /></td>
			<td>Check Name</td><td class="ht"><input id="CheckName" type="text" name="CheckName" tabindex="9" /><span class="tooltip">This is the name that will be printed on checks</span></td><td></td><td></td></tr>
			
			<tr><td>Contact</td><td><input id="Contact" type="text" name="Contact" value="" tabindex="2" /></td>
			<td>Acct. No.</td><td><input type="text" id="AcctNumber" name="AcctNumber" value="" tabindex="10" /></td><td></td><td></td></tr>
			
			<tr><td>Alt. Contact</td><td><input type="text" id="AltContact" name="AltContact" value="" tabindex="3" /></td><td>Tax ID</td><td><input type="text" name="TaxID" id="TaxID" value="" tabindex="11" /></td><td></td><td></td></tr>
			
			<tr><td>Address</td><td rowspan="3"><textarea rows="3" name="Address" id="Address" tabindex="4"></textarea></td><td>Terms</td><td rowspan="3"><textarea name="Terms" id="Terms" tabindex="12"></textarea></td><td>Note</td><td><textarea name="Note" id="Note" tabindex="13"></textarea></td><td></td></tr>
			
			<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
			<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
			
			<tr><td>Phone</td><td><input type="text" name="Phone" id="Phone" value="" tabindex="5" /></td><td class="ht">Eligible for 1099?<span class="tooltip">Check this box if this vendor qualifies for the 1099 tax form.</span></td><td><input type="checkbox" name="Elig1099" id="Elig1099" /></td><td></td><td></td></tr>
			
			<tr><td>Alt. Phone</td><td><input type="text" name="AltPhone" id="AltPhone" value="" tabindex="6" /></td><td class="ht">Inactive?<span class="tooltip">If this box is checked, the vendor will be hidden by default.</span></td><td><input type="checkbox" name="Inactive" id="Inactive" /></td><td></td><td></td></tr>
			
			<tr><td>Fax</td><td><input type="text" name="Fax" id="Fax" value="" tabindex="7" /></td><td></td><td></td><td></td><td></td></tr>
			
			<tr><td>E-mail</td><td><input type="email" name="E-mail" id="E-mail" value="" tabindex="8" /></td><td></td><td></td><td></td><td style="text-align: right;">
		<input type="submit" value="save" name="submit" />&nbsp;
		<input type="button" value="reset fields" onclick="displayVendorData(vendHTMLid);" /></td></tr>
			
			</table>
			</form>
			</div>';
			
			// vendor transactions box
			echo '<div id="vendor-transactions-box">
				<table name="vendor-transactions-table" class="vendor-transactions-table" cellpadding="3" cellspacing="0">
				<thead>
					<tr><th colspan="8"><span class="subtable-title">Transactions history &raquo;</span></th></tr>
				<tr><th>Trans. ID</th><th>Date</th><th>Account</th><th>Type</th><th>Num</th><th>Payee</th><th>Memo</th><th>Amount</th></tr></thead><tbody>
			</tbody></table>';
			
			echo '</div>';
			
			
		if (!isset($_GET['new']))
		{
			// do nothing (if a vendor is in session it will pull up from $(document).ready function)
		}
		else
		{
			if (isset($_SESSION['lastVendorHtmlId'])) {unset($_SESSION['lastVendorHtmlId']);}
			?>
				<script type="text/javascript" language="javascript">
					if (window.vendHTMLid) {
					delete window.vendHTMLid; }
				</script>
			<?php
		}
		
		
		// check for save, or for delete
		if (isset($_GET['save']))
		{
			if ($_POST['VendorID'] != '') { $VendorID = $_POST['VendorID']; }
			else { $VendorID = ''; }
			if ($_POST['Balance'] != '') { $Balance = $_POST['Balance']; }
			else { $Balance = "0"; }
			$Name = $_POST['Name'];
			$Note = $_POST['Note'];
			$Contact = $_POST['Contact'];
			$AltContact = $_POST['AltContact'];
			$Address = $_POST['Address'];
			$Phone = $_POST['Phone']; // don't convert to only digits; user could save ext. number
			$AltPhone = $_POST['AltPhone'];
			$Fax = $_POST['Fax'];
			$Email = $_POST['E-mail'];
			$CheckName = $_POST['CheckName'];
			$AcctNumber = $_POST['AcctNumber'];
			$TaxID = $_POST['TaxID'];
			$Terms = $_POST['Terms'];
			if (isset($_POST['Elig1099']) && $_POST['Elig1099'] == "on")
			{ $Elig1099 = "1"; }
			else { $Elig1099 = "0"; }
			if (isset($_POST['Inactive']) && $_POST['Inactive'] == "on")
			{ $Inactive = "1"; }
			else { $Inactive = "0"; }
			
			
			if (!isset($_GET['new'])) {
				$saveVendor = 'REPLACE INTO vendors 
				SET `VendorID` = ?, `Inactive` = ?, `Name` = ?, `Balance` = ?, `Address` = ?, `Contact` = ?, `AltContact` = ?, `Phone` = ?, `AltPhone` = ?, `Fax` = ?, `E-mail` = ?, `PrintCheckName` = ?, `AccountNo` = ?, `Terms` = ?, `TaxID` = ?, `Elig1099` = ?, `Note` = ?';
			} else {
				$saveVendor = 'INSERT INTO vendors VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
			}
			$stmt = $Database->prepare($saveVendor);
			$stmt->bind_param('sssssssssssssssss',$VendorID,$Inactive,$Name,$Balance,$Address,$Contact,$AltContact,$Phone,$AltPhone,$Fax,$Email,$CheckName,$AcctNumber,$Terms,$TaxID,$Elig1099,$Note);
			if ($stmt->execute())
			{
				?>
				<script type="text/javascript">
					<?php if (isset($_GET['new'])) { echo '
					window.location = "vendors.php?vcc";'; } ?>
				</script>
				<?php
				$stmt->close();
			}
			else
			{
				?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Vendor info was not successfully saved.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
				</script>
				<?php
			}
		}
		elseif (isset($_GET['delete']))
		{
			if (isset($_SESSION['lastVendorHtmlId'])) 
			{
				$htmlID = $_SESSION['lastVendorHtmlId'];
				$vendorID = str_replace("vendorSel","",$htmlID);
				
				$stmt = $Database->prepare("DELETE FROM vendors WHERE VendorID = ".$vendorID);
				if ($stmt->execute()) 
				{
					unset($_SESSION['lastVendorHtmlId']);
					?>
					<script type="text/javascript">
					delete window.vendHTMLid;
					window.location = "vendors.php?dvc";
					</script>
					<?php
					$stmt->close();
				}
				else
				{
					?>
					<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Vendor was not deleted.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
				    </script>
				<?php
				}
			} 
		}
		elseif (isset($_GET['vcc']))
		{
			?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Saved vendor info successfully.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 2500);
			</script>
			<?php
		}
		elseif (isset($_GET['dvc']))
		{
			?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Successfully deleted the vendor.";
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