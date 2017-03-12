<?php 
/*
	/admin/tickets.php
	Handles appointments by date or appointment ID for viewing/editing/deleting
	Made by Douglas Franz, freelance PHP/MySQL/HTML/CSS/JS/jQuery-ist.
*/

session_start();
date_default_timezone_set('America/New_York');
ob_implicit_flush(true);
include('models/auth.php');
include('includes/calendar.php');
include('includes/database.php');
include('includes/datetime_functions.php');
// first display default (NOT printing) view
if (!isset($_GET['print']))
{
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
	
		// save employee select element with names first
		var employeeSelectHtmlString = '';
		$.ajax(
		{
				url: "ajax/echoEmployeeNames.php",
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					window.employeeSelectHtmlString = data;
				}
		});
		
		function convertdate(date,func) 
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
		
		function timetouser (input) // takes mysql time XX:XX:XX and converts to X:XX am/pm
		{
			var atoms = input.split(":");
			var h= atoms[0];
			var m = atoms[1]; 
			var s = atoms[2]; 
			
			if (h[0] == "0" && h != "00") 
			{
				h = h.replace("0","");
			}
			if (h > 12) 
			{
				h = (h - 12); i = "pm";
			}
			else if (h==12)
			{
				i = "pm";
			}
			else 
			{
				i = "am";
			}
			var output = h + "" + ':' + "" + m + "" + i;		

			return output;
		}
		
		function addTime(timeString, durString)
		{
			dateObject = new Date();
			var atoms = timeString.split(":");
			var h = atoms[0];
			var m = atoms[1]; 
			var s = atoms[2]; 
			dateObject.setHours(h, m, s, 0);
			
			dateObject.setTime(dateObject.getTime() + (1000 * 60 * durString)); // add minutes as milliseconds to time
			
			var nH = (dateObject.getHours() < 10) ? "0" + dateObject.getHours() : dateObject.getHours();
			var nM = (dateObject.getMinutes() < 10) ? "0" + dateObject.getMinutes() : dateObject.getMinutes();
			var nS = (dateObject.getSeconds() < 10) ? "0" + dateObject.getSeconds() : dateObject.getSeconds();
			
			newTimeString = nH + ":" + nM + ":" + nS;
			
			return newTimeString;
		}
		
		function calcTimes(rowID)
		{
			rowIDnum = parseFloat(rowID.replace("apptDetails",""));
			// i don't think i even need to use rowIDnum as the x var in the for loop here... appears not.
			for (x = 1; x <= 29; x++)
			{
				var timeString = '00:00:00';
				var durString = 0;
				
				// :nth-child error somewhere below... dkfhaurwhgpaurwhg
				
				if ($("#apptDetails" + x + " td:nth-child(2)").html() == '') {
					continue;
				} else if ($("#apptDetails" + x + " td:nth-child(2) input").length > 0 && $("#apptDetails" + x + " td:nth-child(2) input").val() != '') {
					timeString = $("#apptDetails" + x + " td:nth-child(2) input").val();
				} else if ($("#apptDetails" + x + " td:nth-child(2)").html() != '') {
					timeString = $("#apptDetails" + x + " td:nth-child(2)").html();
				} else {
					continue;
				}
				
				if ($("#apptDetails" + x + " td:nth-child(3)").html() == '') {
					continue;
				} else if ($("#apptDetails" + x + " td:nth-child(3) input").length > 0 && $("#apptDetails" + x + " td:nth-child(3) input").val() != '') {
					durString = Math.round(parseFloat($("#apptDetails" + x + " td:nth-child(3) input").val()));
				} else if ($("#apptDetails" + x + " td:nth-child(3)").html() != '') {
					durString = Math.round(parseFloat($("#apptDetails" + x + " td:nth-child(3)").html()));
				} else {
					continue;
				}
				
				var addedTimeSum = addTime(timeString, durString);
				dummyDate = new Date();
					var atoms = addedTimeSum.split(":");
					var h = atoms[0];
					var m = atoms[1]; 
					var s = atoms[2]; 
					dummyDate.setHours(h, m, s, 0);
					
				var nextRowTimeString = '00:00:00';
				if ($("#apptDetails" + (x + 1) + " td:nth-child(2)").html() == '') {
					continue;
				} else if ($("#apptDetails" + (x + 1) + " td:nth-child(2) input").length > 0 && $("#apptDetails" + x + " td:nth-child(2) input").val() != '') { // probably unnecessary
					nextRowTimeString = $("#apptDetails" + (x + 1) + " td:nth-child(2) input").val();
				} else if ($("#apptDetails" + (x + 1) + " td:nth-child(2)").html() != '') {
					nextRowTimeString = $("#apptDetails" + (x + 1) + " td:nth-child(2)").html();
				} else {
					continue;
				}
	
				nextRowDummyDate = new Date();
					var atoms2 = nextRowTimeString.split(":");
					var h2 = atoms2[0];
					var m2 = atoms2[1]; 
					var s2 = atoms2[2]; 
					nextRowDummyDate.setHours(h2, m2, s2, 0);
				
				// only (re-)calculate times IF the next time is not greater than the auto-sum
				// which basically means the user wants an intentional time gap between services
				if (Number(dummyDate) >= Number(nextRowDummyDate))
				{
					//console.log('previous time was after or the same as next time');
					
						$("#apptDetails" + (x+1) + " td:nth-child(2)").html(addedTimeSum);
						//console.log('the addedTimeSum is: ' + addedTimeSum);
					
					/* NOT SURE IF THIS REALLY DOES ANYTHING
					else if ($("#apptDetails" + x + " td:nth-child(3)").html() == '' && $("#apptDetails" + (x + 1) + " td:nth-child(2)").html() != '' && $("#apptDetails" + (x + 2) + " td:nth-child(2)").html() == '')
					{
						$("#apptDetails" + (x + 1) + " td:nth-child(2)").html('');
					} */
					}
					else
					{
						console.log('dummyDate thing returned false.');
					}
			}
			console.log("Ran calcTimes(). RowIDnum is: " + rowIDnum);
		}

		function calcTotals() 
		{
			var cn = 0;
			for (var e = 1; e <= 30; e++)
			{
				if ($("#apptDetails"+e+" td:nth-child(7)").html().length > 0 && $("#apptDetails"+e+" td:nth-child(9)").html().length > 0)
				{
					cn++;
				}
			}
			for (var p = 1; p <= cn; p++)
			{
				// fill 10th col. with totals
				if ($("#apptDetails"+p+" td:nth-child(7) input").length > 0 && $("#apptDetails"+p+" td:nth-child(7) input").val() != '')
				{
					$("#apptDetails"+p+" td:nth-child(10)").html(
					(parseFloat($("#apptDetails"+p+" td:nth-child(7) input").val()) * parseFloat($("#apptDetails"+p+" td:nth-child(9) input").val())).toFixed(2)
					);
				}
				else if ($("#apptDetails"+p+" td:nth-child(7) input").val() != '')
				{
					$("#apptDetails"+p+" td:nth-child(10)").html(
					(parseFloat($("#apptDetails"+p+" td:nth-child(7)").html()) * parseFloat($("#apptDetails"+p+" td:nth-child(9)").html())).toFixed(2)
					);
				}
				else if (($("#apptDetails"+p+" td:nth-child(7)").html() != ''))
				{
					$("#apptDetails"+p+" td:nth-child(10)").html(
					(parseFloat($("#apptDetails"+p+" td:nth-child(7)").html()) * parseFloat($("#apptDetails"+p+" td:nth-child(9)").html())).toFixed(2)
					);
				}
				
				// convert NaN to blank
				if ($("#apptDetails" + p + " td:nth-child(10)").html() == 'NaN') {
					$("#apptDetails" + p + " td:nth-child(10)").html('');
				}
			}
			
			var t1 = ($("#Tender1").val() != '' && $("#Tender1").val() != 'NaN') ? parseFloat($("#Tender1").val()) : 0;
			var t2 = ($("#Tender2").val() != '' && $("#Tender2").val() != 'NaN') ? parseFloat($("#Tender2").val()) : 0;
			var tip = ($("#Tip").val() != '') ? parseFloat($("#Tip").val()) : 0;
			//var changea = ($("#Change").val() != '') ? parseFloat($("#Change").val()) : 0;

			rowTotals = new Array();
			rowTotals[0] = 0;
			rowTaxCheck = new Array();
			rowTaxCheck[0] = "0";
			var c = 0;
			for (x=1; x<=30; x++)
			{
				if ($("#apptDetails"+x+" td:nth-child(10)").html() != '' && $("#apptDetails"+x+" td:nth-child(10)").html() != 'NaN')
				{
					c += 1;
				}
			}
			//console.log('c = '+c);
			for (y=1; y <= c; y++)
			{
				rowTotals[y] = (($("#apptDetails"+y+" td:nth-child(10)").html() != '') ? parseFloat($("#apptDetails"+y+" td:nth-child(10)").html()) : 0);
				if ($("#Taxable"+y).prop("checked"))
				{
					rowTaxCheck[y] = "1";
				} else {
					rowTaxCheck[y] = "0";
				}
			}
			sum = 0;
			taxSum = 0;
			
			//console.log('rowTotals[]: ' + rowTotals);
			//console.log('rowTaxCheck[]: ' + rowTaxCheck);
			
			for (z=1; z <= rowTotals.length - 1; z++)
			{
				if (rowTaxCheck[z] == "1")
				{ taxSum += (rowTotals[z] * 0.07); } // 7% tax in FL
				sum += rowTotals[z];
			}
			
			//$("#Change").val(changea.toFixed(2));
			$("#Subtotal").val(sum.toFixed(2));
			$("#Tax").val(taxSum.toFixed(2));
			$("#Total").val((sum+taxSum+tip).toFixed(2));
			$("#Paid").val(parseFloat(t1+t2).toFixed(2));
			var chanval = ($("#Change").val() != '' && $("#Change").val() != 'NaN') ? parseFloat($("#Change").val()).toFixed(2) : 0.00;
			$("#Due").val(parseFloat(parseFloat($("#Total").val()) - parseFloat($("#Paid").val()) + parseFloat(chanval)).toFixed(2));
			
			//console.log('ran the calcTotals() function');
			
		}
		
		function displayApptData(TicketID) {
			$.ajax(
			{
				url: "ajax/echoApptData.php?selected_id=" + TicketID,
				type: 'GET',
				dataType: 'json',
				success: function(data)
				{
					// general ticket data
					$("#TicketID").val(data['ticket'][0]);
					
					if (data['ticket'][9] == "Open"){	
						$("#tsOpen").prop("checked", true);}
					else if (data['ticket'][9] == "Closed"){
						$("#tsClosed").prop("checked",true);}
					else if (data['ticket'][9] == "Canceled") {
						$("#tsCanceled").prop("checked",true); }
					else { $("#tsOpen").prop("checked",true); }

					// populate the hidden original ticket status
					$("#tickstat").val(data['ticket'][9]);
					
					var dc = data['ticket'][3].substring(0,10);
					var tc = data['ticket'][3].substring(11);
					$("#dtCreated").val(convertdate(dc,"touser") + " at " + timetouser(tc));
					$("#DateScheduled").val(convertdate(data['ticket'][4],"touser"));
					$("#ClientID").val(data['ticket'][10]);
					$("#ClientName").val(data['ticket'][11] + " " + data['ticket'][12]);
					
					$("#Creator").val(data['ticket'][2]);
					//paytype 1
					var exists = false;
						for(var i = 0, opts = document.getElementById('PayType1').options; i < opts.length; ++i)
						   if( opts[i].value === data['ticket'][17] )
						   {
							  exists = true; 
							  break;
						   }
					if (exists == true) { 
						$('[name="PayType1"]').val(data['ticket'][17]);
					}
					$("#PayData1").val(data['ticket'][18]);
					$("#Tender1").val(parseFloat(data['ticket'][16]).toFixed(2));
					if ($("#Tender1").val() == 'NaN') { $("#Tender1").val(''); }
					//paytype 2
					var exists = false;
						for(var i = 0, opts = document.getElementById('PayType2').options; i < opts.length; ++i)
						   if( opts[i].value === data['ticket'][20] )
						   {
							  exists = true; 
							  break;
						   }
					if (exists == true) { 
						$('[name="PayType2"]').val(data['ticket'][20]);
					}
					$("#PayData2").val(data['ticket'][21]);
					$("#Tender2").val(parseFloat(data['ticket'][19]).toFixed(2));
					if ($("#Tender2").val() == 'NaN') { $("#Tender2").val(''); }
					$("#Subtotal").val(parseFloat(data['ticket'][13]).toFixed(2));

					if (data['ticket'][15] == '' || data['ticket'][15] == null) {
						 tipval = 0.00;
					} else {
						tipval = parseFloat(data['ticket'][15]).toFixed(2);
					}
					$("#Tip").val(tipval);
					$("#TipFor").val(data['ticket'][27]);
					//console.log(data['ticket'][27]);

					$("#Tax").val(parseFloat(data['ticket'][14]).toFixed(2));
					$("#Total").val(parseFloat(data['ticket'][24]).toFixed(2));

					if (data['ticket'][22] == '' || data['ticket'][22] == null) {
						chval = 0.00;
					} else {
						chval = parseFloat(data['ticket'][22]).toFixed(2);
					}
					$("#Change").val(chval);
					$("#ChangeType").val(data['ticket'][23]);
					
					$("#TicketComment").html(data['ticket'][25]);

					// DETAIL ROWS ==================
					var numDetailRows = data['details'].length;
					for (x=1; x <= numDetailRows; x++)
					{

						// populate hiddent POST element "initialRows"
						$("#initialRows").val($("#initialRows").val() + " " + data['details'][x-1][0]);

						// the ticketdetails MySQL table RowID column value (hidden by CSS)
						$("#apptDetails" + x + " td:nth-child(11)").html(data['details'][x-1][0]);
						// end hidden td

						$("#apptDetails" + x + " td:nth-child(1)").html(data['details'][x-1][14]);
						if (data['details'][x-1][10] != '00:00:00') {
							$("#apptDetails" + x + " td:nth-child(2)").html(data['details'][x-1][10]);
						}
						if (data['details'][x-1][21] != '0') {
							$("#apptDetails" + x + " td:nth-child(3)").html(data['details'][x-1][21]);
						}
						
						$("#apptDetails" + x + " td:nth-child(4)").html(data['details'][x-1][5]);
						
						if (data['details'][x-1][22] != null) {
							$("#apptDetails" + x + " td:nth-child(5)").html(data['details'][x-1][22]); 
						}
						else if (data['details'][x-1][23] != null) {
							$("#apptDetails" + x + " td:nth-child(5)").html(data['details'][x-1][23]); 
						}
						else {
							$("#apptDetails" + x + " td:nth-child(5)").html("none"); 
						}
						
						$("#apptDetails" + x + " td:nth-child(6)").html(data['details'][x-1][15]);
						$("#apptDetails" + x + " td:nth-child(7)").html(parseFloat(data['details'][x-1][16]).toFixed(2));

						if (data['details'][x-1][24] == "0") {
							$("#apptDetails" + x + " td:nth-child(8)").html("<input type='checkbox' name='Taxable"+x+"' id='Taxable"+x+"' />");
						}
						else if (data['details'][x-1][24] == "1") { // index 24 is 25th column "Taxable" in MySQL
							$("#apptDetails" + x + " td:nth-child(8)").html("<input type='checkbox' name='Taxable"+x+"' id='Taxable"+x+"' checked />");
						}
						$("#apptDetails" + x + " td:nth-child(9)").html(data['details'][x-1][17]);
						$("#apptDetails" + x + " td:nth-child(10)").html(parseFloat(data['details'][x-1][18]).toFixed(2));
					}
					
					calcTotals();		
				}
			});
		}
		
		function triggerChange()
		{
			$("#changeIndicator").val("0" + $("#changeIndicator").val()).trigger('change');
		}
		
		function saveRowValues() 
		{
			if ($("#Employee").length > 0) {
				window.prevItemEmployee = $("#Employee option:selected").attr("value");
			} else {
				window.prevItemEmployee = $("#" + itemHTMLid + " td:nth-child(1)").html();
			}
			//console.log('window.prevItemEmployee was saved to ' + window.prevItemEmployee);
			//console.log('the length of #Employee is: ' + $("#Employee").length);
	
			window.prevItemStartTime = $("#StartTime").val();
			
			window.prevItemDuration = $("#Duration").val();
			
			window.prevItemType = $("#"+itemHTMLid+" td:nth-child(4)").html();
			window.prevItemID = $("#"+itemHTMLid+" td:nth-child(5)").html();
			
			window.prevItemDescription = $("#Description").val();
			
			if ($("#Price").length > 0 && (isNaN($("#Price").val()) || $("#Price").val() == '')) {
				window.prevItemPrice = '';
			} else {
				window.prevItemPrice = ($("#Price").length > 0 && $("#Price").val() != '' ? parseFloat($("#Price").val()).toFixed(2) : parseFloat($("#"+itemHTMLid+" td:nth-child(7)").html()).toFixed(2));
			}
			//DONT CHANGE THE TAX CHECKBOXES

			if ($("#Quantity").length > 0 && (isNaN($("#Quantity").val()) || $("#Quantity").val() == '')) {
				window.prevItemQty = '';
			} else {
				window.prevItemQty = ($("#Quantity").length > 0 && $("#Quantity").val() != '' ? parseFloat($("#Quantity").val()).toFixed(0) : parseFloat($("#"+itemHTMLid+" td:nth-child(9)").html()).toFixed(0));
			}
			
			window.prevItemTotal = $("#"+itemHTMLid+" td:nth-child(10)").html();

			window.prevItemRowID = $("#"+itemHTMLid+" td:nth-child(11)").html();
			//console.log('saved prevItem window variables to html contents of the row, ' + itemHTMLid + '.');
			
			// re-initiate change event handler on select element (needed for some reason)
			//$("select#Employee").change(triggerChange);
				//$("select#Employee").change(console.log('the select employee element changed.'));
				
			calcTotals();
		}
		
		function openEdits(itemHTMLid) 
		{
			// first check for open/closed status and direct user to open the ticket before editing, if this is a closed ticket
			if ($('#tsClosed').is(':checked')) {
				var open_confirmation = confirm("This ticket is closed. Open it to make changes?");
				if (open_confirmation) {
					//alert('going to open then save');
					//$("#tsClosed").prop("checked", false);
					$("#tsOpen").prop("checked", true);
					submitTicketForm("none"); // "none" means don't delete any rows.

				} else {
					//alert('fine.');
				}
			}
	
			var rowNum = parseFloat(itemHTMLid.replace("apptDetails","")).toFixed(0);
			var rowAboveID = "apptDetails" + (rowNum - 1);
			
			if ($("#"+ rowAboveID +" td:nth-child(10)").html() == '' || $("#"+ rowAboveID +" td:nth-child(10)").html() == 'NaN') { return false; }
			if (window.itemHTMLid == itemHTMLid) {return false;}
			else 
			{
				var currentEmp = $("#" + itemHTMLid + " td:nth-child(1)").html();
				//console.log('currentEmp: ' + currentEmp);
				if (window.prevItemHTMLid) {
					// replace form inputs with EDITED text on previous row (do only once)
					$("#"+window.prevItemHTMLid+" td:nth-child(1)").html(window.prevItemEmployee);
					$("#"+window.prevItemHTMLid+" td:nth-child(2)").html(window.prevItemStartTime);
					$("#"+window.prevItemHTMLid+" td:nth-child(3)").html(window.prevItemDuration);
					$("#"+window.prevItemHTMLid+" td:nth-child(4)").html(window.prevItemType);
					$("#"+window.prevItemHTMLid+" td:nth-child(5)").html(window.prevItemID);
					$("#"+window.prevItemHTMLid+" td:nth-child(5) button").remove(); // remove find buttons
					$("#"+window.prevItemHTMLid+" td:nth-child(6)").html(window.prevItemDescription);
					$("#"+window.prevItemHTMLid+" td:nth-child(7)").html(window.prevItemPrice);
					//$("#"+window.prevItemHTMLid+" td:nth-child(8)").html(window.prevItemTax);
					$("#"+window.prevItemHTMLid+" td:nth-child(9)").html(window.prevItemQty);
					$("#"+window.prevItemHTMLid+" td:nth-child(10)").html(window.prevItemTotal);
					$("#"+window.prevItemHTMLid+" td:nth-child(11)").html(window.prevItemRowID);
				}
				
				//set current id
				window.itemHTMLid = itemHTMLid;
				// set current id as future previous id
				window.prevItemHTMLid = itemHTMLid;
				// set MySQL RowID to hidden 11th column
				var sid = $("#"+itemHTMLid+ " td:nth-child(11)").html();
				
				// see if the previous employee name exists in the list
				var EExists = false;
				var selectObject = $('select[name*=' + 'Employee' + ']');
				if (selectObject.find('option[value=' + currentEmp + ']')) {
					EExists = true;
					//console.log('just made "EExists" true');
					
					// ALWAYS RETURNS TRUE. THIS IS A PROBLEM
					
				}
					//console.log(this.value);
				//console.log('EExists: ' + EExists);
				
				// employee dropdown
				$("#"+itemHTMLid+" td:nth-child(1)").html(window.employeeSelectHtmlString);
				
				//set onchange event for the select element
				$("#Employee").change(saveRowValues);
				
				// set employee dropdown to proper value
				if (EExists == true)
				{	
					//console.log('employee name exists in select element.');
					//select already existing and selected employee
					$('[name=Employee]').val(currentEmp);
				}
				else {
					console.log(currentEmp + ' not found in select element.');
					//create new option and select it for employee
					 $('#Employee')
						 .append($("<option></option>")
						 .attr("value",window.currentEmp)
						 .text(window.currentEmp)); 
					$('[name=Employee]').val(window.currentEmp);
				}
				
				//replace ID with ID + button (or just button if ID is blank)
				$("#"+itemHTMLid+" td:nth-child(5)").html($("#"+itemHTMLid+" td:nth-child(5)").html() + '<button class="popupButton" id="'+ itemHTMLid + '-popout" onclick="popupItemSearchP(&quot;' + sid + '&quot;,&quot;' + document.getElementById(itemHTMLid).id + '&quot;);">product</button>' + 
				'<button class="popupButton" id="'+ itemHTMLid + '-popout" onclick="popupItemSearchS(&quot;' + sid + '&quot;,&quot;' + document.getElementById(itemHTMLid).id + '&quot;);">service</button>');
				
				var startText = $("#"+itemHTMLid+" td:nth-child(2)").html();
				$("#"+itemHTMLid+" td:nth-child(2)").addClass("ht");
				$("#"+itemHTMLid+" td:nth-child(2)").html("<input type='text' size='8' name='StartTime' id='StartTime' value='" + startText + "' /><span class='tooltip'>HH:MM:SS format only</span>");
				
				var durText = $("#"+itemHTMLid+" td:nth-child(3)").html();
				if (durText != '') {
					// if the duration is not blank, use whatever value is in it
					$("#"+itemHTMLid+" td:nth-child(3)").html("<input type='text' size='4' name='Duration' id='Duration' value='" + durText + "' />");
				} else {
					// if the duration is blank, use the sum of the previous time and duration for the time <td>
					previousRowID = "apptDetails" + (parseFloat(itemHTMLid.replace("apptDetails","")) - 1).toString();
					previousTime = $("#" + previousRowID + " td:nth-child(2)").html();
					previousDur = $("#" + previousRowID + " td:nth-child(3)").html();
					newTime = addTime(previousTime, previousDur);
					$("#"+itemHTMLid+" td:nth-child(2)").html("<input type='text' size='8' name='StartTime' id='StartTime' value='" + newTime + "' /><span class='tooltip'>HH:MM:SS format only</span>");
					
					// ...and a blank input for duration <td>
					$("#"+itemHTMLid+" td:nth-child(3)").html("<input type='text' size='4' name='Duration' id='Duration' value='" + durText + "' />");
				}
				
				var descText = $("#"+itemHTMLid+" td:nth-child(6)").html();
				$("#"+itemHTMLid+" td:nth-child(6)").html("<input type='text' size='36' name='Description' id='Description' value='" + descText + "' />");
				
				var priceText = $("#"+itemHTMLid+" td:nth-child(7)").html();
				$("#"+itemHTMLid+" td:nth-child(7)").html("<input type='text' size='7' name='Price' id='Price' value='" + priceText + "' />");
				
				var qtyText = $("#"+itemHTMLid+" td:nth-child(9)").html();
				$("#"+itemHTMLid+" td:nth-child(9)").html("<input type='text' size='2' name='Quantity' id='Quantity' value='" + qtyText + "' />");
				
				//triggerChange();
				// when a ticket detail field is changed, initiate change indicator again
				$("select#Employee").change(triggerChange);
				$("#StartTime").keyup(triggerChange);
				$("#StartTime").keyup(calcTimes(itemHTMLid));
				$("#Duration").keyup(triggerChange);
				$("#Duration").keyup(calcTimes(itemHTMLid));
				$("#Description").change(triggerChange);
				$("#Price").keyup(triggerChange);
				$("input[id^='taxCheck']").change(triggerChange);
				$("#Quantity").keyup(triggerChange);
				
				saveRowValues();
			}
		}
		
		function popupItemSearchP(ticketDetailRowID,htmlRowID)
		{
			if (ticketDetailRowID == '' || (typeof ticketDetailRowID === 'undefined')) {
				ticketDetailRowID = 'none';
			}
			window.open('products.php?popupSearch&tdrID=' + ticketDetailRowID + '&hrID=' + htmlRowID, '_blank', 'location=yes,height=600,width=900,scrollbars=yes,status=yes');
		}
		
		function popupItemSearchS(ticketDetailRowID,htmlRowID)
		{
			if (ticketDetailRowID == '' || (typeof ticketDetailRowID === 'undefined')) {
				ticketDetailRowID = 'none';
			}
			window.open('services.php?popupSearch&tdrID=' + ticketDetailRowID + '&hrID=' + htmlRowID, '_blank', 'location=yes,height=600,width=900,scrollbars=yes,status=yes');
		}

		function changeClient()
		{
			window.open('clients.php?popupSearch','_blank','location=yes,height=600,width=900,scrollbars=yes,status=yes');
		}
		
		function checkTicketForm() // JS form validation.
		{
			if ($("#DateScheduled").val() == '' || $("table.appointment-details tr:nth-child(1) td:nth-child(1)").html() == '')
			{	
				alert("You must input a value for the scheduled date for the ticket, and there must be at least one service/product in the ticket.")
				return false;
			}
			
			// this check must be last before the final 'return true; ' statment.
			else if ($("#Due").val() != 0) 
			{
				var proceed_confirm = confirm("The paid amount is unequal to the ticket total. Save anyways?");
				if (proceed_confirm == false) {
					return false;
				} else {
					return true;
				}
			}
			else {
				return true;
			}
		}

		// save dat ticket yo
		function submitTicketForm(delete_rows)  // (var delete_rows = "none") // default is to delete NO rows but can be changed by user. (the onsubmit is changed by supplying an argument to submitTicketForm() when user deletes a ticket Row. )
		{
			if (checkTicketForm() == true) // run JS form validation first 
			{
			console.log("BEGINNING TICKET SAVING FUNCTION");

			// compare original ticket status to current
			// is done in echoSaveTicketData.php

			// count total ticket detail rows by whether or not they have a value in the total column
			var c = 0;
			var detailRows = Array();
			for (z = 1; z <= 30; z++) 
			{
				if ($("#apptDetails" + z + " td:nth-child(10)").html() != '')
				{
					c++;
				}
			}
			console.log("the ticket detail row count is "+String(c));
			// clean the rows to get raw input data (instead of form elements)
			dataString = '';

			// check for rows that should be deleted (which were set to the function call arguments after removing (with JS))
			// e.g submitTicketForm(delete_rows=18421)
			console.log("Deleting rows (arguments passed): " + delete_rows)


			// MAIN
			colArray = ["Employee","StartTime","Duration","Type","ID","Description","Price","Tax","Qty","Total","TDrowID"];
			for (z = 1; z <= c; z++)
			{
				// check for form elements and disregard them if needed
				// and then add the raw data to ajax string
				for (y = 1; y <= 11; y++)
				{
					// if there's no child element
					if ($("#apptDetails" + z + " td:nth-child(" + y + ")").children().length < 1)
					{
						dataString = dataString + "&" + colArray[y-1] + "-" + $("#apptDetails" + z + " td:nth-child(11)").html() + "=" + $("#apptDetails" + z + " td:nth-child(" + y + ")").html();
						// e.g. " &Employee-14284=Michelle Monroe "
						//console.log('no child element found');
					}

					// or else there must be a child element, i.e. an input or button or select etc.
					else
					{
						// treat employee select element as special case and retrieve the currently selected one
						if (y==1)
						{	
							dataString = dataString + "&" + colArray[y-1] + "-" + $("#apptDetails" + z + " td:nth-child(11)").html() + "=" + $("#Employee option:selected").text();
						}	

						// treat prod/serv ID box as special case by retrieving only text before the <button>'s
						else if (y==5)
						{
							var thetext = $("#apptDetails" + z + " td:nth-child(5)").html();
							dataString = dataString + "&" + colArray[y-1] + "-" + $("#apptDetails" + z + " td:nth-child(11)").html() + "=" + thetext.substr(0, thetext.indexOf('<but'));
						}

						// get the checkbox status as 1 or 0, which is 8th column, as special case
						else if (y==8) 
						{
							if ($("#apptDetails" + z + " td:nth-child(" + y + ") input").is(':checked')) {
								//console.log('a tax checkbox was detected checked.');
								dataString = dataString + "&" + colArray[y-1] + "-" + $("#apptDetails" + z + " td:nth-child(11)").html() + "=" + "1";
							}
							else {
								dataString = dataString + "&" + colArray[y-1] + "-" + $("#apptDetails" + z + " td:nth-child(11)").html() + "=" + "0";
							}
						}

						// otherwise it must just be an input element
						else 
						{
							dataString = dataString + "&" + colArray[y-1] + "-" + $("#apptDetails" + z + " td:nth-child(11)").html() + "=" + $("#apptDetails" + z + " td:nth-child(" + y + ") input").val();
							// e.g. " &Employee-14284=Michelle Monroe "
							//console.log('child element found');
						}
					}
				}
			}
			
			var finalPostData = $('#appointment-detail-form').serialize() + dataString;
			
			$.ajax(
			{
				url: "ajax/echoSaveTicketData.php",
				type: 'POST',
				cache: false,
				dataType: 'html',
				data: finalPostData, // should send all POST data plus ticket detail data processed in this function above
				success: function(msg) {
					//console.log("success from AJAX call!");
					//console.log("finalPostData: " + finalPostData);
					console.log("return message (from ajax php script):" + msg);
					//alert("Hey. the success funtion was called.");

					// THE BELOW COMMAND IS THE POST-SAVING PAGE REDIRECT (REFRESH) =======================
					window.location = "tickets.php?cts";
					// NOW DONE SAVING. AYyyE. ============================================================
				}
			});
			}
		}
		
		function saveAndClose() {
			// "close and save", really..
			//alert("closing");
			$("#tsClosed").prop("checked", true);
			//alert("saving");
			submitTicketForm("none");
			//return false;
		}

		function deleteTicketDetailRow()
		{
			if (!window.itemHTMLid) { alert('No ticket item row is selected! Cannot delete a row.'); return false; }
			else 
			{
				if (confirm('Are you sure you want to delete the selected ticket details row?'))
				{

					// pass 11th <td> (the ticketdetails MySQL id) to saving function call to make sure it goes on to delete in echoSaveTicketData.php
					$("#appointment-detail-form").onSubmit = function() {alert('worked'); submitTicketForm($("#" + window.itemHTMLid + " td:nth-child(11)").html())};
					// NO document.getElementById('appointment-detail-form').setAttribute("onSubmit", submitTicketForm($("#" + window.itemHTMLid + " td:nth-child(11)").html()));



					// delete row contents.
					for (x=1; x <= 11; x++)
					{
						if (x != 8) { // skip the checkbox
							$("#" + window.itemHTMLid + " td:nth-child(" + x + ")").html('');
						}
					}

					// need to also move ticket rows up one to fill the gap.
					//console.log(window.itemHTMLid); // looks like apptDetails4 etc.
					var rowid = window.itemHTMLid;
					var digit = parseInt(rowid.replace("apptDetails",""));

					for (y=(digit+1); y <=30; y++)
					{
						//console.log(y); counts all the way to 30 from deleted row.
						for (a=1; a <= 11; a++)
						{
							$("#" + "apptDetails" + (y-1) + " td:nth-child(" + a + ")").html(
									$("#" + "apptDetails" + y + " td:nth-child(" + a + ")").html()
							);
						}
					}

					// delete window variables
					delete window.itemHTMLid;
					delete window.prevItemHTMLid;

				}
			}
		}

		function deleteTicket()
		{
			if ($("#TicketID").val() == '') {
				alert('No [saved] ticket is currently open for viewing! Cannot delete ticket.');
				return false;
			}
			else
			{
				if (confirm('Are you sure you want to delete this ticket (ID = ' + $("#TicketID").val() + ')? Pressing "OK" will delete all the ticket data, including services, products, etc.'))
				{
					$.ajax(
					{
						url: "ajax/echoDeleteTicket.php?tid=" + $("#TicketID").val(),
						type: 'GET',
						dataType: 'json',
						success: function(data) 
						{
							console.log('got a successful response from echoDeleteTicket.php');
							if (data == "11")
							{
								window.location = "tickets.php?ctd";
							}
						}
					});
				}
			}
		}
		
		$(document).ready(function() {
			// triggers to re-calculate ticket totals
			$("#Tender1").keyup(calcTotals);
			$("#Tender2").keyup(calcTotals);
			$("#Tip").keyup(calcTotals);
			$("#Change").keyup(calcTotals);
			$("tr[id^='apptDetails'] td:nth-child(8)").change(calcTotals);
			$("tr[id^='apptDetails'] td:nth-child(9)").change(calcTotals);
			$("tr[id^='apptDetails'] td:nth-child(10)").change(calcTotals);
			
			$("#Quantity").keyup(calcTotals);
			$("#Price").keyup(calcTotals);
			$("[id^='Taxable']").change(calcTotals);
			
			//float table headers
			var $table = $('table.appointment-details');
			$table.floatThead({
				useAbsolutePositioning: false,
				scrollContainer: function($table) {
					return $table.closest('#apptDetailRows');
				}
			});
			
			// save edited ticket detail row values after a change to window variables to preserve on the next openEdits() call
			$("#changeIndicator").change(saveRowValues);
		});
	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>
