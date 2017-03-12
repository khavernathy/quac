<?php 
/*
	reports.php
	Made by Douglas Franz, freelance PHP/MySQL/HTML/CSS/JS/jQuery-ist.
*/
if (!isset($_GET['reportBy']) || !isset($_GET['timeFrame'])) {
	header("location:reports.php?reportBy=employee&timeFrame=allTime");
}
session_start();
date_default_timezone_set('America/New_York');
ob_implicit_flush(true);
include('models/auth.php');
include('includes/calendar.php');
include('includes/database.php');
include('includes/datetime_functions.php');
include('includes/pull_report_data_functions.php');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Quac Admin Area</title>
	<script type="text/javascript" language="javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

	<script type="text/javascript" language="javascript">
		function loadChart(rB,tF) 
		{
					<?php if (isset($_GET['reportBy']) && isset($_GET['timeFrame'])) {
						$rB = $_GET['reportBy']; $tF = $_GET['timeFrame'];

					} else {
						$rB ='employee'; $tF = 'allTime';
					}
					?>
					var mysql_data=<?php echo get_data($rB,$tF); ?>; // pulling from included php function file.
							//console.log(mysql_data);
				    // Load the Visualization API and the corechart package.
				      google.charts.load('current', {'packages':['corechart']});

				      // Set a callback to run when the Google Visualization API is loaded.
				      google.charts.setOnLoadCallback(drawChart);

				      // Callback that creates and populates a data table,
				      // instantiates the pie chart, passes in the data and
				      // draws it.
				      function drawChart() {

				        // Create the data table.
				        var data = new google.visualization.DataTable();
				        data.addColumn('string', 'Employee');
				        data.addColumn('number', 'Totals');
				        /*data.addRows([

				        	//['blahd'], 0], // CANNOT have first term zero.
				          ['Mushrooms', 3],
				          ['Onions', 1],
				          ['Olives', 1],
				          ['Zucchini', 1],
				          ['Pepperoni', 2],
				          ['Blah', 0]
				        ]); 
						*/

						//mysql_data.forEach(function(item,index) {console.log(item + ' '+ index)});
						

						
						var dataString = '[';
						for (var key in mysql_data) {
							if (mysql_data.hasOwnProperty(key)) {
								dataString = dataString + '["'+key + '", '+mysql_data[key] + '], ';
							}
						}
						dataString = dataString.slice(0,-2);
						dataString = dataString + ']';
						//console.log(dataString);
						//data.addRows(dataString);
						var dataArray = JSON.parse(dataString);
						data.addRows(dataArray);
						

				        // Set chart options
				        var tF = "<?php echo $tF; ?>";
				        if (tF == "allTime") { tF = "all time";}
				        else if (tF == "thisYear") {tF = "this year";}
				        else if (tF == "thisMonth") {tF = "this month";}
				        else if (tF == "thisWeek") {tF = "this week"; }
				        else if (tF == "custom") {tF = "custom";}
				        var rB = "<?php echo $rB; ?>";
				        if (rB == "employee") {rB = "Employees"; }
				        else if (rB == "vendor") {rB = "Vendors (top 100)"; }
				        else if (rB == "customer") {rB = "Customers (top 100)"; }
				        else if (rB == "product/service") {rB = "Products/Services";}
				        var options = {'title':'Report for '+rB+' for '+tF+', gross income',
				                       'width':600, // 400
				                       'height':500}; // 300

				        // Instantiate and draw our chart, passing in some options.
				        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
				        chart.draw(data, options);

				        var chart2 = new google.visualization.BarChart(document.getElementById('chart2_div'));
				        chart2.draw(data,options);
				      }

				      // FINALLY WORKS.

		}

		// master function to display report data
		function updateData()
		{
			//alert('runnin updateData');

			reportBy = $("#reportBy").val();
			timeFrame = $("#timeFrame").val();

			window.location = "reports.php?reportBy=" + reportBy + "&timeFrame=" + timeFrame;

		}

	</script>

	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>
<body class="reports">
	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	<div id="admin_content">
		<h1>Reports &raquo;
		

		</h1>
			<hr />
			<div id="statusBox"></div>

			<div id="reportMenu">
				<form name="reportMenuForm" id="reportMenuForm" method="POST" action="">
					&nbsp;View report by: 
					<select name="reportBy" id="reportBy" onchange="updateData();">
						<option value="employee">employee</option>
						<option value="vendor">vendor</option>
						<option value="customer">customer</option>
						<option value="product/service">product/service</option>
					</select> 

					for time frame:
					<select name="timeFrame" id="timeFrame" onchange="updateData();">
						<option value="allTime">all time</option>
						<option value="thisYear">this year</option>
						<option value="thisMonth">this month</option>
						<option value="thisWeek">this week</option>
						<option value="today">today</option>
						<option value="custom">custom</option>
					</select>

					<b>&raquo; Please allow up to 1min for data to process, especially for large requests!</b>
				</form>
			</div>

			<div id="reportDisplayBox">
			<?php 
			if (isset($_GET['reportBy']) && isset($_GET['timeFrame']))
			{
				
				$rB = $_GET['reportBy'];
				$tF = $_GET['timeFrame'];

				?>
				<script type="text/javascript" language="javascript">
					$("#reportBy").val(<?php echo json_encode($rB); ?>);
					$("#timeFrame").val(<?php echo json_encode($tF); ?>);
				</script>
			
				<table>
				<tr>
					<td>
					<div id="chart_div"></div>
					</td>
					<td>
					<div id="chart2_div"></div>
					</td>
				</tr>
				</table>
				<script type="text/javascript">loadChart("<?php echo $rB; ?>", "<?php echo $tF; ?>");</script>
				<?php } ?>	
			</div>

	</div>
</body>
</html>
