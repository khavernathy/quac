<?php 
/*
	employees.php
	Made by Douglas Franz, freelance PHP/MySQL/HTML/CSS/JS/jQuery-ist.
*/

session_start();
date_default_timezone_set('America/New_York');
ob_implicit_flush(true);
include('models/auth.php');
include('includes/calendar.php');
include('includes/database.php');
include('includes/datetime_functions.php');
include('includes/php_rounding_functions.php');
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
			
			// make sure not saving an existing employee name (to avoid later probs with ID cross-ref.)
			<?php
				$getemployees = "SELECT `FirstName`,`LastName` FROM `employees`";
				$stmt = $Database->prepare($getemployees);
				$empArray = array();
				if ($stmt->execute())
				{
					$result = $stmt->get_result();
					for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
					{
						$result->data_seek($n);
						$row = $result->fetch_array();
						array_push($empArray,$row[0].' '.$row[1]);
					}					
				}
				else
				{
					echo 'MySQL query failed. Contact administrator';
				}
			?>

			var empArray = <?php echo json_encode($empArray); ?>;
			console.log(empArray);
			var fn = $("#FirstName").val(); var ln = $("#LastName").val();
			console.log(fn + " " + ln);

			if ($("#FirstName").val() == '' || $("#LastName").val() == '')
			{ alert("You must at least include the employee's first and last name."); return false;}
			else if ($("#E-mail").val() != '' && !(email_regex.test($("#E-mail").val())))
			{ alert('Please enter a valid e-mail address.'); return false;}
			
			<?php
			if (isset($_GET['new'])) {
			?>
				else if (($.inArray(fn + " " + ln,empArray)) != -1) {
					alert("Cannot save a duplicated new employee name. Please change the name to something unique.");
					return false;
				}
			<?php
			}
			?>
			//return false;
			else {return true;}
		}
		
		function deleteConfirm()
		{
			if (typeof vendHTMLid === 'undefined' || vendHTMLid == '')
			{ alert('No employee is selected!'); return false; }
			else {
				var delVendName = $("#"+vendHTMLid).children('td').first().html()
				return confirm('Are you sure you want to delete '+delVendName+'? This action cannot be undone.');
			}
		}

		function gotoTicket(tickid) {
			window.location = "tickets.php?viewTicket="+tickid;
		}
		
		function displayEmployeeData(vendHTMLid) {
			if (window.check) { $(".highlightRed").removeClass("highlightRed"); }
			$('#'+vendHTMLid).addClass("highlightRed");
			window.check = true;
			window.vendHTMLid = vendHTMLid;
			<?php
			if (!isset($_GET['payroll'])) {
				?>
				document.employeeInfoForm.action = "employees.php?save";
				<?php
			}
			?>

			$.ajax(
			{
				url: "ajax/echoEmployeeData.php?selected_id=" + vendHTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					$('#EmployeeID').val(data[0]);
					$('#FirstName').val(data[7]);
					$('#LastName').val(data[8]);
					$('#Address').val(data[2]);
					$('#City').val(data[3]);
					$('#State').val(data[4]);
					$('#ZIP').val(data[5]);
					$('#Category').val(data[6]);
					
					$('#Phone').val(convertphone(data[9],"touser"));
					$('#AltPhone').val(convertphone(data[10],"touser"));
					
					$('#Gender').val(data[11]);
					$('#SSN').val(data[12]);
					$('#CertNo').val(data[13]);
					$('#Comment').val(data[14]);
					$('#DOB').val(convertdate(data[15],"touser"));
					$('#DOH').val(convertdate(data[16],"touser"));
					
					$('#SSN').val(data[12]);
					$('#E-mail').val(data[17]);
					$('#DefaultCut').val(data[18]);
					$('#DefaultProductCut').val(data[19]);

					if (data[1] == "1") {$('#Inactive').each(function(){ this.checked = false; });}
					else if (data[1] == "0") {$('#Inactive').each(function(){ this.checked = true; });}
					
					
				}
			});
			
			var htmlString = '';
			// clear the transactions box first in case there is no new data
			$("#employee-transactions-box table.employee-transactions-table tbody").html(htmlString);
			
			$.ajax(
			{
				url: "ajax/echoEmployeeTrans.php?selected_id=" + vendHTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					for (x=0; x <= (data.length - 1); x++)
					{
						htmlString = htmlString + '<tr><td>'+data[x][0]+'</td><td>'+convertdate(data[x][4],"touser")+'</td><td>'+data[x][2]+'</td><td>'+data[x][3]+'</td><td>'+data[x][5]+'</td><td>'+data[x][6]+'</td><td>'+data[x][7]+'</td><td>$'+data[x][9]+'</td></tr>';
					}
					
					$("#employee-transactions-box table.employee-transactions-table tbody").html(htmlString);
					//console.log('employee transactions by name retrieval worked');
					//console.log(htmlString);
					
					// float table headers
						var $table = $('table.employee-transactions-table');
						$table.floatThead({
							useAbsolutePositioning: false,
							scrollContainer: function($table) {
								return $table.closest('#employee-transactions-box');
							}
						});
						$table.floatThead('reflow');
				}
			});
		}
		
		<?php
		if (!isset($_GET['payroll']))
		{
			?>
			function scrollTo(vendHTMLid)
			{
				vendHTMLid = vendHTMLid.replace("csr","employeeSel");
				var row = document.getElementById(vendHTMLid);
				var c = row.getAttribute("class");
				c = parseInt(c.replace("r",""));
				$('#employee-list-box').scrollTop((c*18)-(18*3));
			}
			<?php
		}
		?>
		
		$(document).ready(function() {
			//float table headers
			var $table = $('table.employees');
			$table.floatThead({
				useAbsolutePositioning: false,
				scrollContainer: function($table) {
					return $table.closest('#employee-list-box');
				}
			});
			
			//pull up previous employee if any
			// not if entering new employee
			<?php
			if (isset($_SESSION['lastEmployeeHtmlId']) && $_SESSION['lastEmployeeHtmlId'] != '' && !isset($_GET['new']) && !isset($_GET['payroll']))
			{
				$lastEmployeeId = $_SESSION['lastEmployeeHtmlId'];
				?>
					displayEmployeeData(<?php echo json_encode($lastEmployeeId); ?>);
					scrollTo(<?php echo json_encode($lastEmployeeId); ?>);
				<?php
			}
			?>
		});
	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>