<body class="appt-viewer">
	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	<div id="admin_content">
		<h1>Tickets &raquo;
		
		<a class="buttonLink" id="addApptButton" href="clients.php?newAppt">
		<img class="button" src="images/addTicket.png" alt="create new ticket" title="create new ticket" height="32px" width="32px" /></a>
		
		<a class="buttonLink" id="deleteTicketRowButton" href="#" onclick="deleteTicketDetailRow();">
		<img class="button" src="images/deleteRow.png" alt="delete selected row" title="delete selected row" height="32px" width="32px" /></a>
		
		<?php
		if (!isset($_GET['addAppt'])) {
		?>
		<a class="buttonLink" id="printTicketButton" href="tickets.php?print<?php echo ((isset($_GET['viewTicket'])) ? '&tid='.$_GET['viewTicket'] : '&tid=none'); ?>" target="_blank" onclick="return confirmPrint();">
		<img class="button" src="images/printer.png" alt="print this ticket" title="print this ticket" height="32px" width="32px" /></a>
		<?php
		}
		?>

		<a class="buttonLink" id="deleteTicketButton" href="#" onclick="deleteTicket();">
		<img class="button" src="images/deleteTicket.png" alt="delete this ticket" title="delete this ticket" height="32px" width="32px" /></a>
		
		</h1>
			<hr />
			<div id="statusBox"></div>
		<div class="appointment-box">
		<table name="appointment-general" id="appointment-general" cellpadding="2" border="0" cellspacing="3">

		<!-- RIGHT NOW THE FORM IS NOT CHECKED FOR ERRORS... THAT NEEDS TO BE FIXED. -->
		<!-- BEGIN FORM!! -->

		<form name="appointment-detail-form" id="appointment-detail-form" method="POST" action="#" onsubmit="submitTicketForm(); return false;">
		<input type="hidden" id="tid" name="tid" value="<?php
		// this is a hidden Ticket ID element just in case (replacing the $_GET var from the old method for saving the ticket i.e. tickets.php?save. AJAX is used now and the form data will be passed through ajax to a separate PHP script
		
			if (isset($_GET['viewTicket'])) {
				echo $_GET['viewTicket'];
			} elseif (isset($_SESSION['lastApptID'])) {
				echo $_SESSION['lastApptID'];
			} else {
				echo 'new';
			}
		?>" />

		<!-- original ticket status -->
		<input type="hidden" id="tickstat" name="tickstat" value="" />

		<!-- hidden element containing initial ticketdetails row IDs to check for disparity and delete later -->
		<input type="hidden" id="initialRows" name="initialRows" value="" />


		<tr><td>Ticket ID:</td><td class="ht"><input class="rogray" type="text" name="TicketID" id="TicketID" readonly="readonly" /><span class="tooltip">If you are creating a new ticket, the ID will be automatically generated when you save the ticket.</span></td>
		<td>Client ID:</td><td><input type="text" class="rogray" id="ClientID" name="ClientID" readonly="readonly" /></td></tr>
		
		<tr><td>Status:</td><td class="ht">
		<input type="radio" name="TicketStatus" value="Open" id="tsOpen" />Open 
		<input type="radio" name="TicketStatus" value="Closed" id="tsClosed" />Closed
		<input type="radio" name="TicketStatus" value="Canceled" id="tsCanceled" />Canceled
		<span class="tooltip">If you change the ticket status, product stock counts and payroll dues will automatically change.</span>
		</td>
		<td>Client Name:</td><td><input type="text" class="rogray" id="ClientName" name="ClientName" readonly="readonly" /><input type="button" id="changeClientButton" name="changeClientButton" value="change client" onclick="changeClient();" /></td></tr>
		
		<tr><td>Date/Time Created:</td><td class="ht"><input class="rogray" type="text" id="dtCreated" name="dtCreated" readonly="readonly" /><span class="tooltip">If you are creating a new ticket, the creation date and time will be automatically generated when you save the ticket.</span></td>
		<td>Created By:</td><td><input type="text" class="rogray" id="Creator" name="Creator" readonly="readonly" /></td></tr>
		
		<tr><td>Date Scheduled:</td><td class="ht"><input type="text" id="DateScheduled" name="DateScheduled" value="<?php if (isset($_GET['addAppt'])) { echo date('m-d-Y'); } ?>" /><span class="tooltip">MM-DD-YYYY format only</span></td></tr>
		</table>
		
		<!-- BEGIN TICKET DETAILS ROWS -->
		<div id="apptDetailRows">
		<table name="appointment-details" class="appointment-details" cellpadding="1" border="0" cellspacing="0">
		<thead>
		<tr><th>Employee</th><th>Start Time</th><th>Duration (mins)</th><th>Type</th><th>ID</th><th>Description</th><th>Price</th><th>Tax</th><th>Qty</th><th>Total</th><th></th></tr></thead>
		<tbody>
		<?php
		for ($x = 1; $x <= 30; $x++)
		{
			echo '<tr id="apptDetails'.$x.'" onclick="openEdits(this.id);"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><input type="checkbox" id="Taxable'.$x.'" name="Taxable'.$x.'" /></td><td></td><td></td><td>new'.$x.'</td></tr>';
		}
		?>
		</tbody>
		</table>
		</div>
		
		<!-- BEGIN TOTALS ROWS -->
		<table name="appointment-totals" id="appointment-totals" cellpadding="2" border="0" cellspacing="3">
		<tr><td>Payment type:</td><td>
		<select name="PayType1" id="PayType1">
			<option value=""></option>
			<option value="Visa">Visa</option>
			<option value="MasterCard">MasterCard</option>
			<option value="Discover">Discover</option>
			<option value="American Express">American Express</option>
			<option value="Cash">Cash</option>
			<option value="Check">Check</option>
			<option value="Gift Card">Gift Card</option>
			<option value="Spa Finder">Spa Finder</option>
		</select>
		</td>
		<td>Subtotal:</td><td>$<input class="rogray" type="text" name="Subtotal" id="Subtotal" readonly="readonly" /></td>
		<td>Comment:</td><td rowspan="3"><textarea name="TicketComment" id="TicketComment" rows="4" cols="30"></textarea></td>
		</tr>
		
		<tr><td>Comment:</td><td><input type="text" name="PayData1" id="PayData1" /></td>
		<td>Tip:</td><td>$<input type="text" name="Tip" id="Tip" /></td></tr>
		
		<tr><td>Tender:</td><td>$<input type="text" name="Tender1" id="Tender1" /></td>
		<td>Tip For:</td><td><select name="TipFor" id="TipFor" />
			<?php
				$stmt = $Database->prepare("SELECT `EmployeeID`,`FirstName`,`LastName` FROM `employees` WHERE `Active` = '1' ORDER BY `FirstName` ASC");
				if ($stmt->execute())
				{
					echo '<option value=""></option><option value="(none)">(none)</option>';
					$result = $stmt->get_result();
					$total_rows = $result->num_rows;
					for ($x = 0; $x <= ($total_rows - 1); $x++)
					{
						$result->data_seek($x); //gets individual row
						$row = $result->fetch_array(); // $res->fetch_assoc() fetches the key names instead of numbers
						echo '<option id="empTipSel'.$row[0].'" value="'.$row[1].' '.$row[2].'">'.$row[1].' '.$row[2].'</option>';
					}
				}
			?>
		</select></td></tr>
		
		<tr><td>-------</td><td></td><td>Tax:</td><td>$<input class="rogray" type="text" name="Tax" id="Tax" readonly="readonly" /></td></tr>
		
		<tr><td>Payment type 2:</td><td>
			<select name="PayType2" id="PayType2">
			<option value=""></option>
			<option value="Visa">Visa</option>
			<option value="MasterCard">MasterCard</option>
			<option value="Discover">Discover</option>
			<option value="American Express">American Express</option>
			<option value="Cash">Cash</option>
			<option value="Check">Check</option>
			<option value="Gift Card">Gift Card</option>
			<option value="Spa Finder">Spa Finder</option>
		</select>
		</td>
		<td>Total:</td><td>$<input class="rogray" type="text" name="Total" id="Total" readonly="readonly" /></td>
		</tr>
		
		<tr><td>Comment 2:</td><td><input type="text" name="PayData2" id="PayData2" /></td>
		<td>Paid:</td><td>$<input class="rogray" type="text" name="Paid" id="Paid" readonly="readonly" /></td>
		<!-- SUBMIT IS HERE!! -->
		<td></td>
		<td rowspan="8"><input type="button" value="save &amp; close" id="saveAndCloseButton" onclick="saveAndClose();" /> <input type="submit" value="save" id="apptDetailFormSubmit" /></td>
		</tr>
		
		<tr><td>Tender 2:</td><td>$<input type="text" name="Tender2" id="Tender2" /></td>
		<td>Due:</td><td>$<input class="rogray" type="text" name="Due" id="Due" readonly="readonly" /></td>
		</tr>
		
		<tr><td>Change type:</td><td>
		<select name="ChangeType" id="ChangeType">
			<option value=""></option>
			<option value="Cash">Cash</option>	
		</select>	
		</td>
		<td>Change given:</td><td><input type="text" name="Change" id="Change" /></td>
		</tr>
		
		</table>
		<input type="hidden" id="changeIndicator" value="" />
		</form>
		</table>
		</div>
	</div>
	
	<?php
	// save ticket data if form was submitted
	// done with JS
	
	// finally pull up appt data if one was selected or viewed before in session
	// not if creating a new appointment for a new client
	if ((isset($_GET['viewTicket']) || isset($_SESSION['lastApptID']) && $_SESSION['lastApptID'] != '') && !isset($_GET['addAppt']))
	{
		$_SESSION['lastApptID'] = (isset($_GET['viewTicket']))?$_GET['viewTicket']:$_SESSION['lastApptID'];
		$ticketID = (isset($_GET['viewTicket']))?$_GET['viewTicket']:$_SESSION['lastApptID'];
		?>
		<script type="text/javascript">
			displayApptData(<?php echo $ticketID; ?>);
		</script>
		<?php
	}
	elseif (isset($_GET['addAppt']) && isset($_GET['client']))
	{
		$clientID = $_GET['client'];
		$getclient = "SELECT `ClientID`,`FirstName`,`LastName` FROM `clients` WHERE `ClientID` = ?";
		$stmt = $Database->prepare($getclient);
		$stmt->bind_param("s", $clientID);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
			$result->data_seek(0);
			$row = $result->fetch_array();
			?>
				<script type="text/javascript">
					$("#ClientID").val(<?php echo json_encode($row[0]); ?>);
					$("#ClientName").val(<?php echo json_encode($row[1].' '.$row[2]); ?>);
					$("#tsOpen").prop("checked",true);
					$("#Creator").val(<?php echo json_encode($_SESSION['uName']); ?>);
				</script>
			<?php
			$result->close();
		}
		else
		{
			echo 'Failed to execute MySQL query. Client Name/ID not retrieved.';
		}
		
		if (isset($_GET['employee'])) 
		{
			$employee = $_GET['employee'];
			?>
				<script type="text/javascript">
					$("#apptDetails1 td:nth-child(1)").html(<?php echo json_encode($employee); ?>);
				</script>
			<?php
		}

		if (isset($_GET['day']))
		{
			$day = $_GET['day'];
			?>
				<script type="text/javascript">
					$("#DateScheduled").val(<?php echo json_encode($day); ?>);
				</script>
			<?php
		}
		
		if (isset($_GET['time'])) 
		{
			$time = $_GET['time'];
			$mins = $time - floor($time);
			if ($mins == 0) {
				$caboose = ":00:00";
			} elseif ($mins == 0.25) {
				$caboose = ":15:00";
			} elseif ($mins == 0.5) {
				$caboose = ":30:00";
			} elseif ($mins == 0.75) {
				$caboose = ":45:00";
			}
			
			if (floor($time) < 10) {
				$hour = "0".floor($time);
			} else {
				$hour = floor($time);
			}
			?>
				<script type="text/javascript">
					$("#apptDetails1 td:nth-child(2)").html(<?php echo json_encode($hour.$caboose); ?>);
				</script>
			<?php
		}
	}

	// finally check if saved ticket notification is needed.
	if (isset($_GET['cts']))
	{
		?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Saved ticket data successfully.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 2500);
			</script>
		<?php
		unset($_GET['cts']); // doesn't really "not happen again" if refresh is pressed..
	}

	// check if ticket was deleted
	if (isset($_GET['ctd']))
	{
		unset($_SESSION['lastApptID']);
		?>
			<script type="text/javascript">
			document.getElementById('statusBox').innerHTML = "Deleted ticket data successfully.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 2500);
			</script>
		<?php
	}
	?>

