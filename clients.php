<?php 
/*
	/clients.php
	Handles client list 
	Made by Douglas Franz, freelance PHP/MySQL/HTML/CSS/JS/jQuery-ist.
*/

session_start();
date_default_timezone_set('America/New_York');
ob_implicit_flush(true);
//@ini_set('zlib.output_compression', 1); // apparently makes the page-load faster due to compression?

include('models/auth.php');
include('includes/calendar.php'); 
include('includes/database.php');
include('includes/datetime_functions.php');
include('includes/php_string_function.php');
require 'phpmailer/PHPMailerAutoload.php';
// ini_set('max_input_vars', 10000); //value is actually set in php.ini and it works. woot.
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Quac Admin Area</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/__jqft.js"></script>
	<script type="text/javascript" language="javascript">
		
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
	
		function displayClientData(HTMLid) 
		{
		
			<?php
			if (isset($_GET['sendEmails']) || isset($_GET['doEmailSend']))
			{
				// ajax call to set JS HTMLid to PHP session var
				?>
				$.ajax(
				{
					url: "ajax/saveClientIDToSession.php?selected_id=" + HTMLid,
					type: 'GET',
					dataType: 'json',
					success: function(data)
					{
						// redirect (and thus autoload selected client). this must be INSIDE the success function
						window.location = "clients.php";
					}
				});
				<?php
			}
			?>
			
			if (window.check) { $(".highlightRed").removeClass("highlightRed"); }
			var index = HTMLid.indexOf("csr");
			if (index != -1) { HTMLid = HTMLid.replace("csr","clientSel"); }
			$('#'+HTMLid).addClass("highlightRed");
			window.check = true;
			window.HTMLid = HTMLid;
			document.clientInfoForm.action = "clients.php?save";

			$.ajax(
			{
				url: "ajax/echoClientData.php?selected_id=" + HTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					$('#ClientID').val(data[0]);
					if (data[1] == "n/a") { document.getElementById('Title').selectedIndex = "0";}
					else if (data[1] == "Mr.") { document.getElementById('Title').selectedIndex = "1"; }
					else if (data[1] == "Ms.") { document.getElementById('Title').selectedIndex = "2"; }
					else if (data[1] == "Mrs.") { document.getElementById('Title').selectedIndex = "3"; }
					else { document.getElementById('Title').selectedIndex = "0"; }
					$('#FirstName').val(data[2]);
					$('#LastName').val(data[3]);
					$('#Address').val(data[4]);
					$('#City').val(data[5]);
					$('#State').val(data[6]);
					$('#ZIP').val(data[7]);
					if (data[8] == "M") { document.getElementById('Gender').selectedIndex = "1";}
					else if (data[8] == "F") { document.getElementById('Gender').selectedIndex = "2"; }
					$('#CellPhone').val(convertphone(data[9],"touser"));
					$('#HomePhone').val(convertphone(data[10],"touser"));
					$('#WorkPhone').val(convertphone(data[11],"touser"));
					$('#PrimaryPhone').val(convertphone(data[12],"touser"));
						if ($('#CellPhone').val() == $('#PrimaryPhone').val())
						{$('#PriCell').prop("checked",true);}
						else if ($('#HomePhone').val() == $('#PrimaryPhone').val())
						{$('#PriHome').prop("checked",true);}
						else if ($('#WorkPhone').val() == $('#PrimaryPhone').val())
						{$('#PriWork').prop("checked",true);}
					$('#E-mail').val(data[13]);
					$('#Occupation').val(data[14]);
					if (data[15] != "0000-00-00") {$('#DateOfBirth').val(convertdate(data[15],"touser"));}
					if (data[16] != "0000-00-00") {$('#Anniversary').val(convertdate(data[16],"touser"));}
					$('#Balance').val(parseFloat(data[17]).toFixed(2));
					$('#BalanceComment').val(data[18]);
					if (data[19] == "1") {$('#AptEmail').each(function(){ this.checked = true; });} 
					else {$('#AptEmail').each(function(){ this.checked = false; });}
					$('#ClientHistory').val(data[20]);
					$('#Comment').val(data[21]);
					$('#FirstEmployeeID').val(data[22]);
					$('#FirstEmployeeName').val(data[23]);
					if (data[24] != "0000-00-00") {$('#FirstVisit').val(convertdate(data[24],"touser"));}
					$('#LastEmployeeID').val(data[25]);
					$('#LastEmployeeName').val(data[26]);
					if (data[27] != "0000-00-00") {$('#LastVisit').val(convertdate(data[27],"touser"));}
					$('#TotalVisits').val(data[28]);
					if (data[29] == "1") {$('#Inactive').each(function(){ this.checked = true; });}
					else if (data[29] == "0") {$('#Inactive').each(function(){ this.checked = false; });}
					$('#NoShow').val(data[30]);
			
				}
			});
			
			$.ajax(
			{
				url: "ajax/echoClientTickets.php?selected_id=" + HTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					if ($("#cs")) {
						$("#cs").remove(); // remove client transaction sum box if it's there
					}
						
					if (Array.isArray(data) == true) {
						htmlString = '';
						sum = 0.00;
						for (x=1; x <= data.length; x++)
						{
							var apptLink = "tickets.php?viewTicket="+data[x-1][0];
							htmlString = htmlString + '<tr onclick="window.location=&#39;'+apptLink+'&#39;;"><td>'+data[x-1][0]+'</td><td>'+data[x-1][2]+'</td><td>'+convertdate(data[x-1][4],"touser")+'</td><td>'+data[x-1][9]+'</td><td>$'+data[x-1][24]+'</td></tr>';
							
							sum = sum + parseFloat(data[x-1][24])
						}
						
						s = String((sum * 100 / 100).toFixed(2));
						//console.log("transactions sum for this client: " + s);
						
						$("#tdh").append("<span id='cs' style='text-align: right;'>Total: $" + s + "</span>");
						
						$("#client-transactions-box table.client-tickets-table tbody").html(htmlString);
						// float table headers
						var $table = $('table.client-tickets-table');
						$table.floatThead({
							useAbsolutePositioning: false,
							scrollContainer: function($table) {
								return $table.closest('#client-tickets-table-box');
							}
						});
						$table.floatThead('reflow');
					}
					else {
						console.log("transactions sum for this client: 0.00");
						$("#client-transactions-box table.client-tickets-table tbody").html(data);
					}
				}
			});
			
			$.ajax(
			{
				url: "ajax/echoClientServices.php?selected_id=" + HTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					if ($("#ss")) {
						$("#ss").remove(); // remove client service sum box if it's there
					}
					
					if (Array.isArray(data) == true) {
						htmlString = '';
						ssum = 0.00;
						for (x=1; x <= data.length; x++)
						{
							var apptLink = "tickets.php?viewTicket="+data[x-1][1];
							htmlString = htmlString + '<tr onclick="window.location=&#39;'+apptLink+'&#39;;"><td>'+convertdate(data[x-1][8],"touser")+'</td><td>'+data[x-1][14]+'</td><td>'+data[x-1][15]+'</td><td>$'+data[x-1][16]+'</td></tr>';
							
							ssum = ssum + parseFloat(data[x-1][16]);
						}
						
						ssum = String((ssum * 100 / 100).toFixed(2));
						
						$("#tsh").append("<span id='ss' style='text-align: right;'>Total: $" + ssum + "</span>");
						
						$("#client-transactions-box table.client-services-table tbody").html(htmlString);
						// float table headers
						var $table = $('table.client-services-table');
						$table.floatThead({
							useAbsolutePositioning: false,
							scrollContainer: function($table) {
								return $table.closest('#client-services-table-box');
							}
						});
						$table.floatThead('reflow');
					}
					else {
						$("#client-transactions-box table.client-services-table tbody").html(data);
					}
				}
			});
			
			$.ajax(
			{
				url: "ajax/echoClientProducts.php?selected_id=" + HTMLid,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					if ($("#ps")) {
						$("#ps").remove(); // remove client product sum box if it's there
					}
					
					if (Array.isArray(data) == true) {
						htmlString = '';
						psum = 0.00;
						
						for (x=1; x <= data.length; x++)
						{
							var apptLink = "tickets.php?viewTicket="+data[x-1][1];
							htmlString = htmlString + '<tr onclick="window.location=&#39;'+apptLink+'&#39;;"><td>'+convertdate(data[x-1][8],"touser")+'</td><td>'+data[x-1][15]+'</td><td>$'+data[x-1][16]+'</td><td>'+data[x-1][17]+'</td><td>$'+data[x-1][18]+'</td></tr>';
							
							psum = psum + parseFloat(data[x-1][18]);
						}
						
						psum = String((psum * 100 / 100).toFixed(2));
						
						$("#tph").append("<span id='ps' style='text-align: right;'>Total: $" + psum + "</span>");
						
						$("#client-transactions-box table.client-products-table tbody").html(htmlString);
						// float table headers
						var $table = $('table.client-products-table');
						$table.floatThead({
							useAbsolutePositioning: false,
							scrollContainer: function($table) {
								return $table.closest('#client-products-table-box');
							}
						});
						$table.floatThead('reflow');
					}
					else {
						$("#client-transactions-box table.client-products-table tbody").html(data);
					}
				}
			});
		}
		
		function showEmailForm()
		{
			$('#sendEmailSubmitBox').html('<input type="submit" value="send e-mail" id="sendEmailSubmit" />');
			$('#sendEmailSubmitBox').fadeIn();
			console.log('showEmailForm() ran.');
		}
		
		function scrollTo(HTMLid)
		{
			HTMLid = HTMLid.replace("csr","clientSel");
			var row = document.getElementById(HTMLid);
			var c = row.getAttribute("class");
			c = parseInt(c.replace("r",""));
			$('#client-list-box').scrollTop((c*20)-45);
		}
		
		function hideSearch()
		{
			$("#statusBox").fadeOut();
			document.getElementById('clientSearchFirstName').value = '';
			document.getElementById('clientSearchLastName').value = '';
			document.getElementById('clientSearchPhone').value = '';
		}
		
		function checkForm()
		{
			email_regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			date_regex =  /^\d{2}\-\d{2}\-\d{4}$/;
			if ($("#FirstName").val() == '' || $("#LastName").val() == '')
			{ alert('You must at least include a first and last name.'); return false;}
			else if ($("#E-mail").val() != '' && !(email_regex.test($("#E-mail").val())))
			{ alert('Please enter a valid e-mail address.'); return false;}
			else if (
			($("#DateOfBirth").val() != '' && !(date_regex.test($("#DateOfBirth").val()))) || ($("#Anniversary").val() != '' && !(date_regex.test($("#Anniversary").val())))
			)
			{ alert('Birthday and anniversary fields must use MM-DD-YYYY format'); return false;}
			else {return true;}
		}
		
		function deleteConfirm()
		{
			if (typeof HTMLid === 'undefined' || HTMLid == '')
			{ alert('No client is selected!'); return false; }
			else {
				var delCliName = $("#"+HTMLid).children('td:nth-child(2)').html()
				return confirm('Are you sure you want to delete '+delCliName+'? This action cannot be undone.');
			}
		}
		
		function confirmAdd()
		{
			if (!window.HTMLid || window.HTMLid == '')
			{ 
				alert('There is no client selected!');
				return false;
			} else {
				var clientID = window.HTMLid.replace("clientSel","");
				var tiCheck = false; var emCheck = false; dayCheck = false;
				<?php 
				if (isset($_GET['time'])) {
				?>				
					var apptTime = <?php echo json_encode($_GET['time']); ?>;
					tiCheck = true;
				<?php
				}
				if (isset($_GET['employee'])) {
				?>
					var apptEmployee = <?php echo json_encode($_GET['employee']); ?>;
					emCheck = true;
				<?php 
				} 
				if (isset($_GET['date'])) {
				?>				
					var apptDate = <?php echo json_encode($_GET['date']); ?>;
					dayCheck = true;
				<?php
				}
				?>

				// go diego go
				window.location = "tickets.php?addAppt&client="+clientID + ((tiCheck)?"&time=" + apptTime :"") + ((emCheck)?"&employee=" + apptEmployee : "") + ((dayCheck)?"&day=" + apptDate : "");
			}
		}
		
		function checkBoxes()
		{
			if (typeof checkboxesStatus === 'undefined' || checkboxesStatus == "off" || checkboxesStatus == '')
			{
				$(".emailChecks").each(function() {
					this.checked = true;
				});
				checkboxesStatus = "on";
			}
			else if (checkboxesStatus == "on")
			{
				$(".emailChecks").each(function() {
					this.checked = false;
				});
				checkboxesStatus = "off";
			}
		}
		
		// function to handle live search
		function runLiveSearch() {
			if (typeof $table === 'undefined' || $table == '')
			{ console.log('$table was not detected...');
			} else {
				$table.trigger('reflow'); console.log('reflowed table');
			}
		
			var search_string = $(this).val();
			var which = this.id;
			which = which.replace("clientSearch","");
			var getVars = '';
			// check for other input fields
			if (which == "FirstName") {
				if (search_string != '') {getVars='FirstName='+search_string;}
				if ($('#clientSearchLastName').val() != '') {
					getVars = getVars + '&LastName='+$('#clientSearchLastName').val();
				}
				if ($('#clientSearchPhone').val() != '') {
					getVars = getVars + '&Phone='+$('#clientSearchPhone').val();
				}
			}
			else if (which == "LastName") {
				if (search_string != '') {getVars='LastName='+search_string;}
				if ($('#clientSearchFirstName').val() != '') {
					getVars = getVars + '&FirstName='+$('#clientSearchFirstName').val();
				}
				if ($('#clientSearchPhone').val() != '') {
					getVars = getVars + '&Phone='+$('#clientSearchPhone').val();
				}
			}
			else if (which == "Phone") {
				if (search_string != '') {getVars='Phone='+search_string;}
				if ($('#clientSearchFirstName').val() != '') {
					getVars = getVars + '&FirstName='+$('#clientSearchFirstName').val();
				}
				if ($('#clientSearchLastName').val() != '') {
					getVars = getVars + '&LastName='+$('#clientSearchLastName').val();
				}
			}

			if ($('#clientSearchPhone').val() == '' && $('#clientSearchFirstName').val() == '' && $('#clientSearchLastName').val() == '') {
				$("#statusBox").fadeOut();
			}else{
				search_string = $('#clientSearchFirstName').val() + " " + $('#clientSearchLastName').val() + " " + $('#clientSearchPhone').val();
				$.ajax({
					url: "ajax/echoClientSearchResults.php?"+getVars,
					type: 'GET',
					dataType: 'json',
					success: function(data) {
						var filler = '';
						if (data && data.length > 0) {
							if (data.length < 30) {
							filler = '<span class="searchInfoBar">Found '+data.length+' results for "'+search_string+'" [<a href="#" onclick="hideSearch();">close search</a>]</span> <br />'; }
							else { filler = '<span class="searchInfoBar">Showing first 30 results for "'+search_string+'" [<a href="#" onclick="hideSearch();">close search</a>]</span> <br />'; }
							filler = filler + '<table id="search-results" cellpadding="2" border="1" cellspacing="1"><tr><td>ID</td><td>First name</td><td>Last Name</td><td>Cell phone</op td><td>Home phone</td><td>Work phone</td><td>Primary Phone</td></tr>';
							for (x=0;x <= (data.length - 1);x++)
							{
								filler = filler +'<tr class="searchLink" id="csr'+data[x][0]+'" onclick="displayClientData(this.id); scrollTo(this.id)"><td>'+data[x][0]+'</td><td>' + data[x][1] + '</td><td>' + data[x][2] + '</td><td>'+convertphone(data[x][3],"touser")+'</td><td>'+convertphone(data[x][4],"touser")+'</td><td>'+convertphone(data[x][5],"touser")+'</td><td>'+convertphone(data[x][6],"touser")+'</td></tr>';
							}
							filler = filler + '</table>';
						} else { filler = '<span class="searchInfoBar">No search results found for "'+search_string+'". [<a href="#" onclick="hideSearch();">close search</a>]</span>'; }
						document.getElementById('statusBox').innerHTML = filler;
					}
				});
				$("#statusBox").fadeIn();
			}		
		}

		function transferDataToTicket(HTMLclientID)
		{
			actualID = HTMLclientID.replace("clientSel","");
			//alert("moving data to ticket. client id = " + actualID);

			$.ajax({
				url: "ajax/echoClientNameFromId.php?cid="+actualID,
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					console.log('the client name/id transfer ran correctly.')
					clientName = data;
					window.opener.$("#ClientID").val(actualID);
					window.opener.$("#ClientName").val(clientName);
					window.close();
				}
		
			});

			// window.close() was here but doesn't work unless within ajax function call.
		}
		
		function checkEmailForm() 
		{
			if ($('#clientEmailForm input[type=checkbox]:checked').length) {
				return true;
			} else {
				alert('No client has been checked for e-mail sending!');
				return false;
			}
		}
		
		$(document).ready(function(){
			
			// get rid of that pesky extra space if sending emails
			<?php
				if (isset($_GET['sendEmails'])) {
					?>
					$('#client-transactions-box').remove();
					$('#client-detail-box').height(520);
					<?php
				}
			?>
			
		    // change primary phone basic on selected radio button
			$("input[name=PrimarySel]").click(function(){
				if ($('#PriCell').is(':checked')){
					$('#PrimaryPhone').val($('#CellPhone').val());
				}
				if ($('#PriHome').is(':checked')){
					$('#PrimaryPhone').val($('#HomePhone').val());
				}
				if ($('#PriWork').is(':checked')){
					$('#PrimaryPhone').val($('#WorkPhone').val());
				}
			});
			
			// disable enter-button form submission
			$('#CIF').bind("keyup keypress", function(e) {
			  var code = e.keyCode || e.which; 
			  if (code  == 13) {               
				e.preventDefault();
				return false;
			  }
			});
			
			// live search event handlers
			$('#clientSearchFirstName').keyup(runLiveSearch);
			$('#clientSearchLastName').keyup(runLiveSearch);
			$('#clientSearchPhone').keyup(runLiveSearch);
			
			//float table headers
			var $table = $('table.clients');
			$table.floatThead({
				useAbsolutePositioning: false,
				scrollContainer: function($table) {
					return $table.closest('#client-list-box');
				}
			});

			// pull client info and scroll to that client if one was viewed before in session
			// and if not entering new client
			// and if not sending emails
			<?php
			if (isset($_SESSION['lastClientHtmlId']) && $_SESSION['lastClientHtmlId'] != '' && !isset($_GET['new']) && !isset($_GET['sendEmails']) && !isset($_GET['doEmailSend'])) // finally got the GET variable right. doEmailSend is correct. Yeesh.
			{
				$lastClientId = $_SESSION['lastClientHtmlId'];
				?>
					displayClientData(<?php echo json_encode($lastClientId); ?>);
					scrollTo(<?php echo json_encode($lastClientId); ?>);
				<?php
			}
			?>
			
			// display instructions for new appointment if redirected from appointment page
			<?php
			if (isset($_GET['newAppt']))
			{
				?>
				alert('To create a new appointment, select a client by clicking them in the list on the left, and click the "add appointment" button (the notepad icon with a plus)');
				<?php
			}
			?>
			
		});
	
	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>