<body class="employees">
	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	<div id="admin_content">
		<h1>Employees &raquo;
		<a class="buttonLink" href="employees.php?new">
		<img class="button" src="images/addVendor.png" alt="create new employee" title="create new employee" height="32px" width="32px" /></a>
		
		<a class="buttonLink" href="employees.php?delete" onclick="return deleteConfirm();">
		<img class="button" src="images/deleteVendor.png" alt="delete selected employee" title="delete selected employee" height="32px" width="32px" /></a>

		<a class="buttonLink" href="employees.php?payroll">
		<img class="button" src="images/payroll.png" alt="run employee payroll" title="run employee payroll" height="32px" width="32px" /></a>
		

		</h1>
			<hr />
			<div id="statusBox"></div>
		
		<?php
		// DEFAULT EMPLOYEE VIEW
		if (!isset($_GET['payroll']))
		{		
			echo '<div id="employee-list-box">';
		
			if (isset($_GET['inactive'])) { $act = 'WHERE `Active`= "0" OR `Active` = "1"'; }
			else { $act = 'WHERE `Active`=1'; }

			$getemployees = "SELECT `EmployeeID`,`FirstName`,`LastName`, `Active` FROM `employees` $act ORDER BY `FirstName` ASC";
			$stmt = $Database->prepare($getemployees);
			if ($stmt->execute())
			{
				$result = $stmt->get_result();
				
				echo '<table class="employees" cellpadding="0" border="0" cellspacing="0"><thead>';
				echo '<tr><th>ID</th><th>Name - ';
				if (!isset($_GET['inactive'])) {
					echo '[<a href="employees.php?inactive">Show inactive employees</a>]'; }
			    else {
					echo '[<a href="employees.php">Hide inactive employees</a>]';
			    }
				echo '</th></thead><tbody>';

				for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
				{
					$result->data_seek($n);
					$row = $result->fetch_array();
					
					//if ($n <200) { // only do 200 emps for development phase
				
					if ($row[3] == '0') {
						$itext = "--- INACTIVE";
					} else {
						$itext = "";
					}
					$htmlID = 'employeeSel'.$row[0];
					echo '<tr id="'.$htmlID.'" class="r'.($n+1).'" onclick="displayEmployeeData(this.id);">
					<td>'.$row[0].'</td>
					<td>'.$row[1].' '.$row[2].$itext.'</td></tr>'; //}
				}
				$result->close();
				echo '</tbody></table>';
			}
			else
			{
				echo "Query to retrieve all employees' info failed";
			}
			echo '</div>';
			
			// Individual Employee data box
			echo '
			<div id="employee-detail-box">
			<form name="employeeInfoForm" id="EIF" method="post" onsubmit="return checkForm();" action="employees.php?save'.((isset($_GET['new']) || !isset($_SESSION['lastEmployeeHtmlid']))?'&new':'').'">
			<table class="employee-details" cellpadding="0" border="0" cellspacing="0">';
			?>
			
			<tr><td>ID</td><td><span class="ht"><input id="EmployeeID" class="rogray" type="text" name="EmployeeID" value="" size="7" readonly="readonly" /><span class="tooltip">If creating a new employee, an ID will be auto-assigned upon saving.</span></span></td>
			<td>Category</td><td><input id="Category" name="Category" type="text" name="Category" value="" tabindex="10" /></td>
			<td style="text-align:left" class="ht">Default wage fraction: <input type="text" name="DefaultCut" id="DefaultCut" tabindex="17" size="5" /><span class="tooltip">e.g. 0.42 for 42% -- this can be changed when actually running payroll.</span></td>
			
			</tr>
			
			<tr><td>First Name</td><td class="ht"><input id="FirstName" type="text" name="FirstName" value="" tabindex="1" /><span class="tooltip">No spaces here please [one word]</span></td>
			<td>Gender</td><td><input id="Gender" type="text" name="Gender" tabindex="11" /></td><td style="text-align:left" class="ht">Default product commission: <input type="text" name="DefaultProductCut" id="DefaultProductCut" tabindex="17" size="5" /><span class="tooltip">e.g. 0.10 for 10% -- this can be changed when actually running payroll.</span></td></tr>
			
			<tr><td>Last Name</td><td><input id="LastName" type="text" name="LastName" value="" tabindex="2" /></td>
			<td>Cert. No.</td><td><input type="text" name="CertNo" id="CertNo" tabindex="12" /></td>
			<td></td><td></td></tr>
			
			<tr><td>Address</td><td><input type="text" name="Address" id="Address" tabindex="3"></input></td>
			<td>SSN</td><td><input type="text" id="SSN" name="SSN" value="" tabindex="13" /></td>
			<td></td><td></td></tr>
			
			<tr><td>City</td><td><input type="text" name="City" id="City" tabindex="4" /></td>
			<td>Date of Birth</td><td class="ht"><input type="text" name="DOB" id="DOB" tabindex="14" /><span class="tooltip">MM-DD-YYYY format only.</span></td>
			<td></td><td></td></tr>
			
			<tr><td>State</td><td><input type="text" name="State" id="State" tabindex="5" /></td>
			<td>Date of Hire</td><td class="ht"><input type="text" name="DOH" id="DOH" tabindex="15" /><span class="tooltip">MM-DD-YYYY format only.</span></td>
			<td></td><td></td></tr>
			
			<tr><td>ZIP</td><td><input type="text" name="ZIP" id="ZIP" tabindex="6" /></td>
			<td>Comment</td><td rowspan="3"><textarea name="Comment" id="Comment" tabindex="16"></textarea></td>
			<td></td><td></td></tr>
			
			<tr><td>Phone</td><td><input type="text" name="Phone" id="Phone" value="" tabindex="7" /></td>
			<td></td><td></td><td></td><td></td></tr>
			
			<tr><td>Alt. Phone</td><td><input type="text" name="AltPhone" id="AltPhone" value="" tabindex="8" /></td>
			<td></td><td></td><td></td></tr>
			
			<tr><td>E-mail</td><td><input type="email" name="E-mail" id="E-mail" value="" tabindex="9" /></td>
			<td>Inactive?</td>
			<td class="ht"><input type="checkbox" name="Inactive" id="Inactive" /><span class="tooltip">If this box is checked, the employee will be hidden by default.</span></td>
			<td style="text-align: right;">
				
		<input type="submit" value="save" name="submit" />&nbsp;
		<input type="button" value="reset fields" onclick="displayEmployeeData(vendHTMLid);" /></td></tr>
			
			</table>
			</form>
			</div>
			<?php
			
			// employee transactions box
			echo '<div id="employee-transactions-box">
				<table name="employee-transactions-table" class="employee-transactions-table" cellpadding="3" cellspacing="0">
				<thead>
					<tr><th colspan="8"><span class="subtable-title">Transactions history &raquo;</span></th></tr>
					<tr><th>Trans. ID</th><th>Date</th><th>Account</th><th>Type</th><th>Num</th><th>Payee</th><th>Memo</th><th>Amount</th></tr></thead><tbody>
			</tbody></table>';
			
			echo '</div>';
		
		// PAYROLL MECHANISM ============================================================PAYROLL
		}
		else { // $_GET['payroll'] is set.
			$today = date("m-d-Y",time());
			?>
			<h3>Current Payroll: <?php echo $today; ?> &raquo; double-click a row to go to the respective ticket.</h3>
			<div id="payroll-box" class="payroll-box">
			<table name="payroll-table" id="payroll-table" class="payroll-table">
			<thead>
			<tr>
				<th>Item ID</th>
				<th>Employee ID</th>
				<th>Employee Name</th>
				<th>Type</th>
				<th>Date</th>
				<th>Description</th>
				<th>Client</th>
				<th>Price ($)</th>
				<th>Quantity</th>
				<th>Subtotal ($)</th>
				<th>Fraction (%)</th>
				<th>Total ($)</th>
			</tr>
			</thead>

			<?php
			$stmt = $Database->prepare("SELECT * FROM `current_payroll` ORDER BY `EmployeeName` ASC");
			if ($stmt->execute())
			{

				// do a fancy color loop of employees.
				//$colors = array("#999966","#00ff00","#3399ff","#ffffff","#cdcdcd","#ffff00","#ff9933","#cc33ff");
				$colors = array("#ffffff","#cdcdcd","#ff9933","#00dd00");
				$cc = 0; // color counter.
				$unique_emps = array();
				$emp_totals = array();
				$emp_business_totals = array();
				$result = $stmt->get_result();
				for ($n=0; $n <= ($result->num_rows - 1); $n++)
				{
					$result->data_seek($n);
					$row = $result->fetch_array();
					$ticketrowid = $row[1]; //echo $ticketrowid.'<br />';

					// first grab the parent ticket ID using the item ID (ticketdetails row ID)
					$query = "SELECT `TicketID` FROM `ticketdetails` WHERE `RowID` = '$ticketrowid'";
					$stmt_ticketid = $Database->prepare($query);
					if ($stmt_ticketid->execute())
					{
						$result2 = $stmt_ticketid->get_result(); // result2 so we don't overwrite the result variabel.
						//$result->data_seek(0);
						$ticketidarr = $result2->fetch_array();
						$ticketid = $ticketidarr[0]; //var_dump($result);
						
					}
					else 
					{
						// ticket id retrieval failed but we can skip it..
						echo 'error; query failed';
					}
					$stmt_ticketid->close();


					if (isset($currEmp) && $currEmp != $row[3]) 
					{
						if ($cc != count($colors) - 1) {
							$cc++;
						} else {
							$cc = 0;
						}
						echo '<tr style="background-color:'.$colors[$cc].'" ondblclick="gotoTicket('.$ticketid.');">';
						
					} else { echo '<tr style="background-color:'.$colors[$cc].'" ondblclick="gotoTicket('.$ticketid.');">'; }
					$currEmp = $row[3];
					if ($row[6] == "S") { $type = "Service"; } elseif ($row[6] == "P") { $type = "Product"; } else {$type = '';}

					// display row
					echo '<td>'.$row[1].'</td><td>'.$row[2].'</td><td>'.$row[3].'</td><td>'.$type.'</td><td>'.convertdate($row[14],"touser").'</td><td>'.$row[7].'</td><td>'.$row[8].'</td><td>'.$row[9].'</td><td>'.$row[10].'</td><td>'.$row[11].'</td><td>'.round($row[12],3).'</td><td>'.$row[13].'</td>';
					echo '</tr>';

					// establish employee array and totals array
					if (!in_array($row[3],$unique_emps)) {
						//array_push($unique_emps,$row[3]);
						$unique_emps[$row[2]] = $row[3];
						$emp_totals[$row[2]] = $row[13];
						$emp_business_totals[$row[2]] = $row[11];
					} else {
						$emp_totals[$row[2]] = $emp_totals[$row[2]] + $row[13];
						$emp_business_totals[$row[2]] = $emp_business_totals[$row[2]] + $row[11];
					}
				}
				$stmt->close();
			}
			else
			{
				echo 'Failed to retrieve payroll data. Contact administrator.';
			}
			?>

			</table>
			</div>	
		<?php
		// DISPLAY TOTALS!!!
		echo '<br /><hr /><br /><h3>Employee Totals: </h3>';
		echo '<table id="payroll-totals" name="payroll-totals" class="payroll-table-totals"><thead><tr><th>Employee ID</th><th>Employee Name</th><th>Net Business ($)</th><th>Total ($) for this pay-period</th><th>Earned / provided ratio (%)</th></tr></thead>
		<tbody>';

		$sum=0.0;
		$business_sum = 0.0;
		foreach ($unique_emps as $k => $v)
		{
			echo '<tr><td>'.$k.'</td><td>'.$v.'</td><td style="text-align:right;">'.round2($emp_business_totals[$k]).'</td><td style="text-align:right;">'.round2($emp_totals[$k]).'</td><td style="text-align:right;">'.round1(($emp_totals[$k] / $emp_business_totals[$k])*100).'</td></tr>';
			$sum += $emp_totals[$k];
			$business_sum += $emp_business_totals[$k];
		}
		echo '<tr style="background-color:#ffffff;"><td></td><td><b>Total:</b></td><td style="text-align:right;"><b>'.round2($business_sum).'</b></td><td style="text-align:right;"><b>'.round2($sum).'</b></td><td style="text-align:right;"><b>'.round1($sum / $business_sum * 100).'</b></td></tr>';
	
		echo '</tbody></table>';

		}
		// --------------------------------------------
		// ====================== END PAYROLL FUNCTIONS.

			
		if (!isset($_GET['new'])) // i.e. creating a new employee
		{
			// do nothing (if a employee is in session it will pull up from $(document).ready function)
		}
		else
		{
			if (isset($_SESSION['lastEmployeeHtmlId'])) {unset($_SESSION['lastEmployeeHtmlId']);}
			?>
				<script type="text/javascript" language="javascript">
					if (window.vendHTMLid) {
					delete window.vendHTMLid; }
				</script>
			<?php
		}
		
		
		// check for save, or for delete
		if (isset($_GET['save'])) // save the employee information
		{
			if ($_POST['EmployeeID'] != '') { $EmployeeID = $_POST['EmployeeID']; }
			else { $EmployeeID = ''; }
			//if ($_POST['Balance'] != '') { $Balance = $_POST['Balance']; }
			//else { $Balance = "0"; }
			$FirstName = $_POST['FirstName'];
			$LastName = $_POST['LastName'];
			//$Note = $_POST['Note'];
			//$Contact = $_POST['Contact'];
			//$AltContact = $_POST['AltContact'];
			$Address = $_POST['Address'];
			$City = $_POST['City'];
			$State = $_POST['State'];
			$ZIP = $_POST['ZIP'];
			$Phone = $_POST['Phone']; // don't convert to only digits; user could save ext. number
			$AltPhone = $_POST['AltPhone'];
			$Gender = $_POST['Gender'];
			$SSN = $_POST['SSN'];
			//$Fax = $_POST['Fax'];
			$Email = $_POST['E-mail'];
			$Category = $_POST['Category'];
			$CertNum = $_POST['CertNo'];
			$Comment = $_POST['Comment'];

			$DOB = convertdate($_POST['DOB'],'tomysql');
			$DOH = convertdate($_POST['DOH'],'tomysql');

			$DefaultCut = $_POST['DefaultCut'];
			$DefaultProductCut = $_POST['DefaultProductCut'];
			//$CheckName = $_POST['CheckName'];
			//$AcctNumber = $_POST['AcctNumber'];
			//$TaxID = $_POST['TaxID'];
			//$Terms = $_POST['Terms'];
			//if (isset($_POST['Elig1099']) && $_POST['Elig1099'] == "on")
			//{ $Elig1099 = "1"; }
			//else { $Elig1099 = "0"; }
			if (isset($_POST['Inactive']) && $_POST['Inactive'] == "on")
			{ $Active = "0"; }
			else { $Active = "1"; }
			
			
			if (!isset($_GET['new'])) {
				$saveEmployee = 'REPLACE INTO employees 
				SET `EmployeeID` = ?, `Active` = ?, `Address` = ?, `City` = ?, `State` = ?, `ZIP` = ?, `Category` = ?, `FirstName` = ?, `LastName` = ?, `Phone` = ?, `Phone2` = ?, `Gender` = ?, `SSN` = ?, `CertNum` = ?, `Comment` = ?, `DateOfBirth` = ?, `DateOfHire` = ?, `E-mail` = ?, `DefaultCut` = ?, `DefaultProductCut` = ?';
			} else {
				$saveEmployee = 'INSERT INTO employees VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
			}
			$stmt = $Database->prepare($saveEmployee);
			$stmt->bind_param('ssssssssssssssssssss',$EmployeeID,$Active,$Address,$City,$State,$ZIP,$Category,$FirstName,$LastName,$Phone,$AltPhone,$Gender,$SSN,$CertNum,$Comment,$DOB,$DOH,$Email,$DefaultCut,$DefaultProductCut);
			if ($stmt->execute())
			{

				// great; the employee save worked. Now make sure we save the default cut if it changed for this employee, in the current_payroll table
				if (!isset($_GET['new'])) {
					$eid = $EmployeeID;
				} else {
					$eid = $Database->insert_id;
				}
				$stmt->close();

				// first take care of service cut (fraction earned)
				$updateFraction = 'UPDATE current_payroll
				SET `Fraction` = ?, `Total` = ? * `SubTotal`
				WHERE `EmployeeID` = ? AND `Type` = ?';
				$stmt = $Database->prepare($updateFraction);
				$stmt->bind_param('ssss',$DefaultCut,$DefaultCut,$eid,$t='S');
				if ($stmt->execute())
				{
					// good
					$stmt->close();
					// now product cut.
					$updateFraction = 'UPDATE current_payroll
					SET `Fraction` = ?, `Total` = ? * `SubTotal`
					WHERE `EmployeeID` = ? AND `Type` = ?';
					$stmt = $Database->prepare($updateFraction);
					$stmt->bind_param('ssss',$DefaultProductCut,$DefaultProductCut,$eid,$t='P');
					if ($stmt->execute())
					{
						// good. reload with confirmation
						?>
						<script type="text/javascript">
							window.location = "employees.php?ecc";
						</script>
						<?php
					}
					else
					{
						// error
							?>
							<script type="text/javascript">
							document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Employee info was not successfully saved.";
								$("#statusBox").fadeIn();
								setTimeout(function(){
									$("#statusBox").fadeOut();
								}, 7000);
							</script>
						<?php
					}
				}
				else
				{
					// error
					?>
					<script type="text/javascript">
						document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Employee info was not successfully saved.";
						$("#statusBox").fadeIn();
						setTimeout(function(){
							$("#statusBox").fadeOut();
						}, 7000);
					</script>
				<?php
				}
				
			}
			else
			{
				?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Employee info was not successfully saved.";
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
			if (isset($_SESSION['lastEmployeeHtmlId'])) 
			{
				$htmlID = $_SESSION['lastEmployeeHtmlId'];
				$employeeID = str_replace("employeeSel","",$htmlID);
				
				$stmt = $Database->prepare("DELETE FROM employees WHERE EmployeeID = ".$employeeID);
				if ($stmt->execute()) 
				{
					unset($_SESSION['lastEmployeeHtmlId']);
					?>
					<script type="text/javascript">
					delete window.vendHTMLid;
					window.location = "employees.php?dvc";
					</script>
					<?php
					$stmt->close();
				}
				else
				{
					?>
					<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Employee was not deleted.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
				    </script>
				<?php
				}
			} 
		}
		elseif (isset($_GET['ecc']))
		{
			?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Saved employee info successfully.";
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
			document.getElementById('statusBox').innerHTML = "Successfully deleted the employee.";
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
