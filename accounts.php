<?php 
/*
	accounts.php
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
		function reloadToDisplayAccount(givenID) {
			if (givenID == window.prevAcctHTMLid) { return false; }
			var acctName = $("#"+givenID+" td:nth-child(1)").html();
			$.ajax(
			{
				url: "ajax/saveAccountIDToSession.php?selected_id=" + givenID,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					console.log(data);
					window.scrollPos = $("#account-list-box").scrollTop();
					window.location = "accounts.php?ahid="+givenID;
				}
			});
		}
		
		function deleteConfirm()
		{
			if (typeof prevAcctHTMLid === 'undefined' || prevAcctHTMLid == '')
			{ alert('No account is selected!'); return false; }
			else 
			{
				var delAcctName = $("#"+prevAcctHTMLid+" td:nth-child(1)").html();
				if (confirm('Are you sure you want to delete '+delAcctName+'? This action cannot be undone.'))
				{
					// delete the account
					$.ajax(
					{
						url: "ajax/echoDeleteAccount.php?selected_id=" + prevAcctHTMLid,
						type: 'GET',
						dataType: 'json',
						success: function(data)
						{
							if (data == "Deleted the account successfully")
							{ window.location = "accounts.php?cad";	}
							else {
								$("#statusBox").html("ERROR: Failed to delete the account.");
								$("#statusBox").fadeIn();
								setTimeout(function(){
									$("#statusBox").fadeOut();
								}, 7000);
							}
						}
					});
				}			
			}
		}
		
		function deleteTransConfirm() 
		{
			if (typeof prevTransHTMLid === 'undefined' || prevTransHTMLid == '')
			{ alert('No transaction is selected!'); return false; }
			else
			{
				if (transHTMLid == 'newTrans') {
					alert('Cannot delete a new and unsaved transaction.'); return false;
				}
				var delTransID = prevTransHTMLid.replace(prevTransHTMLid.substr(prevTransHTMLid.length - 3),"");
				if (confirm('Are you sure you want to delete transaction ID# ' + delTransID + '? This action cannot be undone.'))
				{
					// delete the account
					$.ajax(
					{
						url: "ajax/echoDeleteTrans.php?selected_id=" + delTransID,
						type: 'GET',
						dataType: 'json',
						success: function(data)
						{
							if (data == "Deleted the transaction successfully")
							{ window.location = "accounts.php?ctd";	}
							else {
								$("#statusBox").html("ERROR: Failed to delete the transaction.");
								$("#statusBox").fadeIn();
								setTimeout(function(){
									$("#statusBox").fadeOut();
								}, 7000);
							}
						}
					});
				}
			}
		}
		
		function scrollTo(goToId)
		{
			var row = document.getElementById(goToId);
			var c = row.getAttribute("class");
			c = parseInt(c.replace("r",""));
			$('#account-list-box').scrollTop((c*15)-45);
		}
		
		function checkForm()
		{
			date_regex =  /^\d{2}\-\d{2}\-\d{4}$/;
			if (!(date_regex.test($("#Date").val())))
			{
				alert('Must enter date in MM-DD-YYYY format.');
				return false;
			}
			else if ($("#Payment").val() == '' && $("#Deposit").val() == '')
			{
				alert('Must enter a payment or deposit amount for the transaction.');
				return false;
			}
			else if ($("#Payment").val() != '' && $("#Deposit").val() != '')
			{
				alert('Cannot enter a payment and deposit for the same transaction.');
				return false;
			}
			if ($("#Payment").val() != '') { str = $("#Payment").val(); }
			else if ($("#Deposit").val() != '') { str = $("#Deposit").val(); }
			isNumValid = str.search(/^\$?\d+(,\d{3})*(\.\d*)?$/) >= 0;
			
			if (!isNumValid)
			{
				alert('Must enter a valid number for the payment or deposit.');
				return false;
			}
		}
		
		function trig()
		{
			window.trigCheck = true;
		}
		
		function checkDaBox(trid)
		{
			console.log("the id is.." + trid);
			if (window.trigCheck != true) {
				cbID = 'reconcile' + trid;
				if (($("#"+cbID).is(':checked')))
				{
					$('#' + cbID).prop('checked', false);
				}
				else
				{
					$('#' + cbID).prop('checked', true);
				}
			}
			window.trigCheck = false;
		}
		
		function openEdits(transHTMLid)
		{
			if ($(".newTransRow").length > 0) { $(".newTransRow").remove(); window.checkNT = false; }
			var firstChunk = transHTMLid.substring(0, transHTMLid.length - 3);
			if (window.transHTMLid == firstChunk+"-r1" || window.transHTMLid == firstChunk+"-r2") {return false;}
			else 
			{
				if (window.prevTransHTMLid || (window.prevTransHTMLid && transHTMLid == 'newTrans')) 
				{
					var pti = window.prevTransHTMLid;
					var ptipid = pti.substring(0, pti.length - 3);
					var firstRow = $("#"+ptipid+"r1");
					var secondRow = $("#"+ptipid+"r2");
						// replace form inputs with ORIGINAL text on previous transaction
						$("#"+ptipid+"-r1 td:nth-child(1)").html(prevTransDate);
						$("#"+ptipid+"-r1 td:nth-child(2)").html(prevTransNum);
						$("#"+ptipid+"-r1 td:nth-child(3)").html(prevTransPayee);
						$("#"+ptipid+"-r1 td:nth-child(4)").html(prevTransPayment);
						$("#"+ptipid+"-r1 td:nth-child(5)").html(prevTransCheckMark);
						$("#"+ptipid+"-r1 td:nth-child(6)").html(prevTransDeposit);
						$("#"+ptipid+"-r1 td:nth-child(7)").html(prevTransBalance);
						$("#"+ptipid+"-r1 td:nth-child(8)").html("");
						
						$("#"+ptipid+"-r2 td:nth-child(2)").html(prevTransType);
						$("#"+ptipid+"-r2 td:nth-child(3)").html(prevTransAcct);
						$("#"+ptipid+"-r2 td:nth-child(4)").html(prevTransMemo);
				}
				
				if (transHTMLid != 'newTrans')
				{
					// set new "previous id" and corresponding values (so that the user doesn't assume a previous row was saved)
					window.prevTransHTMLid = transHTMLid;
					// set current id
					window.transHTMLid = transHTMLid;
					
					var ti = transHTMLid;
					var tipid = ti.substring(0, ti.length - 3);
					
					var prevTransDollarAmount = (($("#" + tipid + "-r1 td:nth-child(4)").html() != '') ? ($("#" + tipid + "-r1 td:nth-child(4)").html() * -1) : $("#" + tipid + "-r1 td:nth-child(6)").html());
					var prevFinalBalance = $(".account-details tbody tr:nth-last-of-type(2) td:nth-child(7)").html();
					
					console.log('unedited amount: ' + prevTransDollarAmount + '..... Final Bal: ' + prevFinalBalance);
					console.log($("table.account-details tbody tr:nth-last-of-type(2) td:nth-child(7)").length);
					
					window.prevTransDate = $("#"+tipid+"-r1 td:nth-child(1)").html();
					window.prevTransNum = $("#"+tipid+"-r1 td:nth-child(2)").html();
					window.prevTransPayee = $("#"+tipid+"-r1 td:nth-child(3)").html();
					window.prevTransPayment = $("#"+tipid+"-r1 td:nth-child(4)").html();
					//checkmark thing needs work //// uh no it doesn't now
					window.prevTransCheckMark = $("#"+tipid+"-r1 td:nth-child(5)").html();
					window.prevTransDeposit = $("#"+tipid+"-r1 td:nth-child(6)").html();
					window.prevTransBalance = $("#"+tipid+"-r1 td:nth-child(7)").html();
					
					window.prevTransType = $("#"+tipid+"-r2 td:nth-child(2)").html();
					window.prevTransAcct = $("#"+tipid+"-r2 td:nth-child(3)").html();
					window.prevTransMemo = $("#"+tipid+"-r2 td:nth-child(4)").html();
					
					// fill fields with inputs instead of raw text
					
					var dateText = $("#"+tipid+"-r1 td:nth-child(1)").html();
					$("#"+tipid+"-r1 td:nth-child(1)").html("<input type='hidden' name='selectedID' id='selectedID' value='"+tipid+"' /><input type='hidden' name='uneditedAmount' id='uneditedAmount' value='" + prevTransDollarAmount + "' /><input type='hidden' name='finalBalance' id='finalBalance' value='" + prevFinalBalance + "' /><input type='text' class='oeBox' size='8' name='Date' id='Date' value='" + dateText + "' />");

					var numText = $("#"+tipid+"-r1 td:nth-child(2)").html();
					$("#"+tipid+"-r1 td:nth-child(2)").html("<input type='text' class='oeBox' size='7' name='Num' id='Num' value='" + numText + "' />");
					
					var payeeText = $("#"+tipid+"-r1 td:nth-child(3)").html();
					$("#"+tipid+"-r1 td:nth-child(3)").html("<input type='text' class='oeBox' size='46' name='Payee' id='Payee' value='" + payeeText + "' />");
					
					var paymentText = $("#"+tipid+"-r1 td:nth-child(4)").html();
					$("#"+tipid+"-r1 td:nth-child(4)").html("<input type='text' class='oeBox' size='6' name='Payment' id='Payment' value='" + paymentText + "' />");
					
					//no change to checkmark
					
					var depositText = $("#"+tipid+"-r1 td:nth-child(6)").html();
					$("#"+tipid+"-r1 td:nth-child(6)").html("<input type='text' class='oeBox' size='6' name='Deposit' id='Deposit' value='" + depositText + "' />");
					
					$("#"+tipid+"-r1 td:nth-child(8)").html("<input type='submit' class='oeBox' value='save' name='accountFormSubmit' id='accountFormSubmit' />");
					
					var typeText = $("#"+tipid+"-r2 td:nth-child(2)").html();
					$("#"+tipid+"-r2 td:nth-child(2)").html("<input type='text' class='oeBox' size='7' name='Type' id='Type' value='" + typeText + "' />");
					
					var acctText = $("#"+tipid+"-r2 td:nth-child(3)").html();
					$("#"+tipid+"-r2 td:nth-child(3)").html("<span class='ht'><input type='text' class='oeBox' size='23' name='Account' id='Account' value='" + acctText + "' /><span class='tooltip'>This is the account that the money is going to or coming from.</span></span>");
					
					var memoText = $("#"+tipid+"-r2 td:nth-child(4)").html();
					$("#"+tipid+"-r2 td:nth-child(4)").html("<input type='text' class='oeBox' size='23' name='Memo' id='Memo' value='" + memoText + "' />");
				}
				
				if (transHTMLid != 'newTrans') {
					document.accountInfoForm.action = document.accountInfoForm.action.replace("&new","");
				}
			}
			
		}
		
		function confirmTA()
		{
			<?php
			if (isset($_GET['ahid'])) {
			?>
				if (!window.checkNT) {
					openEdits('newTrans');
					
					var prevTransDollarAmount = 0.0;
					var prevFinalBalance = $(".account-details tbody tr:nth-last-of-type(2) td:nth-child(7)").html();
					console.log('unedited amount: ' + prevTransDollarAmount + '..... Final Bal: ' + prevFinalBalance);
					
					$("table.account-details tbody").append("<tr style='background-color: #E0EFE0;' class='newTransRow'><td><input type='hidden' name='uneditedAmount' id='uneditedAmount' value='" + prevTransDollarAmount + "' /><input type='hidden' name='finalBalance' id='finalBalance' value='" + prevFinalBalance + "' /><input type='text' size='8' name='Date' id='Date' /></td><td><input type='text' size='7' name='Num' id='Num' /></td><td colspan='2'><input type='text' size='46' name='Payee' id='Payee' /></td><td><input type='text' size='6' name='Payment' id='Payment' /></td><td><input type='text' size='1' name='checkMark' id='checkMark' /></td><td><input type='text' size='6' name='Deposit' id='Deposit' /></td><td></td><td><input type='submit' value='save' name='accountFormSubmit' id='accountFormSubmit' /></td></tr><tr style='background-color: #fff;' class='newTransRow'><td></td><td><input type='text' size='7' name='Type' id='Type' /></td><td><input type='text' size='23' name='Account' id='Account' /></td><td><input type='text' size='23' name='Memo' id='Memo'  /></td><td></td><td></td><td></td><td></td><td></td></tr>");
					
					var dt = new Date();
					var mon = String(dt.getMonth() + 1);
					var month = ((mon.length == 1) ? "0" + mon : mon);
					var day = ((String(dt.getDate()).length == 1) ? "0" + dt.getDate() : dt.getDate());
					$('#Date').val(month + "-" + day + "-" + dt.getFullYear());
					
					document.accountInfoForm.action = document.accountInfoForm.action + '&new';
					window.checkNT = true;
					window.transHTMLid = 'newTrans';
					//window.prevTransHTMLid = 'newTrans';
					var theBox = $("#account-detail-box");
						theBox.animate({ scrollTop: theBox.prop("scrollHeight") - theBox.height() }, 750);
					
				} else {
					alert('There is already a new transaction input form. Scroll to the bottom of the account ledger.'); return false;
				}
			<?php } else { ?>
			alert('No account has been selected! Cannot add transaction.'); return false; <?php } ?>
		}
		
		function confirmTransfer()
		{
			if ($("#TransferFrom").val() == $("#TransferTo").val())
			{
				alert('Cannot transfer funds to and from the same account.');
				return false;
			}
			else if (parseFloat($("#Amount").val()) == 0.0)
			{
				alert('Cannot create a transfer for $0.00');
				return false;
			}
			return confirm('Are you sure you want to make this transfer?');
		}
		
		$(document).ready(function() {
			// make hover color yellow for both transaction rows
			$(".transItem").hover(function() {
				var ti = this.id;
				var tipid = ti.substring(0, ti.length - 2);
				$("tr[id^='"+tipid+"']").css("background-color","#EDED43");
			}, function() {
				var ti = this.id;
				var tipid = ti.substring(0, ti.length - 2);
				$("#"+tipid+"r1").css("background-color","#E0EFE0");
				$("#"+tipid+"r2").css("background-color","#FFFFFF");
			});
			
			//$('form').attr('autocomplete','off');
			//$(".oeBox").width($(".oeBox").parent().width());
		
		});
	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>
<body class="accounts">
	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	<div id="admin_content">
		<h1 id="account_page_h1">Accounts &raquo;
		<a class="buttonLink" href="accounts.php?newAcct">
			<img class="button" src="images/addAccount.png" alt="create new account" title="create new account" height="32px" width="32px" /></a>
			
		<!--<a class="buttonLink" href="#" onclick="deleteConfirm();">
			<img class="button" src="images/deleteAccount.png" alt="delete selected account" title="delete selected account" height="32px" width="32px" /></a>	
			-->
		<a class="buttonLink" href="#" onclick="confirmTA();">
			<img class="button" src="images/addTransaction.png" alt="create a new transaction for selected account" title="create a new transaction for selected account" height="32px" width="32px" /></a>
			
		<a class="buttonLink" href="#" onclick="deleteTransConfirm();">
			<img class="button" src="images/deleteTransaction.png" alt="deleted selected transaction" title="deleted selected transaction" height="32px" width="32px" /></a>
			
		<a class="buttonLink" href="accounts.php?transfer">
			<img class="button" src="images/moneyTransfer.png" alt="transfer money" title="transfer money" height="32px" width="32px" /></a>
			
		<a class="buttonLink" href="accounts.php?reconcile">
			<img class="button" src="images/reconcile.png" alt="reconcile an account" title="reconcile selected account" height="32px" width="32px" /></a>
			
		</h1>
			<hr />
			<div id="statusBox"></div>
			<?php
			echo '<div id="account-list-box">';
		
			$getaccounts = "SELECT * FROM `accounts` ORDER BY `Type` ASC";
			$stmt = $Database->prepare($getaccounts);
			if ($stmt->execute())
			{
				$result = $stmt->get_result();
				
				echo '<table class="accounts" cellpadding="0" border="0" cellspacing="0"><thead>';
				echo '<tr><th>Name</th><th>Type</th><th>Balance</th></thead><tbody>';
				for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
				{
					$result->data_seek($n);
					$row = $result->fetch_array();
					
					$htmlID = 'accountSel'.$row[0];
					echo '<tr id="'.$htmlID.'" class="r'.($n+1).'" onclick="reloadToDisplayAccount(this.id);">
					<td>'.((strlen($row[1]) <= 17) ? $row[1] : substr($row[1],0,17)."...").'</td>
					<td>'.((strlen($row[3]) <= 14) ? $row[3] : substr($row[3],0,14)."...").'</td>
					<td>'.number_format((float)$row[4], 2).'</td></tr>'; //}
				}
				$result->close();
				echo '</tbody></table>';
			}
			else
			{
				echo "Query to retrieve all accounts' info failed";
			}
			echo '</div>';
			
			// Individual account data box
			echo '
			<div id="account-detail-box">';
			
			// display reconcile header data if applicable
			if (isset($_GET['reconcile']) && isset($_GET['do']))
			{
				$AA = explode(";",$_POST['rAccount']);
				$AN = $AA[1]; 
				$AID = $AA[0];
				echo '<h3>Reconciling &raquo;</h3>
					<hr />
				<form name="reconcileCheckmarks" id="reconcileCheckmarks" method="POST" action="accounts.php?reconcile&submit">
				<table class="reconcile-general" cellpadding="2" cellspacing="2">
					<tr>
						<td><b>Account:</b></td><td>'.$AN.'</td><td><b>Account ID:</b></td><td>'.$AID.'</td><td><b>Deposits:</b></td><td id="deps"></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td><b>Beginning Date:</b></td><td>'.$_POST['sDate'].'</td><td><b>Ending Date:</b></td><td>'.$_POST['eDate'].'</td><td><b>Withdrawals:</b></td><td id="debs"></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td><b>Beginning Balance:</b></td><td id="bbal">'."loading".'</td><td><b>Ending Balance:</b></td><td id="ebal">'."loading".'</td><td><b>&raquo;Net Change:</b></td><td id="difb"></td>
						<td></td>
						<td></td>
						<td><input type="submit" value="save reconciliation" /></td>
					</tr>
				</table>';
			}
			
			
			// display account transaction ledger table headers if applicable
			if (!isset($_GET['newAcct']) && !isset($_GET['transfer']) && !isset($_GET['reconcile'])) 
			{
				echo '
				<form name="accountInfoForm" id="AIF" method="post" onsubmit="return checkForm();" action="account.php?trans&save'.(isset($_GET['ahid'])?'&ahid='.$_GET['ahid']:'').'">
				<table class="account-details'.((isset($_GET['do']))?' reconcile':'').'" cellpadding="0" border="0" cellspacing="0">
				<thead>
					<tr><th>Date</th><th>Num</th><th colspan="2">Payee / Recipient / Name</th><th>Payment</th><th>R</th><th>Deposit</th><th>Balance</th><th>[save]</th></tr>
					<tr><th></th><th>Type</th><th>Account (from)</th><th>Memo</th><th></th><th></th><th></th><th></th><th></th></tr>
				</thead>
				<tbody>';
				// cuts off early with an "open table" which is closed later.
			}
			// or display reconcile tables (credits and withdrawals)
			elseif (isset($_GET['reconcile']) && isset($_GET['do']))
			{
				echo '
				<hr />
				<table id="reconcile-deposits" class="reconcile" cellpadding="1" border="0" cellspacing="0">
				<thead>
					<tr><th colspan="7">Deposits</th></tr>
					<tr><th></th><th>Date</th><th>Num</th><th>Payee</th><th>Account (from)</th><th>Memo</th><th>Deposit</th></tr>
					
				</thead>
				<tbody>
				</tbody>
				</table>
				
				<table id="reconcile-withdrawals" class="reconcile wd" cellpadding="1" border="0" cellspacing="0">
				<thead>
					<tr><th colspan="7">Withdrawals</th></tr>
					<tr><th></th><th>Date</th><th>Num</th><th>Payee</th><th>Account (from)</th><th>Memo</th><th>Withdrawal</th></tr>
					
				</thead>
				<tbody>
				</tbody>
				</table>';
			}
				
				// display account ledger if one is selected
				if (isset($_GET['ahid']))
				{
					$id = $_GET['ahid'];
					//unset($_GET['ahid']);
					$_SESSION['lastAccountHtmlId'] = $id;
					$newid = str_replace("accountSel","",$id);
					
					?>
					<script type="text/javascript">
						window.acctHTMLid = <?php echo json_encode($_SESSION['lastAccountHtmlId']); ?>;
						window.prevAcctHTMLid = <?php echo json_encode($_SESSION['lastAccountHtmlId']); ?>;
						if ($(".highlightRed").length > 0) {$(".highlightRed").removeClass("highlightRed");}
						$('#'+acctHTMLid).addClass("highlightRed");
						if (window.scrollPos) { $("#account-list-box").scrollTop(window.scrollPos); delete window.scrollPos; }
						else { scrollTo(acctHTMLid); }
						document.accountInfoForm.action = "accounts.php?trans&save";
						$("#account-detail-box").scrollTop($("table.account-details tr:last").position().top);
					</script>
					<?php
					
					$stmt = $Database->prepare("SELECT * FROM `transactions` WHERE `AccountID` = ? ORDER BY `date` ASC");
					$stmt->bind_param("s", $newid);
					if ($stmt->execute())
					{
						$res = $stmt->get_result();
						if ($res->num_rows > 0)
						{
							for ($n=0; $n <= ($res->num_rows - 1); $n++)
							{
								$row = $res->fetch_array();
								$transid = $row[0];
								$date = convertdate($row[4],"touser");
								$acctName = $row[2];
								$type = $row[3];
								$num = (is_numeric($row[5]) && strpos($row[5],'.') !== true)?number_format($row[5], 0,'',''):$row[5];
								$name = $row[6];
								//$split = $row[8];
								$memo = (is_numeric($row[7]) && strpos($row[7],'.') !== true)?number_format($row[7], 0,'',''):$row[7];
								$amount = number_format($row[9],2);
								//$db_balance = number_format($row[10],2);
								$running_total = (isset($running_total))?(round($running_total + $row[9], 2)):(round($row[9], 2));
								$rec = $row[11];
								
								echo '<tr id="'.$transid."-r1".'" class="transItem" onclick="openEdits(this.id);"><td>'.$date.'</td><td>'.$num.'</td><td colspan="2">'.$name.'</td><td>'.(($amount < 0)?(str_replace("-","",$amount)):"").'</td><td><input type="checkbox" name="reconcile'.$transid.'" id="reconcile'.$transid.'" '.(($rec == "1")?'checked':'').' /></td><td>'.(($amount >= 0)?$amount:"").'</td><td style="font-weight: bold; '.(($running_total < 0)?'color: #ff0000;':'').'">'.number_format($running_total, 2).'</td><td></td></tr>
								
								<tr id="'.$transid."-r2".'" class="transItem" onclick="openEdits(this.id);"><td></td><td>'.$type.'</td><td>'.$acctName.'</td><td>'.$memo.'</td><td></td><td></td><td></td><td></td><td></td></tr>';

							}
							$res->close();
						}
						else
						{
							echo '<tr><td colspan="9">There are no transactions for this account.</td></tr>';
						}
					}
					else
					{
						echo 'Failed to retrieve individual account data for ID:'.$newid;
					}
					
					?>
						<script type="text/javascript">
							// scroll box to the bottom
							var theBox = $("#account-detail-box");
							theBox.animate({ scrollTop: theBox.prop("scrollHeight") - theBox.height() }, 750); 
							
							//float table headers
							// NEED TO FIND A WAY TO ONLY FLOAT AFTER THE SCROLLDOWN OCCURS BECAUSE IT SLOWS DOWN THE LOAD DRAMATICALLY
							var $table = $('table.accounts');
							$table.floatThead({
								useAbsolutePositioning: false,
								scrollContainer: function($table) {
									return $table.closest('#account-list-box');
								}
							});
							
							/*var $table = $('table.account-details');
							$table.floatThead({
								useAbsolutePositioning: false,
								scrollContainer: function($table) {
									return $table.closest('#account-detail-box');
								}
							});*/
						</script>
					<?php
				}
				// if user opted to create a new account, display account creation form
				elseif (isset($_GET['newAcct']))
				{
					echo '<div id="new-account-form-box">
							<h3>Create new account &raquo;</h3>';
							
					if (!isset($_GET['save']))
					{
						?>
							<br />
							<form name="newAccountForm" id="newAccountForm" action="accounts.php?newAcct&save" method="POST">
							<table class="new-account-form-table" cellpadding="2" cellspacing="2"><tbody>
							<tr><td>
							Account Name:</td><td><input type="text" name="AccountName" id="AccountName" size="28" /></td></tr><tr><td>
							Account Type:</td><td><select name="AccountType" id="AccountType">
								<option value=""></option>
								<option value="Accounts Payable">Accounts Payable</option>
								<option value="Accounts Receivable">Accounts Receivable</option>
								<option value="Bank">Bank</option>
								<option value="Cost of Goods Sold">Cost of Goods Sold</option>
								<option value="Equity">Equity</option>
								<option value="Expense">Expense</option>
								<option value="Fixed Asset">Fixed Asset</option>
								<option value="Income">Income</option>
								<option value="Other Asset">Other Asset</option>
								<option value="Other Current Asset">Other Current Asset</option>
								<option value="Other Current Liabilities">Other Current Liabilities</option>
								</select></td></tr><tr><td>
							Opening Balance:</td><td>$<input type="number" step="any" name="OpeningBalance" id="OpeningBalance" value="0.00" size="7" /></td></tr><tr><td></td><td>
							<input type="submit" value="save new account" id="newAccountSubmit" name="newAccountSubmit" /></td></tr>
							</tbody></table>
							</form>
						<?php
					}
					elseif (isset($_GET['save']) && !isset($_GET['trans'])) // make sure its for an account, not a transaction
					{
						// save new Account and add a beginning balance transaction if balance is > 0
						$name = $_POST['AccountName'];
						$type = $_POST['AccountType'];
						$opbal = $_POST['OpeningBalance'];
						$curbal = $opbal;
						
						$query = "INSERT INTO `accounts` VALUES('', ?, ?, ?, ?,?);";
						$stmt = $Database->prepare($query);
						$stmt->bind_param('sssss',$name,$opbal,$type,$curbal,$a='');
						if ($stmt->execute())
						{
							$success=true;
							$accountID = $stmt->insert_id;
							//global $accountID;
						}
						else
						{
							$success=false;
						}
						
						if ((float)$opbal > 0) { // apparently $opbal is not read properly
							$query2 = "INSERT INTO `transactions` VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
							$stmt = $Database->prepare($query2);
							$type = 'Opening';
							$desc = 'Opening Balance';
							$r = '0';
							$today = (string)date('Y-m-d',time());
							$stmt->bind_param('sssssssssssssss', $a='', $accountID, $name, $type, $today, $b='', $desc, $c='', $d='', $opbal, $opbal, $r, $e='', $f='', $g='');
							if ($stmt->execute())
							{
								$success = true;
							}
							else
							{
								$success = false;
							}
						}
						
						if ($success = true) {
							?>
								<script type="text/javascript">
									window.location = "accounts.php?caa";
								</script>
							<?php
						} else {
							?>
								<script type="text/javascript">
									window.location = "accounts.php?faa";
								</script>
							<?php
						}
						
					}
					
					echo '</div>';
				}
				
				// or display transfer form
				elseif (isset($_GET['transfer']))
				{
					echo '<div id="new-transfer-form-box">
							<h3>Transfer funds &raquo;</h3>';
					
					// default money transfer form
					if (!isset($_GET['save']))
					{
						echo '
						<form name="TransferForm" id="TransferForm" action="accounts.php?transfer&save" method="POST" onsubmit="return confirmTransfer();">
						<table class="new-account-form-table" cellpadding="2" cellspacing="2"><tbody>
						
						<tr><td>Transfer from:</td>
							<td><select name="TransferFrom" id="TransferFrom">';
							
							$bankAccounts = array();
							$bab = array();
							$query = "SELECT `AccountID`,`Name`,`CurrentBalance` FROM `accounts` WHERE `Type` = 'Bank'";
							$stmt = $Database->prepare($query);
							if ($stmt->execute())
							{
								$res = $stmt->get_result();
								for ($x = 0; ($x <= $res->num_rows - 1); $x++)
								{
									$res->data_seek($x);
									$row = $res->fetch_array();
									$bankAccounts[$row[0]] = $row[1];
									$bab[$row[0]] = number_format($row[2],2,'.',','); # "bank account balances[]"
									echo '<option value="'.$row[0].';'.$row[1].'">'.$row[1].' ($'.$bab[$row[0]].')</option>';
								}
								$res->close();
							}
							else
							{
								echo 'Failed to retrieve bank accounts. MySQL statement failed.';
							}
							
						echo '</select>
							</td></tr>
							<tr><td>Transfer to:</td>
								<td><select name="TransferTo" id="TransferTo">';
							
							foreach ($bankAccounts as $key => $value)
							{
								echo '<option value="'.$key.';'.$value.'">'.$value.' ($'.$bab[$key].')</option>';
							}
						
						echo '</select></td></tr>
							<tr><td>Amount: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$</td>
								<td><input type="number" id="Amount" name="Amount" step="any" value="0.00" size="7" /></td></tr>
							
							<tr><td>Memo:</td>
								<td><input type="text" name="Memo" id="Memo" /></td></tr>
								
							<tr><td colspan="2"><input type="submit" value="transfer funds" name="TransferSubmit" id="TransferSubmit" /></td></tr>';
						
						echo '</tbody></table>
						</form>';
					}
					else // SAVE the submitted transfer form transactions
					{
						// a function to make sure the transfer id is unique
						function fix_id($id) 
						{
							global $Database;
							if ($Database->query("SELECT * FROM `transactions` WHERE `TransferID` = '$id'")->num_rows > 0) { 
								$id = $id + 1; fix_id(); 
							} else {
								?>
									<script type="text/javascript">
										console.log('the transfer id is' + <?php echo $id; ?>);
									</script>
								<?php
								
								return $id;
							} 
						}
						
						// success checks
						$sCheck1 = false; $sCheck2 = false; $sCheck3 = false; $sCheck4 = false; 
						
						$tfPost = $_POST['TransferFrom']; // this is the split ID plus the account name (the ID of the account that dough is coming from). It looks like '14;Some Account Name'
						$ttPost = $_POST['TransferTo'];
						$tfArr = explode(";",$tfPost);
						$ttArr = explode(";",$ttPost);
						$tf = $tfArr[0];
						$tfName = $tfArr[1];
						$tt = $ttArr[0];
						$ttName = $ttArr[1];
						
						$amount = (float)$_POST['Amount'];
						$memo = $_POST['Memo'];
						
						//var_dump($_POST);
						
						//				UPDATE ACCOUNT BALANCES
						
						// get the transfer-from account balance
						$getBal = "SELECT `CurrentBalance` FROM `accounts` WHERE `AccountID` = ?";
						$stmt = $Database->prepare($getBal);
						$stmt->bind_param('s',$tf);
						if ($stmt->execute())
						{
							$result = $stmt->get_result();
							$row = $result->fetch_array(); // fetch_array gives number and word indices; fetch_row just gives numbers, fetch_assoc gives just words
							$tfBal = (float)$row[0];
							
							$stmt->close();
							
							// renew the tf account balance with this new transfer
							$newBal = $tfBal - $amount;
							$changeBal = "UPDATE `accounts` SET `CurrentBalance` = ? WHERE `AccountID` = ?";
							$stmt2 = $Database->prepare($changeBal);
							$stmt2->bind_param('ss', $newBal, $tf);
							if ($stmt2->execute())
							{
								//echo 'updated tf account successfully';
								$sCheck1 = true;
								$stmt2->close();
							}
							else
							{
								echo 'Failed to update "transfer-from" account balance.';
							}
							
						}
						else
						{
							echo 'Failed to retrieve "transfer-from" account balance.';
						}
						
						// get transfer-to account balance
						$getBal = "SELECT `CurrentBalance` FROM `accounts` WHERE `AccountID` = ?";
						$stmt = $Database->prepare($getBal);
						$stmt->bind_param('s',$tt);
						if ($stmt->execute())
						{
							$result = $stmt->get_result();
							$row = $result->fetch_array(); // fetch_array gives number and word indices; fetch_row just gives numbers, fetch_assoc gives just words
							$ttBal = $row[0];
							
							$stmt->close();
							
							// renew the tt account balance with this new transfer
							$newBal = $ttBal + $amount;
							$changeBal = "UPDATE `accounts` SET `CurrentBalance` = ? WHERE `AccountID` = ?";
							$stmt2 = $Database->prepare($changeBal);
							$stmt2->bind_param('ss', $newBal, $tt);
							if ($stmt2->execute())
							{
								//echo 'updated tt account successfully';
								$sCheck2 = true;
								$stmt2->close();
							}
							else
							{
								echo 'Failed to update "transfer-to" account balance.';
							}
						}
						else
						{
							echo 'Failed to retrieve "transfer-from" account balance.';
						}
						
						//				CREATE ACCOUNT TRANSACTIONS
						
						// transfer-from transaction
						$tftq = "INSERT INTO `transactions` VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
						$stmt = $Database->prepare($tftq);
						// make unique id
						$id = round(hexdec(md5($tt.$tf.$amount.$memo.rand(1,100000000)))/(pow(10,32)),0);
						$transfer_id = fix_id($id); // defined on line 600
						$type = 'Transfer';
						$today = (string)date('Y-m-d',time());
						$stmt->bind_param('sssssssssssssss', $a='', $tf, $tfName, $type, $today, $transfer_id, $ttName, $memo, $ttName, $b = ($amount * (-1)), $c='', $d='0', $e='', $tt, $transfer_id);
						
						if ($stmt->execute())
						{
							//echo 'Created transfer-from transaction successfully';
							$sCheck3 = true;
							$stmt->close();
						}
						else
						{
							echo 'Failed to create transfer-from transaction successfully';
						}
						
						// transfer-to transaction
						$tttq = "INSERT INTO `transactions` VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
						$stmt = $Database->prepare($tttq);
						$stmt->bind_param('sssssssssssssss', $a='', $tt, $ttName, $type, $today, $transfer_id, $ttName, $memo, $tfName, $amount, $b='', $c='0', $d='', $tf, $transfer_id);
						
						if ($stmt->execute())
						{
							//echo 'Created transfer-to transaction successfully';
							$sCheck4 = true;
							$stmt->close();
						}
						else
						{
							echo 'Failed to create transfer-to transaction successfully';
						}
						
						if ($sCheck1 == true && $sCheck2 == true && $sCheck3 == true && $sCheck4 == true)
						{
							?>
								<script type="text/javascript">
									window.location = "accounts.php?ctr";
								</script>
							<?php
						}
						
					}
					
					echo '</div>';
				}
				
				// or display reconcile form
				elseif (isset($_GET['reconcile']))
				{
					if (!isset($_GET['do']) && !isset($_GET['submit']))
					{
						echo '<div id="reconcile-form-box">
								<h3>Reconcile accounts &raquo;</h3>
								
						<form name="ReconcileSel" id="ReconcileSel" action="accounts.php?reconcile&do" method="POST" onsubmit="">
							<table class="new-account-form-table" cellpadding="2" cellspacing="2"><tbody>
							
							<tr><td>Choose account:</td>
								<td><select name="rAccount" id="rAccount">';

								$query = "SELECT `AccountID`,`Name`,`CurrentBalance` FROM `accounts` WHERE `Type` = 'Bank'";
								$stmt = $Database->prepare($query);
								if ($stmt->execute())
								{
									$res = $stmt->get_result();
									for ($x = 0; ($x <= $res->num_rows - 1); $x++)
									{
										$res->data_seek($x);
										$row = $res->fetch_array();
										echo '<option value="'.$row[0].';'.$row[1].'">'.$row[1].' ($'.number_format($row[2],2,'.',',').')</option>';
									}
									$res->close();
								}
								else
								{
									echo 'Failed to retrieve bank accounts. MySQL statement failed.';
								}
								
						echo '</select>
							</td></tr>
								
							<tr>
								<td>Starting date:</td>
								<td class="ht"><input type="text" name="sDate" id="sDate" /><span class="tooltip">MM-DD-YYYY format only.</span>
								</td>
							</tr>
							
							<tr>
								<td>Ending date:</td>
								<td class="ht"><input type="text" name="eDate" id="eDate" value="'.date("m-d-Y").'" /><span class="tooltip">MM-DD-YYYY format only.</span>
								</td>
							</tr>
							
							<tr><td>Ending balance: $</td>
							<td><input type="number" name="EndingBal" id="EndingBal" step="any" value="0.00" /></td>
							</tr>
							
							<tr><td></td>
							<td><input type="submit" name="doRec" id="doRec" value="begin reconciliation" /></td>
							</tr>
							</table>
						</form>';
					}
					// pull up reconcile form for select account and date range
					else if (isset($_GET['do']) && !isset($_GET['submit'])) # ?do is set
					{
						echo '<div id="reconcile-form-box">';
						$acctid = $_POST['rAccount'];
						echo '<input type="hidden" name="rAccount" id="rAccount" value="'.$_POST['rAccount'].'" />';
						$sDate = convertdate($_POST['sDate'],"tomysql");
						$eDate = convertdate($_POST['eDate'],"tomysql");
						$endingbalance = $_POST['EndingBal'];
						
						//var_dump($_POST);
						
						$stmt = $Database->prepare("SELECT * FROM `transactions` WHERE `AccountID` = ? AND `Date` BETWEEN ? AND ? ORDER BY `date` ASC");
						$stmt->bind_param('sss',$acctid,$sDate,$eDate);
						if ($stmt->execute())
						{
							$res = $stmt->get_result();
							if ($res->num_rows > 0)
							{
								$irArray = array();
								// echoes DATABASE BALANCES (not running total)
								for ($n=0; $n <= ($res->num_rows - 1); $n++)
								{
									$row = $res->fetch_array();
									$transid = $row[0];
									$date = convertdate($row[4],"touser");
									$type = $row[3];
									$num = (is_numeric($row[5]) && strpos($row[5],'.') !== true)?number_format($row[5], 0,'',''):$row[5];
									$name = $row[6];
									$split = $row[8];
									$memo = (is_numeric($row[7]) && strpos($row[7],'.') !== true)?number_format($row[7], 0,'',''):$row[7];
									$amount = number_format($row[9],2);
									$db_balance = number_format($row[10],2);
									$running_total = (isset($running_total))?(round($running_total + $row[9], 2)):(round($row[9], 2));
									$rec = $row[11];
									
									$irArray[$transid] = $rec;
									
									if ($amount >= 0.0) {
										$running_total_deps = (isset($running_total_deps))?(round($running_total_deps + $row[9], 2)):(round($row[9], 2));
										$sort = "dep";
									}
									else if ($amount < 0) {
										$running_total_debs = (isset($running_total_debs))?(round($running_total_debs + $row[9], 2)):(round($row[9], 2));
										$sort = "wd";
									}
									
									if ($n == 0) { $firstBal = '$'.$db_balance; $fbf = $row[10]; $firstAmount = $row[9]; $firstDif = $fbf - $firstAmount; } // first case
									elseif ($n == $res->num_rows - 1) { $endBal = '$'.$db_balance; $ebf = $row[10]; } // last case
									
									// so $firstDif should be equal to the $running_total_deps + $running_total_debs (which is negative)
									
									// echo table rows
									if ($sort=="dep")
									{
										?>
										<script type="text/javascript">
										// add new row to deposits table
										$("table#reconcile-deposits tbody").html($("table#reconcile-deposits tbody").html() + '<tr id="<?php echo $transid; ?>" class="transItemRec" onclick="checkDaBox(this.id);"><td><input type="checkbox" name="reconcile<?php echo $transid; ?>" id="reconcile<?php echo $transid.'" '.(($rec == "1")?'checked':'').' onclick="trig();" />'; ?></td><td><?php echo $date; ?></td><td><?php echo $num; ?></td><td><?php echo $name; ?></td><td><?php echo $split; ?></td><td><?php echo $memo; ?></td><td><?php echo $amount; ?></td></tr>');
										</script>
										<?php
									}
									elseif ($sort=="wd")
									{
										?>
										<script type="text/javascript">
										// add new row to deposits table
										$("table#reconcile-withdrawals tbody").html($("table#reconcile-withdrawals tbody").html() + '<tr id="<?php echo $transid; ?>" class="transItemRec" onclick="checkDaBox(this.id);"><td><input type="checkbox" name="reconcile<?php echo $transid; ?>" id="reconcile<?php echo $transid.'" '.(($rec == "1")?'checked':'').' onclick="trig();" />'; ?></td><td><?php echo $date; ?></td><td><?php echo $num; ?></td><td><?php echo $name; ?></td><td><?php echo $split; ?></td><td><?php echo $memo; ?></td><td><?php echo $amount; ?></td></tr>');
										</script>
										<?php
									}

								}
								$res->close();
								
								// pass all transaction id's with their reconcile status (0 or 1) from array, as a string
								$irData = '';
								foreach ($irArray as $id => $stat)
								{
									$irData = $irData.$id.";"; // so $irData will just be a string with the transaction id's. Not using $stat because the $_POST form will indicate which transactions end up checked
								}
								echo '<input type="hidden" name="irData" id="irData" value="'.$irData.'" />';
								
								?>
									<script type="text/javascript">
										//console.log("<?php echo $irData; ?>");
										$("#definedEbal").html(<?php echo json_encode($endingbalance); ?>);
										$("#bbal").html(<?php echo json_encode('$'.number_format($firstDif,2)); ?>);
										$("#bbal").addClass("ht");
										$("#bbal").append(<?php echo json_encode('<span class="tooltip"><span class="btt">'.$firstBal.'</span> - <span class="gtt">'.$firstAmount.'</span> = '.$firstDif.'</span>'); ?>);
										
										/* $("#bbal").hover(function() {
											$("table.reconcile tbody tr:nth-child(1) td:nth-child(4)").css("color","#aa00ff");
											$("table.reconcile tbody tr:nth-child(1) td:nth-child(6)").css("color","#aa00ff");
											$("table.reconcile tbody tr:nth-child(1) td:nth-child(7)").css("color","#0000ff");
										}, function() {
											$("table.reconcile tbody tr:nth-child(1) td:nth-child(4)").css("color","#4F040B");
											$("table.reconcile tbody tr:nth-child(1) td:nth-child(6)").css("color","#4F040B");
											$("table.reconcile tbody tr:nth-child(1) td:nth-child(7)").css("color","#4F040B");
										}); */
										
										$("#ebal").html(<?php echo json_encode($endBal); ?>);
										$("#deps").html(<?php echo json_encode('$'.number_format($running_total_deps,2)); ?>);
										$("#debs").html(<?php echo json_encode('$'.number_format($running_total_debs * (-1),2)); ?>);
										
										$("#difb").html(<?php echo json_encode('$'.number_format(($ebf - $firstDif),2)); ?>);
										$("#difb").addClass("ht");
										$("#difb").append(<?php echo json_encode('<span class="tooltip"><span class="btt">'.number_format($running_total_deps,2).' - '.number_format(($running_total_debs * (-1)),2).'</span> = '.number_format(($ebf - $firstDif),2).'<br /><span class="gtt">'.$endBal.' - '.number_format($firstDif,2).'</span> = '.number_format(($ebf - $firstDif),2).'</span>'); ?>);
										
										$("#difb").hover(function() {
											$("#bbal").css("color","#aa00ff"); $("#ebal").css("color","#aa00ff");
											$("#deps").css("color","#0000ff"); $("#debs").css("color","#0000ff");
										}, function() {
											$("#bbal").css("color","#000"); $("#ebal").css("color","#000");
											$("#deps").css("color","#000"); $("#debs").css("color","#000");
										});
						
									</script>
								<?php
							}
							else
							{
								echo '<tr><td colspan="9">There are no transactions for this account and date range.</td></tr>';
							}
						}
						else
						{
							echo 'ERROR: Failed to execute MySQL statement. Failed to pull transactions for selected account and date range.';
						}
					}
					else if (isset($_GET['submit']) && !isset($_GET['do'])) # ?submit is set
					{
						// save the reconciliation data (essentially the checkmarks, and update accounts table with most recent reconciliation date (today))
						echo '<div id="reconcile-form-box">
							<h3>Reconcile accounts &raquo;</h3>';
							
							//var_dump($_POST); echo '<br /><br /><br />';
							
							$checkedTransArray = array();
							foreach ($_POST as $key => $value)
							{
								if (strpos($key,"reconcile") !== false)
								{
									array_push($checkedTransArray, str_replace("reconcile","",$key));
								}
							}
							$allTransArray = explode(";",$_POST['irData']);
							
							//var_dump($checkedTransArray);echo '<br /><br />';
							//var_dump($allTransArray);
							
							$queriesArray = array();
							foreach ($allTransArray as $key => $value)
							{
								$checked = false;
								foreach ($checkedTransArray as $ck => $cv)
								{
									if ($value == $cv) {
										$checked = true;
										break;
									}
								}
								
								if ($checked == true)
								{
									$q = "UPDATE `transactions` SET `Reconciled`='1' WHERE `TransID`='".$value."';";
									array_push($queriesArray, $q);
								}
								else
								{
									$q = "UPDATE `transactions` SET `Reconciled`='0' WHERE `TransID`='".$value."';";
									array_push($queriesArray, $q);
								}
							}
							
							//echo '<br /><br /><br />';
							//var_dump($queriesArray);
							
							$error = false;
							foreach ($queriesArray as $key => $query)
							{
								if ($stmt = $Database->query($query))
								{
									$g=0; // nada					
								}
								else
								{
									$error == true;
								}
							}
							
							if ($error == true)
							{
								echo 'Something went wrong. One or more of the database queries failed. The data was not saved properly.';
							}
							else
							{
								// save was successful.
								?>
								<script type="text/javascript">
									window.location="accounts.php?crs";
								</script>
								<?php
							}
						// done saving data for reconcile form (checkboxes)
					}
				}
				// done with reconcile GET variable functions
				
				// close account ledger table and form and add accountID to POST form
				if (!isset($_GET['newAcct'])) 
				{
					echo '</tbody>
					</table>';
					
					if (isset($_GET['ahid'])) { $aid = str_replace("accountSel","",$_GET['ahid']); }
					else { $aid = ''; }
					
					if (!isset($_GET['reconcile'])) { // account ID already provided for reconcile form; not needed
						echo '
						<input type="hidden" name="accountID" id="accountID" value="'.$aid.'" />';
					}
					
					// GET ACCOUNT NAME FOR PROVIDED ID
					/*
					if ($aid != '') {
						$query = "SELECT `Name` FROM `accounts` WHERE `AccountID` = ?";
						$stmt = $Database->prepare($query);
						$stmt->bind_param('s',$aid);
						if ($stmt->execute())
						{
							$result = $stmt->get_result();
							$row = $result->fetch_array();
							$accountName = $row[0];
						}
						else
						{
							$accountName = '';
							echo 'MySQL ERROR: Failed to retrieve account name for ID #'.$aid;
						}
					}
					
					echo '
					<input type="hidden" name="accountName" id="accountName" value="'.$accountName.'" />';
					*/
					
					echo '
					</form>';
					
					/* also put the account name in the <h1>
					?>
						<script type="text/javascript">
							$("#account_page_h1").append("&raquo;Viewing: <span style='font-size: 18px;'><?php echo $accountName; ?></span>");
						</script>
					<?php
					*/
				}
				
			echo '</div>';
		
		//check save, delete, etc. status
		if (isset($_GET['save']) && isset($_GET['trans']))
		{
			// save edited transaction
			if (isset($_POST['selectedID'])) {
				$transID = $_POST['selectedID'];
			} else {
				$transID = '';
			}
			$accountID = $_POST['accountID'];
			
			//$accountName = $_POST['accountName'];
			$Date = convertdate($_POST['Date'],"tomysql");
			$Num = $_POST['Num'];
			$Payee = $_POST['Payee'];
			$Payment = floatval(preg_replace('/[^\d.]/', '', $_POST['Payment']));
			$Deposit = floatval(preg_replace('/[^\d.]/', '', $_POST['Deposit']));
			if ($Payment == '') { $Amount = $Deposit; }
			elseif ($Deposit == '') { $Amount = ($Payment * (-1)); }
			
			// calculate new final balance for the account
			$uneditedAmount = floatval(preg_replace('/[^\d.-]/', '', $_POST['uneditedAmount'])); // keeping the minus dash is important here
			$finalBalance = floatval(preg_replace('/[^\d.-]/', '', $_POST['finalBalance']));
			$newFinalBalance = $finalBalance - $uneditedAmount + $Amount;
			?>
				<script type="text/javascript">
					console.log("<?php echo $finalBalance.'(final) - '.$uneditedAmount.'(unedited) + '.$Amount.' = '.$newFinalBalance.'(value to save)'; ?>");
				</script>
			<?php
						
			if (isset($_POST['reconcile'.$transID]) && $_POST['reconcile'.$transID] == "on") {
				$Checkmark = "1";
			} else {
				$Checkmark = "0";
			}
			$Type = $_POST['Type'];
			$Account = $_POST['Account'];
			$Memo = $_POST['Memo'];
	
			if (!isset($_GET['new'])) {
					$saveTrans = 'REPLACE INTO transactions 
					SET `TransID` = ?, `AccountID` = ?, `AcctName` = ?, `Type` = ?, `Date` = ?, `Num` = ?, `Name` = ?, `Memo` = ?, `Split` = ?, `Amount` = ?, `Balance` = ?, `Reconciled` = ?, `VendorID` = ?, `SplitID` = ?, `TransferID` = ?';
				} else {
					$saveTrans = 'INSERT INTO transactions VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
				}
			
			$stmt = $Database->prepare($saveTrans);
				$stmt->bind_param('sssssssssssssss',$transID,$accountID,$Account,$Type,$Date,$Num,$Payee,$Memo,$b='',$Amount,$newFinalBalance,$Checkmark,$e='',$f='',$g='');
				if ($stmt->execute())
				{
					// transaction was saved. Now save new account balance.
					$saveAcctBal = "UPDATE `accounts` SET `CurrentBalance` = ? WHERE `AccountID` = ?";
					$stmt = $Database->prepare($saveAcctBal);
					$stmt->bind_param('ss',$newFinalBalance,$accountID);
					if ($stmt->execute())
					{
						?>
						<script type="text/javascript">
						window.location = "accounts.php?cte";
						</script>
						<?php
						$stmt->close();
					}
					else
					{
						?>
						<script type="text/javascript">
						document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Account balance was not updated, BUT the transaction was saved successfully.";
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
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Transaction info was not successfully saved.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
					</script>
					<?php
				}
		}
		elseif (isset($_GET['cte']))
		{
			?>
			<script type="text/javascript">
				document.getElementById('statusBox').innerHTML = "Saved transaction successfully. Reloading account...";
				$("#statusBox").fadeIn();
				setTimeout(function(){
					$("#statusBox").fadeOut();
					window.location = "accounts.php?ahid" + <?php echo json_encode($_SESSION['lastAccountHtmlId']); ?>;
				}, 10);
			</script>
			<?php
		}
		elseif (isset($_GET['caa']))
		{
			?>
			<script type="text/javascript">
				document.getElementById('statusBox').innerHTML = "Saved account successfully.";
				$("#statusBox").fadeIn();
				setTimeout(function(){
					$("#statusBox").fadeOut();
				}, 2500);
			</script>
			<?php
		}
		elseif (isset($_GET['faa']))
		{
			?>
			<script type="text/javascript">
				document.getElementById('statusBox').innerHTML = "Failed to save account details.";
				$("#statusBox").fadeIn();
				setTimeout(function(){
					$("#statusBox").fadeOut();
				}, 7500);
			</script>
			<?php
		}
		elseif (isset($_GET['cad']))
		{
			?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Deleted the account successfully.";
			$("#statusBox").fadeIn();
			setTimeout(function(){
				$("#statusBox").fadeOut();
			}, 2500);
			</script>
			<?php
		}
		elseif (isset($_GET['ctr']))
		{
			?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Created and saved money transfer successfully.";
			$("#statusBox").fadeIn();
			setTimeout(function(){
				$("#statusBox").fadeOut();
				<?php
				if (isset($_SESSION['lastAccountHtmlId'])) {
					?>
					window.location = "accounts.php?ahid" + <?php echo json_encode($_SESSION['lastAccountHtmlId']); ?>;
					<?php
				}
				?>
			}, 2500);
			</script>
			<?php
		}
		elseif (isset($_GET['crs']))
		{
			?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Account reconciliation data was saved successfully.";
			$("#statusBox").fadeIn();
			setTimeout(function(){
				$("#statusBox").fadeOut();
				<?php
				if (isset($_SESSION['lastAccountHtmlId'])) {
					?>
					window.location = "accounts.php?ahid" + <?php echo json_encode($_SESSION['lastAccountHtmlId']); ?>;
					<?php
				}
				?>
			}, 2500);
			</script>
			<?php
		}
		// if account was viewed in previous session and user is on session a-fresh,
		// show that account (only if not performing/confirming a delete/creation of account)
		// and if not saving new/edited transaction, and not doing transfer
		// and if not using reconcile function
		elseif (isset($_SESSION['lastAccountHtmlId']) && $_SESSION['lastAccountHtmlId'] != '' && !isset($_GET['cad']) && !isset($_GET['caa']) && !isset($_GET['newAcct']) && !isset($_GET['save']) && !isset($_GET['ahid']) && !isset($_GET['transfer']) && !isset($_GET['reconcile']))
		{
			?>
			<script type="text/javascript">
				reloadToDisplayAccount(<?php echo json_encode($_SESSION['lastAccountHtmlId']); ?>); 
			</script>
			<?php
		}
		?>	
			
	</div>
</body>
</html>
