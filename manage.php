<?php 
/*
	/admin/manage.php
	Calendar application. Handles display of database appointments and links to create/edit new ones
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
	
		function highlightTicket(linkID)
		{
			if (window.checkHL) { $(".highlightGray").removeClass("highlightGray"); }
				var ticketID = linkID.replace("ticket-link-","");
				var targetClass = 'boxTicketId-' + ticketID;
				$("#" + linkID).addClass("highlightGray");
				$("." + targetClass).addClass("highlightGray");
				window.checkHL = true;
		}
		
		function highlightBox(tdID)
		{
			if (window.checkTD) { $(".highlightGray2").removeClass("highlightGray2"); }
				tdID = tdID.replace(".","\\.");
				$("#"+tdID).addClass("highlightGray2");
				window.checkTD = true;
		}

		function checkTime(i) {
		    if (i < 10) {
		        i = "0" + i;
		    }
		    return i;
		}

		function startTime() {
		    var today = new Date();
		    var h = today.getHours();
		    var m = today.getMinutes();
		    var s = today.getSeconds();
		    // add a zero in front of numbers<10
		    m = checkTime(m);
		    s = checkTime(s);
		    document.getElementById('time').innerHTML = h + ":" + m + ":" + s;
		    t = setTimeout(function () {
		        startTime()
		    }, 500);
		}
		
		$(document).ready(function() {
			//float table headers
				var $table = $('table.ind-day-table');
				$table.floatThead({
					useAbsolutePositioning: false,
					scrollContainer: function($table) {
						return $table.closest('#client-list-box');
					}
				});
				
			// make hover color for whole appointments (multiple service boxes) in day view
			$(".ind-service-box").hover(function() {
				var ticketClass = this.classList[1];
				$("."+ticketClass).css('background-color','#1A1A1A');
				window.apptHoverCheck = true;
			}, function() {
				var ticketClass = this.classList[1];
				$("."+ticketClass).css('background-color',''); //#910D1A
				window.apptHoverCheck = false;
			});
			
			// create appointment on doubleclick of a blank <td> (only if not hovering over an appt)
			$(".ind-day-table tbody tr td").dblclick(function() {
				if (!window.apptHoverCheck) {

					var printed_date = $("#printedDate").html();
					var boxArray = this.id.split("-");
					var _time = boxArray[1];
					
					if (this.id.substr(this.id.length - 1) != '0')
					{
						var _emp = uEN[this.id.substr(this.id.length - 1) - 1];
						window.location = "clients.php?newAppt&time=" + _time + "&employee=" + _emp + "&date=" + printed_date;
					}
					else if (this.id.substr(this.id.length - 1) == '0')
					{
						window.location = "clients.php?newAppt&time=" + _time + "&date=" + printed_date;
					}
				}
			});

			// display live time in browser span id=time
			startTime();

			// set ind-day-box to max height
			// LEAVING FIXED FOR NOW (by css). Table goes all the way to bottom of page...
			//var heg = $(document).height();
			//var heg_factor = heg * 0.6;
			//$('#ind-day-box').css("height", heg_factor);
		});
	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>

<body class="manage">

	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	
	<div id="admin_content">
	
		<h1>Calendar &raquo;
		<?php
			// set calendar link to display the relevant month
			$now = time();
			if (isset($_GET['display']) && $_GET['display'] == 'day' && isset($_GET['date'])) {
				$calLink = 'manage.php?display=month&month='.date('m',strtotime($_GET['date'])).'&year='.date('Y',strtotime($_GET['date']));
			} else {
				$calLink = 'manage.php?display=month&month='.date('m',$now).'&year='.date('Y',$now);
			}
		?>
		<a class="buttonLink" href="<?php echo $calLink; ?>">
			<img class="button" src="images/Calendar.png" alt="view full month calendar" title="view full month calendar" height="32px" width="32px" /></a>
			
		<a class="buttonLink" href="manage.php?display=day">
			<img class="button" src="images/calendarDay.png" alt="view full month calendar" title="view single day" height="32px" width="32px" /></a>	
		</h1>
			<hr />
			<?php
			if (((!isset($_GET['display']) && !isset($_SESSION['lastMonthViewMonth']) && !isset($_SESSION['lastMonthViewYear'])) || (isset($_GET['display']) && $_GET['display'] == "day")))
			{
				// ========================================
				//  DAY VIEW
				// ========================================
				$now_for_line = date("H:i",time());
				$time_parts = explode(":",$now_for_line);
				//var_dump($time_parts);
				$min_frac = $time_parts[1] / 60.0;
				$now_total = $min_frac + $time_parts[0];
				//echo $now_total;  // <--------------- this will be used for horizontal line. e.g. 13:30 = 13.5
				//$now_total = 9.3; // fake number

				$now = time();
				if (!isset($_GET['date'])) {
					if (isset($_SESSION['lastDayViewDate'])) {
						$thisdate = $_SESSION['lastDayViewDate'];
					} else {
						$thisdate = date('Y-m-d',$now);
					}
				} else {
					$thisdate = $_GET['date'];
				}
				$thisdateforuser = date('m-d-Y', strtotime("$thisdate"));
				$mysqlDate = date('Y-m-d', strtotime("$thisdate"));
				$tomorrow = date('Y-m-d', strtotime( "$thisdate + 1 day" ));
				$yesterday = date('Y-m-d', strtotime( "$thisdate - 1 day" ));
				
				// save last viewed date to session for returning to this date later.
				$_SESSION['lastDayViewDate'] = $thisdate;
				
				if (isset($_SESSION['lastMonthViewMonth']) && isset($_SESSION['lastMonthViewYear'])) {
					unset($_SESSION['lastMonthViewMonth']);
					unset($_SESSION['lastMonthViewYear']);
				}

				$today_string = date("Y-m-d",time());
				echo '<h4>[<a href="manage.php?display=day&date='.$today_string.'">Go to today</a>] | ';
				echo '<a class="fbb" href="manage.php?display=day&date='.$yesterday.'">&laquo;</a> <span id="printedDate">'.$thisdateforuser.'</span> <a class="fbb" href="manage.php?display=day&date='.$tomorrow.'">&raquo;</a> | <span id="time"></span>';

				echo '</h4>';

				echo '<div id="ind-day-box">
				<table class="ind-day-table" border="0" cellpadding="0" cellspacing="0">';
				
				// ######    select all ticket detail elements for this day, that take time (i.e. have a duration)
				$getApptsForDay = "SELECT * FROM `ticketdetails` WHERE `StartDate` = ? AND `Duration` != ?";
				$dur = "0";
				$stmt = $Database->prepare($getApptsForDay);
				$stmt->bind_param('ss',$mysqlDate,$dur);
				if ($stmt->execute())
				{	
					$apptDetailRows = array();
					$activeEmployees = array();
					$employeeNames = array();
					$result = $stmt->get_result();
					$num_rows = $result->num_rows;
					
					$appts_present = false;
					if ($num_rows > 0) 
					{ 
						$appts_present = true;
						
						for ($x=0; $x <= ($num_rows - 1); $x++)
						{
							$result->data_seek($x);
							$row = $result->fetch_array();
							$apptDetailRows[$x] = $row;
							$activeEmployees[$x][0] = $row[14];
							$activeEmployees[$x][1] = $row[13];
							if ($row[14] != '(none)') { array_push($employeeNames,$row[14]); }
						}
						
						$stmt->close();
						/*echo 'apptDetailRows[]<br />';
						var_dump($apptDetailRows);
						
						echo 'activeEmployees[]<br />';
						var_dump($activeEmployees);*/
						
						sort($employeeNames); // alphabetize columns by employee name
						$uniqueEmployeeNames = array_values(array_unique($employeeNames));
						?>
						<script type="text/javascript">
							var uEN = <?php echo json_encode($uniqueEmployeeNames); ?>;
						</script>
						<?php
						//$uniqueActiveEmployees = array_keys(array_count_values($activeEmployees)); 
						/* echo 'uniqueEmployeeNames[]<br />';
						var_dump($uniqueEmployeeNames); */
					}
				}
				else
				{
					echo 'MySQL query failed.';
				}
				
				echo '<thead><tr><th>Time</th>'; // add th
				if ($appts_present == true)
				{
					$ue = 0;
					foreach ($uniqueEmployeeNames as $name)
					{
						echo '<th>'.$name.'</th>'; $ue++;
					}
				}
				else
				{
					echo '<th></th>';
				}
				echo '</tr></thead><tbody>';
				
				$prevTimeStart = array();
				$prevTimeEnd = array();
				?>
					<script type="text/javascript">
					var prevBoxId = new Array();
					</script>
				<?php
				
				$no_appts_display_check = false;
				for ($y=8;$y<=20.75;$y += 0.25)
				{
					$bo = "";
					if ($y - floor($y) == 0) { $mins = "00"; $bo = "on";}
					elseif ($y - floor($y) == 0.25) { $mins = "15"; }
					elseif ($y - floor($y) == 0.5) { $mins = "30"; }
					elseif ($y - floor($y) == 0.75) { $mins = "45"; }

					if (isset($_GET['date']) && ($_GET['date'] == date("Y-m-d",time()))) {
						//if ($now_total > $y && ($now_total <= ($y + 0.25))) {
						if ($now_total > $y) {
							// put down arrow on most recent time block
							//$da = '&#8595;';
							$da = '&#8659';
						} else { $da = ''; }

						if ($now_total > $y) { 
							// change BG color of past times
							$line_o = "bgLine"; 
						} else {$line_o = "";}
					} else {
						$line_o = '';
						$da = '';
					}
					
					// write td and first tr with time of row.
					echo '<tr id="timeRow-'.$y.'" class="'.$line_o.'"><td '.(($bo == "on")?'style="font-weight:bold;"':'').'>'.$da.' '.(($y > 12.75)?(floor($y) - 12):(floor($y))).':'.$mins.'</td>';
					
					if ($appts_present == true)
					{
						for ($x=1; $x<=$ue; $x++)
						{
							$employeeKey = $x-1;
							$employeeName = $uniqueEmployeeNames[$employeeKey];
							echo '<td id="box-'.$y.'-'.$x.'" onclick="highlightBox(this.id); return false;">';
							for ($d=0; ($d <= count($apptDetailRows) - 1); $d++)
							{
								// if there is a service that starts within this timeframe <td> which has a duration greater than 0 minutes
								//if ((float)timeToDec($apptDetailRows[$d][11]) - timeToDec($apptDetailRows[$d][10]) > 0)
								if ((float)$apptDetailRows[$d][21] > 0) 
								{
									// if the service is for this employee column and time row
									if ((timeToDec($apptDetailRows[$d][10]) >= $y) && (timeToDec($apptDetailRows[$d][10]) < ($y + 0.25)) && ($apptDetailRows[$d][14] == $employeeName)) 
									{
										/*?>
											<script type="text/javascript" language="javascript">
												console.log(<?php var_dump($apptDetailRows[$d]); ?>);
											</script>
										<?php */

										// first get ticket status for relevant block to change color if closed
										$the_tid = $apptDetailRows[$d][1];
										$stmt = $Database->prepare("SELECT `TicketStatus` FROM `tickets` WHERE `TicketID` = '$the_tid'");
										if ($stmt->execute())
										{
											$result = $stmt->get_result();
											$row = $result->fetch_array();
											$tickstatus = $row[0];
											$stmt->close();
											//echo $tickstatus;

											if ($tickstatus == "Closed") { $cl = "closedGrey"; } else {$cl = '';}
										}
										else
										{
											echo 'SQL query failed. Contact support.';
										}

										// next get client phone number
										$cli_id = $apptDetailRows[$d][2];
										$stmt = $Database->prepare("SELECT `PrimaryPhone` FROM `clients` WHERE `ClientID` = '$cli_id'");
										if ($stmt->execute())
										{
											$result = $stmt->get_result();
											$row = $result->fetch_array();
											$ph = $row[0];
											$stmt->close();
										}
										else
										{
											echo 'SQL query failed. Contact support.';
										}

										// set link and divs for service box. set height based on duration of service
										// and set displacement from quarter-hours if start-time is such
										echo '<a id="ticket-link-'.$apptDetailRows[$d][1].'" class="serviceBoxLink" onclick="highlightTicket(this.id); return false;" ondblclick="window.location=&quot;tickets.php?viewTicket='.$apptDetailRows[$d][1].'&quot;;" 
											title="'.$apptDetailRows[$d][3].' '.$apptDetailRows[$d][4].' - '.$apptDetailRows[$d][15].' - '.$apptDetailRows[$d][21].' mins - $'.$apptDetailRows[$d][16].'" 
											alt="'.$apptDetailRows[$d][3].' '.$apptDetailRows[$d][4].' - '.$apptDetailRows[$d][15].' - '.$apptDetailRows[$d][21].' mins - $'.$apptDetailRows[$d][16].'">
											
										<div class="ind-service-box-wrapper">
										<div id="serviceBox-'.$apptDetailRows[$d][0].'" class="ind-service-box boxTicketId-'.$apptDetailRows[$d][1].' '.$cl.'" style="height: '.((((float)(timeToDec($apptDetailRows[$d][11]) - timeToDec($apptDetailRows[$d][10])) * 60) / 0.75) - 4).'px;
										position: relative;
										top: '.(((float)(timeToDec($apptDetailRows[$d][10]) - $y) * 60) / 0.75).'px;">';
										
										// echo service box contents (hiding the start/end time)
										//echo $apptDetailRows[$d][10].' to '.$apptDetailRows[$d][11].' - '.
										echo $apptDetailRows[$d][3].' '.$apptDetailRows[$d][4].' [ '.$ph.' ] : '.$apptDetailRows[$d][15];
										
										echo '</div></div></a>';
										
										// if there is a time-overlap for the same column, shrink the width of the services by 2x
										if (isset($prevTimeStart[$employeeKey]) && 
										((($apptDetailRows[$d][10] == $prevTimeStart[$employeeKey]) || ((isset($prevTimeEnd[$employeeKey])) && timeToDec($apptDetailRows[$d][10]) < timeToDec($prevTimeEnd[$employeeKey])))))
										{
											?>
											<script type="text/javascript">
											// set width to one half for the box, the wrapper, and the link
											// also move the box to the right by the same distance as width
											// also flag the box as halfed using a class
											// also half the width of the previous box that caused the overlap (only if the previous box was not already halfed)
											$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").width(
												$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").width() / 2);
											$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").addClass("halfed");
											
											if ($("#"+prevBoxId[<?php echo $employeeKey; ?>]).hasClass('halfed'))
											{
												var muf = 'fin';
											}
											else
											{
												$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").css(
												'left',$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").width() + 'px');
												
												$("#"+prevBoxId[<?php echo $employeeKey; ?>]).width($("#"+prevBoxId[<?php echo $employeeKey; ?>]).width() / 2);
											}
											
											$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").parent().width(
												$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").parent().width() / 2);
											
											$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").parent().parent().width(
												$("#serviceBox-<?php echo $apptDetailRows[$d][0]; ?>").parent().parent().width() / 2);
										
											</script>
											<?php
										}
										$prevTimeStart[$employeeKey] = $apptDetailRows[$d][10];
										$prevTimeEnd[$employeeKey] = $apptDetailRows[$d][11];
										
										?>
										<script type="text/javascript">
										prevBoxId[<?php echo $employeeKey; ?>] = "serviceBox-<?php echo $apptDetailRows[$d][0]; ?>";
										 //console.log(prevBoxId);
										</script>
										<?php
									}
									else {
										continue;
									}
								}
							}
							echo '</td>';
						}
					}
					else
					{
						if ($no_appts_display_check == false) {
							echo '<td id="box-'.$y.'-0" onclick="highlightBox(this.id); return false;">No service appointments were found for this day.</td>';
							$no_appts_display_check = true;
						} else {
							echo '<td id="box-'.$y.'-0" onclick="highlightBox(this.id); return false;"></td>';
						}
					}
					echo '</tr>';
				}
				echo '</tbody></table>
						</div>';
				//var_dump($apptDetailRows);
			}
			elseif ((isset($_GET['display']) && $_GET['display'] == "month") || (isset($_SESSION['lastMonthViewMonth']) && isset($_SESSION['lastMonthViewYear'])))
			{
				// CALENDAR MONTH VIEW
				
					$now = time();

					if ((isset($_GET['month']) && isset($_GET['year'])) || (isset($_SESSION['lastMonthViewMonth']) && isset($_SESSION['lastMonthViewYear'])))
					{
						if (isset($_GET['month']) && isset($_GET['year'])) {
							$gMonth = $_GET['month'];
							$gYear = $_GET['year'];
						} elseif (isset($_SESSION['lastMonthViewMonth']) && isset($_SESSION['lastMonthViewYear'])) {
							$gMonth = $_SESSION['lastMonthViewMonth'];
							$gYear = $_SESSION['lastMonthViewYear'];
						}
					}
					else
					{
						$gMonth = date('n', $now);
						$gYear = date('Y', $now);
					}
					
					//save variables for later viewing
					$_SESSION['lastMonthViewMonth'] = $gMonth;
					$_SESSION['lastMonthViewYear'] = $gYear;
					if (isset($_SESSION['lastDayViewDate'])) {
						unset($_SESSION['lastDayViewDate']);
					}
					
					if ($gMonth == 1) {$gMonthBack = 12; $gYearBack = ($gYear - 1);}
					else {$gMonthBack = ($gMonth - 1); $gYearBack = $gYear;}

					if ($gMonth == 12) {$gMonthForward = 1; $gYearForward = ($gYear + 1);}
					else {$gMonthForward = ($gMonth + 1); $gYearForward = $gYear;}
					
					$prev_link = '<a class="fbb" href="manage.php?display=month&month='.$gMonthBack.'&year='.$gYearBack.'">&laquo;</a>';
					$next_link = '<a class="fbb" href="manage.php?display=month&month='.$gMonthForward.'&year='.$gYearForward.'">&raquo;</a>';

				if ((isset($_GET['month']) && isset($_GET['year']) && ($_GET['month'] == date('n',$now)) && ($_GET['year'] == date('Y',$now))) || (!isset($_GET['month']) && !isset($_GET['year'])))
				{
					$today = date('j',$now);
				}
				else $today = NULL;
				
				echo '<div class="month-at-bottom">'.date( 'F', mktime(0, 0, 0, $gMonth) ).' '.$gYear.'</div>';
				
				
				$stmt = $Database->prepare("SELECT * FROM tickets WHERE MONTH(`DateScheduled`) = ? AND YEAR(`DateScheduled`) = ? AND `TimeScheduled` != '00:00:00' ORDER BY `TimeScheduled`");
				
				$stmt->bind_param("ss", $gMonth, $gYear);
				if ($stmt->execute())
				{
					$result = $stmt->get_result();
					echo '<div id="statusBox"></div>';
					
					$today_string = date("Y-m-d",time());
					echo '<h4>[<a href="manage.php?display=day&date='.$today_string.'">Go to today</a>] | ';	
					
					if ($result->num_rows == 0)
					{
						// no appointments this month
						echo 'No appointments found for this month.</h4></div>';
					}
					else
					{
						// there are appointments for this month
						echo 'Found '.$result->num_rows.' appointment(s) for this month.</h4></div>';
						
						$daysArray = array();
						
						for ($i = 1; $i <= $result->num_rows; $i++)
						{
							$row = $result->fetch_row();
							
							// day that the appt. is scheduled
							$atoms = explode('-',$row[4]);
							$year = $atoms[0];
							$month = $atoms[1];
							$day = $atoms[2];
							
							$time = timetouser($row[5]);
							//$endtime = timetouser($row[12]);
							$endtime = timetouser('01:00:00'); // default endtime for now
							if ($day[0] == "0") $day = str_replace("0","",$day);
							
							/* specify color of box for therapist
							if ($row[4] == "1") { $class = 'molly'; } 
							elseif ($row[4] == "2") { $class = 'ka'; }
							elseif ($row[4] == "3") { $class = 'linda'; }
							elseif ($row[4] == "4") { $class = 'other'; } */
							$class = 'someClass'; // default class for now
							
							/* get first name of therapist only
							$ther_name = explode(" ", $row[3]);
							$ther_name = $ther_name[0]; */
							$ther_name = "some therapist"; // default ther name for now
							
							// get name of client
							$cli_name = $row[11]." ".$row[12];
							
							$service = "Temp service"; // temp for now
							
							if (array_key_exists($day, $daysArray) && $daysArray[$day] != '')
							{
								// if day already has appointments, add another after it.
								// only if it has services (if starttime != 00:00:00 or null)
								if ($time != "00:00:00" && $time != null) {
								$daysArray[$day][1] = $daysArray[$day][1].'<a title="'.$ther_name.' with client '.$cli_name.'. Service: '.$service.'" alt="'.$ther_name.' with client '.$cli_name.'. Service: '.$service.'" href="tickets.php?viewTicket='.$row[0].'"><div class="appt '.$class.'">
								<i><u>'.$time.'</u></i> - <b>'.$cli_name.'</b>'.
								'</div></a>';
								}
							}
							else
							{
								// if day has no appts listed (yet), add the appointment
								// only if it has services (if starttime != 00:00:00 or null)
								if ($time != "00:00:00" && $time != null) {
								$daysArray[$day] = array(NULL,'<a class="norm" href="manage.php?display=day&date='.$year.'-'.$month.'-'.$day.'" title="view appointments for '.convertdate($row[4],"touser").'" alt="view appointments for '.convertdate($row[4],"touser").'">'.$day.'</a><br /><br /><a title="'.$ther_name.' with client '.$cli_name.'. Service: '.$service.'" alt="'.$ther_name.' with client '.$cli_name.'. Service: '.$service.'" href="tickets.php?viewTicket='.$row[0].'"><div class="appt '.$class.'">
								<i><u>'.$time.'</u></i> - <b>'.$cli_name.'</b>'.
								'</div></a>');
								}
							}
						}
					}
					$stmt->close();
				}
				else
				{
					echo 'query failed.';
				}
				
				$daysArray[$today][0] = 'today';
				if (!array_key_exists(1, $daysArray[$today])) { $daysArray[$today][1] = '<a class="norm"  href="manage.php?display=day&date='.$gYear.'-'.$gMonth.'-'.$today.'" title="view appointments for '.$gMonth."-".$today."-".$gYear.'" alt="view appointments for '.$gMonth."-".$today."-".$gYear.'">'.$today.'</a>';  }
				
				//echo "<br /><br />";
				//var_dump($daysArray);
				
				for ($x = 1; $x < 32; $x++)
				{
					if (array_key_exists($x, $daysArray))
					{
						continue;
					}
					else
					{
						$daysArray[$x] = array(NULL,'<a class="norm" href="manage.php?display=day&date='.$gYear.'-'.$gMonth.'-'.$x.'" title="view appointments for '.$gMonth."-".$x."-".$gYear.'" alt="view appointments for '.$gMonth."-".$x."-".$gYear.'">'.$x.'</a>');
					}
				}
				

				echo generate_calendar(
					$gYear, // year
					$gMonth, // month
					$daysArray, // days array with links and appointments
					4, // day name length ( >3 displays the whole name)
					0, // first day
					$prev_link, // previous month link
					$next_link // next month link
					
				);
			}
			?>
	

	</div>


</body>
</html>