<body class="clients">

	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	
	<div id="admin_content" class="loading">
	
		<h1>Clients &raquo; 
		<a class="buttonLink" href="clients.php?new">
		<img class="button" src="images/addClient2.png" alt="create new client" title="create new client" height="32px" width="32px" /></a>
		
		<a class="buttonLink" href="clients.php?delete" onclick="return deleteConfirm();">
		<img class="button" src="images/deleteClient.png" alt="delete selected client" title="delete selected client" height="32px" width="32px" /></a>
		
		<a class="buttonLink" id="addApptButton" onclick="return confirmAdd();">
		<img class="button" src="images/addTicket.png" alt="add appointment for selected client" title="add appointment for selected client" height="32px" width="32px" /></a>
		
		<a class="buttonLink" id="emailButton" onclick="$('#clientEmailForm').trigger('submit');">
		<img class="button" src="images/addEmail.png" alt="send selected client(s) an e-mail" title="send selected client(s) an e-mail" height="32px" width="32px" /></a>	

		<?php 
			if (isset($_GET['popupSearch']))
			{
				echo '<a class="buttonLink" id="transferButton" onclick="transferDataToTicket(window.HTMLid);">
				<img class="button" src="images/transfer.png" alt="move selected client to current ticket" title="move selected client to current ticket" height="32px" width="32px" /></a>';
			}
			?>	
		&raquo;
		
		<img class="button" src="images/magnifyingGlass.png" height="32px" width="32px" />
		
		<span><form name="clientSearchFNform" id="clientSearchFNform" action="" onsubmit="return false;">
		First Name: <input type="text" name="clientSearchFirstName" id="clientSearchFirstName" />
		<span><form name="clientSearchLNform" id="clientSearchLNform" action="" onsubmit="return false;">
		Last Name: <input type="text" name="clientSearchLastName" id="clientSearchLastName" />
		<span><form name="clientSearchPform" id="clientSearchPform" action="" onsubmit="return false;">
		Phone number: <input type="text" name="clientSearchPhone" id="clientSearchPhone" />
		</form></span>
		</h1>
			<hr />
		
		<?php
		
		// MAIN DISPLAY.
		// selectable box list of clients from A->Z
		echo '<div id="statusBox"></div>';
		echo '<div id="client-list-box">';
		$whereClause = "WHERE `Inactive` = 0 ";
		
		$getclients = "SELECT `ClientID`,`FirstName`,`LastName`,`Balance` FROM `clients` $whereClause ORDER BY `FirstName`,`LastName` ASC";
		$stmt = $Database->prepare($getclients);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
			$nr = $result->num_rows;
			
			echo '<form name="clientEmailForm" id="clientEmailForm" action="clients.php?sendEmails" method="POST" onsubmit="return checkEmailForm();">';
			echo '<table class="clients" cellpadding="0" border="0" cellspacing="0"><thead>';
			echo '<tr><th><img id="emailCheckAllImage" src="images/email.png" width="20px" height="20px" title="select all/none for e-mail" alt="select all/none for e-mail" onclick="checkBoxes();" /></th><th>Name</th><th>Balance</th></thead><tbody>';
			
			if (!isset($_GET['doEmailSend']) && !isset($_GET['sendEmails']))
			{
				for ($n = 0; $n <= ($nr - 1); $n++) 
				{
					$result->data_seek($n);
					$row = $result->fetch_array();
					
					//if ($n <200) { // only do 200 clients for development phase
						/// maybe the $n+1 below causes the slowdown?? Probably not..
						// Google chrome loads this page and big accounts.php pages WAY faster..
						$htmlID = 'clientSel'.$row[0];
						echo '<tr id="'.$htmlID.'" class="r'.($n+1).'">
						<td id="sendEmailCheck'.$row[0].'"><input type="checkbox" class="emailChecks" name="selectedEmailClients['.$row[0].']" /></td>
						<td onclick="displayClientData(this.parentNode.id);">'.$row[1]." ".$row[2].'</td>
						<td onclick="displayClientData(this.parentNode.id);">'.number_format((float)$row[3], 2, '.', '').'</td></tr>';
				    //}
				}
			}
			else
			{
				echo '<tr><td colspan="3">running e-mail function. Click "<a href="./clients.php">Clients</a>" to return to the main Clients screen.</td></tr>';
			}
			
			if (!isset($_GET['acc']) && !isset($_GET['dcc']))
			{
			?>
				<script type="text/javascript">
					$("#statusBox").html("<?php echo json_encode($nr); ?>"+ " clients found.");
					$("#statusBox").fadeIn();
				</script>
			<?php
			}
			$result->close();
			echo '</tbody></table>';
			echo '</form>';
		}
		else
		{
			echo "Query to retrieve all clients' info failed";
		}
		echo '</div>';
		
		// Individual Client data box
		echo '<div id="client-detail-box">';
		if (!isset($_GET['sendEmails']) && !isset($_GET['doEmailSend']))
		{
			?>
			<form name="clientInfoForm" id="CIF" method="post" onsubmit="return checkForm();" action="clients.php?save<?php echo ((isset($_GET['new']) || !isset($_SESSION['lastClientHtmlid']))?'&new':''); ?>">
			<table class="client-details" cellpadding="0" border="0" cellspacing="0">
			<tr><td>ID</td><td><span class="ht"><input id="ClientID" class="rogray" type="text" name="ClientID" value="" maxlength="7" size="7" readonly="readonly" /><span class="tooltip">If creating a new client, an ID will be auto-assigned upon saving.</span></span></td><td></td><td></td>
			<td>Balance</td><td><input id="Balance" class="rogray" type="text" name="Balance" value="" readonly="readonly" size="7"</td>
			</tr>
			
			<tr><td>Title</td><td><select id="Title" name="Title" tabindex="1">
				<option value="n/a">n/a</option>
				<option value="Mr.">Mr.</option>
				<option value="Ms.">Ms.</option>
				<option value="Mrs.">Mrs.</option></select></td>
			<td>Date of birth</td><td class="ht"><input id="DateOfBirth" type="text" name="DateOfBirth" value="" maxlength="10" tabindex="14"/><span class="tooltip">Must be MM-DD-YYYY format.</span></td>
			<td>Balance comments</td><td rowspan="4"><textarea id="BalanceComment" name="BalanceComment" tabindex="19"></textarea></td></tr>
			
			<tr><td>First Name</td><td><input id="FirstName" type="text" name="FirstName" value="" maxlength="50" tabindex="2"/></td>
			<td>Anniversary</td><td class="ht"><input id="Anniversary" type="text" name="Anniversary" value="" maxlength="10" tabindex="15" /><span class="tooltip">Must be MM-DD-YYYY format.</span></td></tr>
			
			<tr><td>Last Name</td><td><input id="LastName" type="text" name="LastName" value="" maxlength="50" tabindex="3" /></td>
			<td>First employee</td><td><input id="FirstEmployeeName" class="rogray" type="text" name="FirstEmployeeName" value="" readonly="readonly"</td></tr>
			
			<tr><td>Address</td><td><input id="Address" type="text" name="Address" value="" maxlength="150" tabindex="4" /></td>
			<td>Last employee</td><td><input id="LastEmployeeName" class="rogray" type="text" name="LastEmployeeName" value="" readonly="readonly" /></td></tr>
			
			<tr><td>City</td><td><input id="City" type="text" name="City" value="" maxlength="75" tabindex="5"/></td>
			<td>First visit</td><td><input id="FirstVisit" class="rogray" type="text" name="FirstVisit" value="" readonly="readonly" /></td></tr>
			
			<tr><td>State</td><td><input id="State" type="text" name="State" value="" maxlength="50" tabindex="6"/></td>
			<td>Last visit</td><td><input id="LastVisit" class="rogray" type="text" name="LastVisit" value="" readonly="readonly" /></td></tr>
			
			<tr><td>ZIP</td><td><input id="ZIP" type="text" name="ZIP" value="" maxlength="10" tabindex="7"/></td>
			<td>Total visits</td><td><input id="TotalVisits" class="rogray" type="text" name="TotalVisits" value="" readonly="readonly" size="3" /></td></tr>
			
			<tr><td>Gender</td>
			<td style="clear: both;"><select id="Gender" name="Gender" tabindex="8">
				<option value=""></option>
				<option value="M">M</option>
				<option value="F">F</option></select></td>
			<td>No-shows</td><td><input id="NoShow" class="rogray" type="text" name="NoShow" value="" readonly="readonly" size="3" /></td></tr>
			
			<tr><td>Cell phone</td><td><input id="CellPhone" type="text" name="CellPhone" value="" maxlength="16" tabindex="9" /><span class="ht"><input id="PriCell" type="radio" name="PrimarySel" value="CellPhone" /><span class="tooltip">Use this button to select this number as the primary number.</span></span></td>
			<td>Comment</td><td rowspan="4"><textarea id="Comment" name="Comment" tabindex="16" /></textarea></td>
			<td>Client History</td><td rowspan="4"><textarea id="ClientHistory" name="ClientHistory" tabindex="20"/></textarea></td></tr>
			
			<tr><td>Home phone</td><td><input id="HomePhone" type="text" name="HomePhone" value="" maxlength="16" tabindex="10" /><span class="ht"><input id="PriHome" type="radio" name="PrimarySel" value="HomePhone" /><span class="tooltip">Use this button to select this number as the primary number.</span></span></td></tr>
			
			<tr><td>Work phone</td><td><input id="WorkPhone" type="text" name="WorkPhone" value="" maxlength="16" tabindex="11"/><span class="ht"><input id="PriWork" type="radio" name="PrimarySel" value="WorkPhone" /><span class="tooltip">Use this button to select this number as the primary number.</span></span></td></tr>
			
			<tr><td>Primary phone</td><td><input id="PrimaryPhone" class="rogray" type="text" name="PrimaryPhone" value="" readonly="readonly" /></td></tr>
			
			<tr><td>E-mail</td><td><input id="E-mail" type="email" name="E-mail" value="" maxlength="150" tabindex="12"/></td>
			<td>Send reminders?</td><td class="ht"><input id="AptEmail" type="checkbox" name="AptEmail" tabindex="17" /><span class="tooltip">If this option is checked, the client will recieve e-mail reminders before each appointment.</span></tr>
			
			<tr><td>Occupation</td><td><input id="Occupation" type="text" name="Occupation" value="" maxlength="100" tabindex="13" /></td>
			<td>Inactive?</td><td class="ht"><input id="Inactive" type="checkbox" name="Inactive" tabindex="18" /><span class="tooltip">If this option is checked, this client will be hidden from the list by default.</span></td>
			<td></td><td style="text-align: right;">
			<input type="submit" value="save" name="submit" />&nbsp;
			<input type="button" value="reset fields" onclick="displayClientData(HTMLid);" /></td>
			</tr>
			
			<input type="hidden" id="FirstEmployeeID" name="FirstEmployeeID" value="" />
			<input type="hidden" id="LastEmployeeID" name="LastEmployeeID" value="" />
			</table>
			</form>
			<?php
		}
		elseif (isset($_GET['sendEmails'])) // display email contents form and pass the recipients array with it to ?doEmailSend
		{
			
			// get recipients from POST data
			$recips = array();
			if (isset($_POST['selectedEmailClients']))
			{
				$total_recips = count($_POST['selectedEmailClients']);
				$condition_string = '';
				$count = 0;
				foreach ($_POST['selectedEmailClients'] as $cid => $state)
				{
					if ($count == 0) {
						$condition_string = "SELECT `FirstName`,`LastName`,`E-mail` FROM clients WHERE `ClientID` = $cid";
					} else {
						$condition_string .= ' OR `ClientID` = '.$cid;
					}
					$count++;
				}
				
				$stmt = $Database->prepare($condition_string);
				if ($stmt->execute())
				{
					$blank_email_addresses = 0;
					$res = $stmt->get_result();
					$nr = $res->num_rows;
					$duplicate_emails = 0;
					for ($x = 0; $x <= ($nr - 1); $x++)
					{
						$res->data_seek($x); //gets individual row
						$row = $res->fetch_array();
						if ($row[2] != '') {
							if (isset($recips["$row[2]"]) && $recips["$row[2]"] != '') {
								$duplicate_emails++;
							}
							$recips["$row[2]"] = $row[0].' '.$row[1];
						} else {
							$blank_email_addresses++;
						}
					}
					
					// display email sending info box
					echo '<div id="email-presend-info-box">
						<h3 class="email-title">E-mail sending details &raquo;</h3>
						<hr />
						<table class="email-info-table" cellpadding="2" cellspacing="2"><tbody><tr><td>
						Total selected clients:</td><td>'.$total_recips.'</td></tr><tr><td>
						Total blank e-mail addresses:</td><td>'.$blank_email_addresses.'</td></tr><tr><td>
						Total duplicate e-mail addresses:</td><td>'.$duplicate_emails.'</td></tr><tr><td>
						Total emails to be attempted:</td><td><span class="email-attempts">'.($total_recips - $blank_email_addresses - $duplicate_emails).' </span>&nsbp;+ 2 (confirmation to relax@bmds and Doug)</td></tr></table>
						</div>
						
						<div id="emailFormBox">
							<form name="emailContentsForm" id="emailContentsForm" method="POST" action="clients.php?doEmailSend">
								Subject:<br /><input type="text" class="emailSubject" name="Subject" id="Subject" /><br /><br />
								Message (HTML accepted):<br /><textarea contenteditable="true" class="emailMessage" name="Message" id="Message"></textarea><br />
								<input type="submit" id="sendEmailSubmit" name="sendEmailSubmit" value="send email(s)" /> ...&raquo; this may take a while. Please be patient for the following page to load.';
								
								/*
								?>
								<script type="text/javascript">
									$(".emailMessage").on("paste", function(){
										// delay, or else innerHTML won't be updated
										setTimeout(function(){
										// option 1 - for pasting text that looks like HTML (e.g. a code snippet)
										alert($(".emailMessage").text());

										// option 2 - for pasting actual HTML (e.g. select a webpage and paste it)
										alert($(".emailMessage").html());
									},100);
								});   
								</script>
								<?php
								*/
								
								$_SESSION['email_recipients'] = $recips;
								//$recips_string = serialize($recips);
								
							echo "
							</form>
						</div>";
						
					$res->close(); 
					
				}
				else
				{
					echo 'Failed to pull client info for e-mail.';
				}
			}
			
		}
		elseif (isset($_GET['doEmailSend'])) // send the emails
		{
			if (isset($_SESSION['email_recipients']))
			{	
				// USING GMAIL ACCOUNT FOR NOW.
				echo 'sending mail...<br />';
				$subject = $_POST['Subject'];
				$message = $_POST['Message'];
				$recips = $_SESSION['email_recipients'];
				//$recips = unserialize($recipsString);
				
				// DISPLAY ALL RECIPS
				//var_dump($recips); // this should be an array
				
				// PREPARE EMAIL FOR SENDING
				$mail = new PHPMailer;

				$mail->isSMTP();    // Set mailer to use SMTP
				//$mail->Host = 'box923.bluehost.com;mail.bellamichelledayspainc.com';  // Specify main and backup SMTP servers
				$mail->SMTPDebug = 0; //3; // 0 for nothing, 2 for data and commands, 3 for data, commands, and connection details

				$mail->SMTPAuth = true;
				$mail->SMTPSecure = "tls";  
				//$mail->Host = 'mail.bellamichelledayspainc.com';
				$mail->Host = 'mail.bellamichelledayspainc.com';
				$mail->Host = "smtp.gmail.com";
				$mail->Port = 587;
				//$mail->Port = 26;  

				//$mail->Username = 'specials@bellamichelledayspainc.com';    // SMTP username
				//$mail->Password = 'specEmail20!';                           // SMTP password
				$mail->Username = "relax@bellamichelledayspa.com";
				$mail->Password = "Mike2010";

				$mail->From = 'specials@bellamichelledayspainc.com';
				$mail->FromName = 'Bella Michelle Day Spa';

				$x = 0;
				// SET UP THE RECIPIENTS EMAIL LIST FROM ALREADY-SELECTED DATA
				// THE HEAVY LIFTING IS SOMEWHAT HERE.
				/*foreach ($recips as $email => $name)
				{
					if ($x == 0) {
						$mail->addAddress($email, $name);
					} else {
						$mail->addBCC($email, $name);
					}
					$x++;
				}*/
			
				$mail->addAddress('mmonroe@getdtsi.com','Mike Monroe'); // for testing	
				$mail->addAddress('khaverim7@gmail.com','Douglas Franz'); // for testing just email to me
				$mail->addAddress('relax@bellamichelledayspa.com','Michael Monroe'); // for confirmation to Mike
				//$mail->addAddress('khavemailtest@gmail.com','test email');
				
				$mail->addReplyTo('specials@bellamichelledayspainc.com', 'No-reply'); // replies WILL be sent to this e-mail address.
				$mail->WordWrap = 50;                   
				
				// EMAIL CONTENTS AND ATTACHMENTS (works)
				//$mail->addAttachment('swiftmailer/Swiftmailer.pdf');         // Add attachments (relative path works)
				//$mail->addAttachment('./cool-cat.jpg', 'a_good_file_name.PNG'); // optional name (also, exact path works)

				$mail->isHTML(true);      // Set email format to accept HTML

				$mail->Subject = $subject;
				// included html images must be full url (not relative path)
				$mail->Body    = $message;
				$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

				
				if (!$mail->send()) { // SEND IT -------------------------------------------
					echo 'Message could not be sent.';
					echo 'Mailer Error: ' . $mail->ErrorInfo;
				} else {
					echo 'All emails successfully sent.';
					echo '<br />';
					echo 'To: ...<br />';
					//var_dump($mail->toArr);
					//var_dump($mail->SingleToArray);
					$output= print_r($mail,true); // saves contents of $mail as returnable string.;
					//echo $output;
					echo get_string_between($output,'[all_recipients:protected]', '[attachment:protected]');
					//echo $mail->version;
				}
				//$mail->ClearAddresses();
				//$mail->ClearAttachments();
				
				unset($_SESSION['email_recipients']);
				//break;
				
			} else {
				echo 'no session variable email_recipients detected';
			}
		
		}
		echo '</div>';
		
		// client transactions box
		echo '<div id="client-transactions-box">';
		if (!isset($_GET['sendEmails']))
		{
			?>
			<div id="client-tickets-table-box">
			<table name="client-tickets-table" class="client-tickets-table" cellpadding="3" cellspacing="0"><thead>
			<tr><th colspan="5" id="tdh"><span class="subtable-title">Tickets history &raquo;</span></th></tr>
			<tr><th>Ticket ID</th><th>Creator</th><th>Date</th><th>Status</th><th>Total</th></tr></thead><tbody>
			</tbody></table>
			</div>
			
			<div id="client-services-table-box">
			<table name="client-services-table" class="client-services-table" cellpadding="3" cellspacing="0"><thead>
			<tr><th colspan="4" id="tsh" ><span class="subtable-title">Services history &raquo;</span></th></tr>
			<tr><th>Date</th><th>Employee</th><th>Description</th><th>Price</th></tr></thead><tbody>
			</tbody></table>
			</div>
			
			<div id="client-products-table-box">
			<table name="client-products-table" class="client-products-table" cellpadding="3" cellspacing="0"><thead><tr>
			<tr><th colspan="5" id="tph" ><span class="subtable-title">Products history &raquo;</span></th></tr>
			<th>Date</th><th>Description</th><th>Price</th><th>Qty.</th><th>Total</th></tr></thead><tbody>
			</tbody></table>
			</div>
			<?php
		}
		else
		{	
			echo '';
		}
		echo '</div>';
		
		if (!isset($_GET['new']))
		{
			// do nothing (client info is pulled by JS script in $(document).ready function
			$n="o";
		}
		elseif (isset($_GET['new'])) // if entering new client
		{
			if (isset($_SESSION['lastClientHtmlId'])) {unset($_SESSION['lastClientHtmlId']);}
			?>
				<script type="text/javascript" language="javascript">
					if (window.HTMLid) {
					delete window.HTMLid; }
				</script>
			<?php
		}
		
		// check for save, or for delete
		if (isset($_GET['save']))
		{
			$ClientID = $_POST['ClientID'];
			$Title = $_POST['Title'];
			$FirstName = $_POST['FirstName'];
			$LastName = $_POST['LastName'];
			$Address = $_POST['Address'];
			$City = $_POST['City'];
			$State = $_POST['State'];
			$ZIP = $_POST['ZIP'];
			$Gender = $_POST['Gender'];
			$CellPhone = convertphone($_POST['CellPhone'],"tomysql");
			$HomePhone = convertphone($_POST['HomePhone'],"tomysql");
			$WorkPhone = convertphone($_POST['WorkPhone'],"tomysql");
			$PrimaryPhone = convertphone($_POST['PrimaryPhone'],"tomysql");
			$Email = $_POST['E-mail'];
			$Occupation = $_POST['Occupation'];
			$DateOfBirth = convertdate($_POST['DateOfBirth'],"tomysql");
			$Anniversary = convertdate($_POST['Anniversary'],"tomysql");
			$Balance = $_POST['Balance'];
			$BalanceComment = $_POST['BalanceComment'];
			if (isset($_POST['AptEmail']) && $_POST['AptEmail'] == "on") {
				$AptEmail = '1'; }
				else {$AptEmail = '0'; }
			$ClientHistory = $_POST['ClientHistory'];
			$Comment = $_POST['Comment'];
			$FirstEmployeeID = $_POST['FirstEmployeeID'];
			$FirstEmployeeName = $_POST['FirstEmployeeName'];
			$FirstVisit = convertdate($_POST['FirstVisit'],"tomysql");
			$LastEmployeeID = $_POST['LastEmployeeID'];
			$LastEmployeeName = $_POST['LastEmployeeName'];
			$LastVisit = convertdate($_POST['LastVisit'],"tomysql");
			$TotalVisits = $_POST['TotalVisits'];
			if (isset($_POST['Inactive']) && $_POST['Inactive'] == "on") {
				$Inactive = '1'; }
				else {$Inactive = '0'; }
			$NoShow = $_POST['NoShow'];
			
			if (!isset($_GET['new'])) {
				$saveClient = 'REPLACE INTO clients 
				SET `ClientID` = ?,`Title` = ?, `FirstName` = ?,`LastName` = ?,`Address` = ?,`City` = ?,`State` = ?,`ZIP` = ?,`Gender` = ?,`CellPhone` = ?,`HomePhone` = ?,`WorkPhone` = ?,`PrimaryPhone` = ?, `E-mail` = ?,`Occupation` = ?,`DateOfBirth` = ?,`Anniversary` = ?,`Balance` = ?,`BalanceComment` = ?,`AptEmail` = ?,`ClientHistory` = ?,`Comment` = ?, `FirstEmployeeID` = ?,`FirstEmployeeName` = ?, `FirstVisit` = ?,`LastEmployeeID` = ?, `LastEmployeeName` = ?,`LastVisit`= ?, `TotalVisits` = ?, `Inactive` = ?, `NoShow` = ?';
			} else {
				$saveClient = 'INSERT INTO clients VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
			}
			$stmt = $Database->prepare($saveClient);
			$stmt->bind_param('sssssssssssssssssssssssssssssss',$ClientID,$Title,$FirstName,$LastName,$Address,$City,$State,$ZIP,$Gender,$CellPhone,$HomePhone,$WorkPhone,$PrimaryPhone,$Email,$Occupation,$DateOfBirth,$Anniversary,$Balance,$BalanceComment,$AptEmail,$ClientHistory,$Comment,$FirstEmployeeID,$FirstEmployeeName,$FirstVisit,$LastEmployeeID,$LastEmployeeName,$LastVisit,$TotalVisits,$Inactive,$NoShow);
			if ($stmt->execute())
			{
				?>
				<script type="text/javascript">
					window.location = "clients.php?acc";
				</script>
				<?php
				$stmt->close();
			}
			else
			{
				?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Client info was not successfully saved.";
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
			if (isset($_SESSION['lastClientHtmlId'])) 
			{
				$htmlID = $_SESSION['lastClientHtmlId'];
				$clientID = str_replace("clientSel","",$htmlID);
				
				$stmt = $Database->prepare("DELETE FROM clients WHERE ClientID = ".$clientID);
				if ($stmt->execute()) 
				{
					unset($_SESSION['lastClientHtmlId']);
					?>
					<script type="text/javascript">
					delete window.HTMLid;
					window.location = "clients.php?dcc";
					</script>
					<?php
					$stmt->close();
				}
				else
				{
					?>
					<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Client was not deleted.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
				    </script>
				<?php
				}
			} 
		}
		elseif (isset($_GET['acc']))
		{
			?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Saved client info successfully.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 2500);
			</script>
			<?php
		}
		elseif (isset($_GET['dcc']))
		{
			?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Successfully deleted the client.";
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
