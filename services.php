<?php 
/*
	services.php
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
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Quac Admin Area</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/__jqft.js"></script>
	<script type="text/javascript">
		
		function deleteConfirm()
		{
			if (typeof prevServHTMLid === 'undefined' || prevServHTMLid == '')
			{ alert('No service is selected!'); return false; }
			else 
			{
				if ($("#newServ").length == 0)
				{
					var delServName = $("#"+prevServHTMLid+" td:nth-child(4) input").val();
					if (confirm('Are you sure you want to delete '+delServName+'? This action cannot be undone.'))
					{
						// delete the service
						$.ajax(
						{
							url: "ajax/echoDeleteService.php?selected_id=" + prevServHTMLid,
							type: 'GET',
							dataType: 'json',
							success: function(data)
							{
								if (data == "Deleted the service successfully")
								{ window.location = "services.php?csd";	}
								else {
									$("#statusBox").html("ERROR: Failed to delete the service.");
									$("#statusBox").fadeIn();
									setTimeout(function(){
										$("#statusBox").fadeOut();
									}, 7000);
								}
							}
						});
					}
				}
				else
				{
					alert('Cannot delete a new and unsaved service.');
				}
			}
		}
		
		function checkForm()
		{
			var num_regex = /^[0-9]+([\,\.][0-9]+)?$/;
			if ($("#").val() == '' || $("#Duration").val() == '' || $("#Price").val() == '')
			{ alert('You must at least include a name, duration, and price.'); return false;}
			else if (!(num_regex.test($("#Duration").val())) || !(num_regex.test($("#Price").val())))
			{ alert('Duration and Price fields must contain number values only (decimals allowed)'); return false;}
			else {return true;}
		}
		
		function openEdits(servHTMLid)
		{
			if (window.servHTMLid == servHTMLid) {return false;}
			else 
			{
				if (window.prevServHTMLid) {
					if (window.prevServHTMLid == "newServ") {
						// delete the new service addition row
						$("#"+window.prevServHTMLid).remove();
						document.serviceForm.action = "services.php?save";
					} else {
						// replace form inputs with ORIGINAL text on previous row
						$("#"+prevServHTMLid+" td:nth-child(2)").html(prevServCustomID);
						$("#"+prevServHTMLid+" td:nth-child(3)").html(prevServCategory);
						$("#"+prevServHTMLid+" td:nth-child(4)").html(prevServName);
						$("#"+prevServHTMLid+" td:nth-child(5)").html(prevServDuration);
						$("#"+prevServHTMLid+" td:nth-child(6)").html(prevServPrice);
						$("#"+prevServHTMLid+" td:nth-child(7)").html(prevServTaxable);
						$("#"+prevServHTMLid+" td:nth-child(8)").html(prevServInactive);
						$("#"+prevServHTMLid+" td:nth-child(9)").html("");
					}
				}
				// set new "previous id" and corresponding values (so that the user doesn't assume a previous row was saved)
				window.prevServHTMLid = servHTMLid;
				window.prevServCustomID = $("#"+servHTMLid+" td:nth-child(2)").html();
				window.prevServCategory = $("#"+servHTMLid+" td:nth-child(3)").html();
				window.prevServName = $("#"+servHTMLid+" td:nth-child(4)").html();
				window.prevServDuration = $("#"+servHTMLid+" td:nth-child(5)").html();
				window.prevServPrice = $("#"+servHTMLid+" td:nth-child(6)").html();
				window.prevServTaxable = $("#"+servHTMLid+" td:nth-child(7)").html();
				window.prevServInactive = $("#"+servHTMLid+" td:nth-child(8)").html();
				
				// set current id
				window.servHTMLid = servHTMLid;
				var sid = servHTMLid.replace("serviceSel","");
				
				
				var cidText = $("#"+servHTMLid+" td:nth-child(2)").html();
				$("#"+servHTMLid+" td:nth-child(2)").html("<input type='hidden' name='selectedID' id='selectedID' value='"+sid+"' /><input type='text' name='CustomID' id='CustomID' value='" + cidText + "' />");
				
				var catText = $("#"+servHTMLid+" td:nth-child(3)").html();
				$("#"+servHTMLid+" td:nth-child(3)").html("<input type='text' name='Category' id='Category' value='" + catText + "' />");
				
				var nameText = $("#"+servHTMLid+" td:nth-child(4)").html();
				$("#"+servHTMLid+" td:nth-child(4)").html("<input type='text' size='46' name='Name' id='Name' value='" + nameText + "' />");
				
				var durationText = $("#"+servHTMLid+" td:nth-child(5)").html();
				$("#"+servHTMLid+" td:nth-child(5)").html("<input type='text' size='4' name='Duration' id='Duration' value='" + durationText + "' />");
				
				var priceText = parseFloat($("#"+servHTMLid+" td:nth-child(6)").html()).toFixed(2);
				$("#"+servHTMLid+" td:nth-child(6)").html("<input type='text' size='5' name='Price' id='Price' value='" + priceText + "' />");
				
				var taxText = $("#"+servHTMLid+" td:nth-child(7)").html();
				if (taxText == "yes") {
					$("#"+servHTMLid+" td:nth-child(7)").html("<input type='checkbox' name='Tax' id='Tax' checked />");
				} else {
					$("#"+servHTMLid+" td:nth-child(7)").html("<input type='checkbox' name='Tax' id='Tax' />");
				}
				
				var InactiveText = $("#"+servHTMLid+" td:nth-child(8)").html();
				if (InactiveText == "yes") {
					$("#"+servHTMLid+" td:nth-child(8)").html("<input type='checkbox' name='Inactive' id='Inactive' checked />");
				} else {
					$("#"+servHTMLid+" td:nth-child(8)").html("<input type='checkbox' name='Inactive' id='Inactive' />");
				}
				
				$("#"+servHTMLid+" td:nth-child(9)").html('<input type="submit" value="save" name="serviceFormSubmit" id="serviceFormSubmit" />');
			}
			
		}
		
		function hideSearch()
		{
			$("#statusBox").fadeOut();
			document.getElementById('serviceSearchName').value = '';
		}
		
		function scrollToAndSelect(HTMLid)
		{
			HTMLid = HTMLid.replace("csr","serviceSel");
			var row = document.getElementById(HTMLid);
			var c = row.getAttribute("class");
			c = parseInt(c.replace("r",""));
			$('#service-list-box').scrollTop((c*18)-(18*3));
			
			openEdits(HTMLid);
		}
		
		// function to handle live search
		function runLiveSearch() {
		
			var search_string = $(this).val();

			if ($('#serviceSearchName').val() == '') {
				$("#statusBox").fadeOut();
			}else{
				search_string = $('#serviceSearchName').val();
				$.ajax({
					url: "ajax/echoServiceSearchResults.php?query="+search_string,
					type: 'GET',
					dataType: 'json',
					success: function(data) {
						var filler = '';
						if (data && data.length > 0) {
							if (data.length < 30) {
							filler = '<span class="searchInfoBar">Found '+data.length+' results for "'+search_string+'" [<a href="#" onclick="hideSearch();">close search</a>]</span> <br />'; }
							else { filler = '<span class="searchInfoBar">Showing first 30 results for "'+search_string+'" [<a href="#" onclick="hideSearch();">close search</a>]</span> <br />'; }
							filler = filler + '<table id="search-results" cellpadding="2" border="1" cellspacing="1"><tr><td>ID</td><td>Custom ID</td><td>Category</td><td>Name</td><td>Duration</td><td>Price</td></tr>';
							for (x=0;x <= (data.length - 1);x++)
							{
								filler = filler +'<tr class="searchLink" id="csr'+data[x][0]+'" onclick="scrollToAndSelect(this.id);"><td>'+data[x][0]+'</td><td>' + data[x][1] + '</td><td>' + data[x][2] + '</td><td>'+data[x][3]+'</td><td>'+data[x][4]+'</td><td>'+data[x][5]+'</td></tr>';
							}
							filler = filler + '</table>';
						} else { filler = '<span class="searchInfoBar">No search results found for "'+search_string+'". [<a href="#" onclick="hideSearch();">close search</a>]</span>'; }
						document.getElementById('statusBox').innerHTML = filler;
					}
				});
				$("#statusBox").fadeIn();
			}		
		}
		
		function transferDataToTicket(rowID)
		{
			if (rowID == '' || (typeof rowID === 'undefined')) {
				alert('No product was selected!');
			} else {
				var tdrID = <?php if (isset($_GET['tdrID'])) { echo json_encode($_GET['tdrID']); } else { echo '""'; } ?>;
				var hrID = <?php if (isset($_GET['hrID'])) { echo json_encode($_GET['hrID']); } else { echo '""'; } ?>;
				//window.opener.$("body").html('This is the rowID of the product: ' + rowID + '<br />This is the ticket detail ID: ' + tdrID + '<br />This is the HTML row ID on the ticket page: ' + hrID + '');
				
				if (hrID != 'none') {
					window.opener.$("#" + hrID + " td:nth-child(3)").html($("#" + rowID + " td:nth-child(5) input").val()); // duration for service
					window.opener.$("#" + hrID + " td:nth-child(4)").html("S"); // s for service
					window.opener.$("#" + hrID + " td:nth-child(5)").html(rowID.replace("serviceSel",""));
					window.opener.$("#" + hrID + " td:nth-child(6)").html($("#" + rowID + " td:nth-child(4) input").val()); // description/name
					window.opener.$("#" + hrID + " td:nth-child(7)").html($("#" + rowID + " td:nth-child(6) input").val()); // price
							// no tax change
					window.opener.$("#" + hrID + " td:nth-child(9)").html("1"); // qty
					window.opener.$("#" + hrID + " td:nth-child(10)").html($("#" + rowID + " td:nth-child(6) input").val()); // total (same as price)
					
					window.opener.saveRowValues();
				
				} else if (hrID == 'none') {
					alert('The hrID (HTML row ID) was not passed as a GET variable.');
				}
				window.close();
			}
		}
		
		$(document).ready(function() {
			
			// live search event handlers
			$('#serviceSearchName').keyup(runLiveSearch);
		
			//float table headers
			var $table = $('table.services');
			$table.floatThead({
				useAbsolutePositioning: false,
				scrollContainer: function($table) {
					return $table.closest('#service-list-box');
				}
			});
		});
		
	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>
<body class="services">
	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	<div id="admin_content">
		<h1>Services &raquo;
			<a class="buttonLink" href="services.php?new">
			<img class="button" src="images/addService.png" alt="create new service" title="create new service" height="32px" width="32px" /></a>
			
			<a class="buttonLink" href="#" onclick="deleteConfirm();">
			<img class="button" src="images/deleteService.png" alt="deleted selected service" title="deleted selected service" height="32px" width="32px" /></a>
			
			<?php 
			if (isset($_GET['popupSearch']))
			{
				echo '<a class="buttonLink" id="transferButton" onclick="transferDataToTicket(window.prevServHTMLid);">
				<img class="button" src="images/transfer.png" alt="move selected service to current ticket" title="move selected service to current ticket" height="32px" width="32px" /></a>';
			}
			?>
			
			&raquo;
			
			<img class="button" src="images/magnifyingGlass.png" height="32px" width="32px" />
			
			<span><form id="serviceSearchForm" name="serviceSearchForm" action="" onsubmit="return false;">
			Name: <input type="text" name="serviceSearchName" id="serviceSearchName" />
			</form></span>
		</h1>
			<hr />
			<div id="statusBox"></div>
			<?php
			echo '<div id="service-list-box">';
			
			if (!isset($_GET['sort'])) {
				$getservices = "SELECT * FROM `services`";
			} else {
				$getservices = "SELECT * FROM `services` ORDER BY ".$_GET['sort']." ".$_GET['order'];
			}
			$stmt = $Database->prepare($getservices);
			if ($stmt->execute())
			{
				$result = $stmt->get_result();
				
				echo "<form name='serviceForm' id='serviceForm' method='POST' action='services.php?save".(isset($_GET['new'])?'&new':'')."' onsubmit='return checkForm();'>";
				echo '<table id="service-table" class="services" cellpadding="0" border="0" cellspacing="0"><thead>';
				echo '<tr>
					<th><a href="services.php?sort=ServiceID&order='.((isset($_GET['sort']) && $_GET['sort'] == "ServiceID" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">ID</a></th>
					<th><a href="services.php?sort=CustomID&order='.((isset($_GET['sort']) && $_GET['sort'] == "CustomID" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Custom ID</a></th>
					<th><a href="services.php?sort=Category&order='.((isset($_GET['sort']) && $_GET['sort'] == "Category" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Category</a></th>
					<th><a href="services.php?sort=Name&order='.((isset($_GET['sort']) && $_GET['sort'] == "Name" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Name</a></th>
					<th><a href="services.php?sort=Duration&order='.((isset($_GET['sort']) && $_GET['sort'] == "Duration" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Duration (mins)</a></th>
					<th><a href="services.php?sort=Price&order='.((isset($_GET['sort']) && $_GET['sort'] == "Price" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Price</a></th>
					<th><a href="services.php?sort=Taxable&order='.((isset($_GET['sort']) && $_GET['sort'] == "Taxable" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Taxable</a></th>
					<th><a href="services.php?sort=Inactive&order='.((isset($_GET['sort']) && $_GET['sort'] == "Inactive" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Inactive</a></th>
					<th>[save]</th></tr></thead><tbody>';
				if (isset($_GET['new'])) {
					echo "<tr id='newServ'><td></td>
					<td><input type='text' name='CustomID' id='CustomID' value='' /></td>
					<td><input type='text' name='Category' id='Category' value='' /></td>
					<td><input type='text' size='46' name='Name' id='Name' value='' /></td>
					<td><input type='text' size='4' name='Duration' id='Duration' value='' /></td>
					<td><input type='text' size='5' name='Price' id='Price' value='' /></td>
					<td><input type='checkbox' name='Tax' id='Tax' /></td>
					<td><input type='checkbox' name='Inactive' id='Inactive' /></td>
					<td><input type='submit' value='save' name='serviceFormSubmit' id='serviceFormSubmit' /></td></tr>";
					?>
						<script type="text/javascript">
							window.prevServHTMLid = "newServ";
						</script>
					<?php
				}
				for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
				{
					$result->data_seek($n);
					$row = $result->fetch_array();
					
					$htmlID = 'serviceSel'.$row[0];
					echo '<tr id="'.$htmlID.'" class="r'.($n+1).(($row[7] == "1")?" grayout":"").'" onclick="openEdits(this.id);">
					<td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td><td>'.$row[3].'</td><td>'.$row[4].'</td><td>'.number_format((float)$row[5], 2, '.', '').'</td><td>'.(($row[6] == "1")?"yes":"no").'</td><td>'.(($row[7] == "1")?"yes":"no").'</td><td></td></tr>'; //}
				}
				$result->close();
				echo '</tbody></table>';
				echo "</form>";
			}
			else
			{
				echo "Query to retrieve all services' info failed";
			}
			echo '</div>';
			
			if (isset($_GET['save']))
			{
				if (!isset($_GET['new'])) {$sid = $_POST['selectedID'];}
				else { $sid = ''; }
				$CustomID = $_POST['CustomID'];
				$Category = $_POST['Category'];
				$Name = $_POST['Name'];
				$Duration = $_POST['Duration'];
				$Price = $_POST['Price'];
				if (isset($_POST['Tax']) && $_POST['Tax'] == "on") { $Tax = "1"; } else { $Tax = "0";}
				if (isset($_POST['Inactive']) && $_POST['Inactive'] == "on") { $Inactive = "1"; } else { $Inactive = "0";}
				$qbid = null;
				
				if (!isset($_GET['new'])) {
					$saveService = 'REPLACE INTO services 
					SET `ServiceID` = ?, `CustomID` = ?, `Category` = ?, `Name` = ?, `Duration` = ?, `Price` = ?, `Taxable` = ?, `Inactive` = ?, `QuickbooksID` = ?';
				} else {
					$saveService = 'INSERT INTO services VALUES (?,?,?,?,?,?,?,?,?)';
				}
				
				$stmt = $Database->prepare($saveService);
				$stmt->bind_param('sssssssss',$sid,$CustomID,$Category,$Name,$Duration,$Price,$Tax,$Inactive,$qbid);
				if ($stmt->execute())
				{
					?>
					<script type="text/javascript">
						window.location = "services.php?csa";
					</script>
					<?php
					$stmt->close();
				}
				else
				{
					?>
					<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Service info was not successfully saved.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
					</script>
					<?php
				}
			}
			elseif (isset($_GET['csa']))
			{
				?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "Saved service info successfully.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 2500);
				</script>
				<?php
			}
			elseif (isset($_GET['csd']))
			{
				?>
				<script type="text/javascript">
				document.getElementById('statusBox').innerHTML = "Deleted the service successfully.";
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