<script type="text/javascript">
function confirmPrint()
{
	<?php
	if (isset($_GET['viewTicket']) || (isset($_SESSION['lastApptID']) && $_SESSION['lastApptID'] != ''))
	{
		if (!isset($_GET['viewTicket']))
		{
			?>
				document.getElementById('printTicketButton').href = "tickets.php?print&tid=<?php echo $_SESSION['lastApptID']; ?>";
				$("#printTicketButton").click();
			<?php
		}
	?>
		return false;
	<?php
	} else {
	?>
			alert('Cannot print ticket data. No saved ticket has been opened for viewing.');
			return false;
	<?php
	}
	?>
}
</script>
</body>
</html>
<?php
}
// print a ticket (no other content displayed up to now)
elseif (isset($_GET['print']))
{
	echo '<!DOCTYPE html><html><head>
			<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>Quac - Print Ticket</title>
			<link rel="stylesheet" type="text/css" href="css/print.css" /></head><body>
			<img class="ontop" src="images/paid_done.png" />';
	if ((isset($_GET['tid']) && $_GET['tid'] == 'none') || !isset($_GET['tid']))
	{
		echo 'No ticket id selected. Redirecting to default tickets page in 3 seconds...';
		?>
			<script type="text/javascript">
				setTimeout(function(){
					window.location = "tickets.php";
				}, 3000);
			</script>
		<?php
	}
	else
	{
		$tid = $_GET['tid'];
		$stmt = $Database->prepare("SELECT * FROM `tickets` WHERE `TicketID` = ?");
		$stmt->bind_param("s", $tid);
		if ($stmt->execute())
		{
			$res = $stmt->get_result();
			//$total_rows = $res->num_rows;
			$res->data_seek(0); //gets individual row
			$output['ticket'] = $res->fetch_array(); // $res->fetch_assoc() fetches the key names instead of numbers
			$res->close();
			
			$stmt2 = $Database->prepare("SELECT * from `ticketdetails` WHERE `TicketID` = ?");
			$stmt2->bind_param("s", $tid);
			if ($stmt2->execute())
			{
				$res2 = $stmt2->get_result();
				for ($n = 0; $n <= ($res2->num_rows - 1); $n++) 
				{
					$res2->data_seek($n);
					$output['details'][$n] = $res2->fetch_array();
				}
				$res2->close();
			}
			else $output .= 'Failed to retrieve ticket detail rows.';
			
		}
		else $output .= 'Failed to retrieve individual appointment data for ID:'.$newid;
		
		//var_dump($output);
		// Display print view for ticket
		
		$stmt = $Database->prepare("SELECT * FROM `settings` WHERE `SettingID` = 1");
		if ($stmt->execute())
		{
			$res = $stmt->get_result();
			$businessData = $res->fetch_array();
			$busName = $businessData[1];
			$busAdd = $businessData[2];
			$busPho = $businessData[3];
			$busFax = $businessData[4];
			$busEma = $businessData[5];
			$busWeb = $businessData[6];
			$logoName = $businessData[7];
			$logoAct = $businessData[8];
		}
		else
		{
			echo 'Failed to load business logo image name.';
		}
		
		echo '<table class="print_header">
				<tbody>
				<tr><td>';
					if ($logoAct == "1") {
						echo '<img src="images/uploaded_logo/'.$logoName.'" />';
					} else {
						echo $busName;
					}
				echo '</td>
		
					<td>
						<span class="invt">Invoice</span><br /><br />
						<table class="date_and_num" cellpadding="4" cellspacing="2" border="2">
						<tbody>
						<tr><th>Date</th><th>Invoice #</th></tr>
						<tr><td>'.convertdate($output['ticket'][4],"touser").'</td><td>'.$output['ticket'][0].'</td></tr>
						</tbody>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
			
			<div id="billing_box"><b>'.$busAdd.'</b><br /><br />
			&nbsp;&nbsp;&nbsp;&nbsp;Bill To:<br />';
			
			$stmt = $Database->prepare("SELECT `FirstName`,`LastName`,`Address`,`City`,`State`,`Zip` FROM `clients` WHERE `ClientID` = ".$output['ticket'][10]);
			
			if ($stmt->execute())
			{
				$res = $stmt->get_result();
				$cliData = $res->fetch_array();
				echo '<span class="clientInfo">'.$cliData[0]." ".$cliData[1]."<br />".$cliData[2]."<br />".$cliData[3].", ".$cliData[4].", ".$cliData[5].'</span>';
			}
			else
			{
				echo 'Failed to load client information data.';
			}
			
			echo '
			<br /><br /><br />
			</div>
			
			<table class="print_ticket_details" cellpadding="2" cellspacing="2" border="2">
			<thead>
			<tr><th>Quantity</th><th>Description</th><th>Rate</th><th>Amount</th></tr>
			</thead>
			<tbody>';
			
			foreach ($output['details'] as $row)
			{
				echo '<tr><td>'.$row[17].'</td><td>'.$row[15].'</td><td>'.$row[16].'</td><td>'.$row[18];
				// taxable is $row[24] (0 or 1)
			}
			
			echo '
			<tr><td></td><td class="righttd"><b>Tax</b></td><td>7.00%</td><td>'.$output['ticket'][14].'</td></tr>
			<tr><td></td><td><span class="boldred">*REFUND POLICY: All sales are final and non-refundable.</span></td><td colspan="2"></td></tr>
			<tr><td></td><td></td><td><b>Total</b></td><td><b>'.$output['ticket'][24].'</b></td></tr>
			</tbody>
			</table>
			
			<center><b>
			Phone: '.$busPho.', Fax: '.$busFax.', E-mail: '.$busEma.'<br />Website: '.$busWeb.
			'</b></center>';
			
	}	
	?>
		<script type="text/javascript">
			window.print();
		</script>
	<?php
	echo '</body></html>';
}
//var_dump($_SESSION);
?>
