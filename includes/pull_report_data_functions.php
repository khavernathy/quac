<?php
//session_start();
include('database.php'); // echo 'test';
/*
	pull_report_data_functions.php
	pulls data from MySQL and converts to JSON-readable format for visualization

*/

function get_data($reportBy = "employee",$timeFrame = "allTime") // set defaults to avoid errors if GET vars are not set. 
{
	$current_year = date('Y',time()); //echo $current_year;
	$current_month = date('m',time()); // echo $current_month;
	$day = date('w');
	$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
	$today = date('Y-m-d',time());

	$tF = '';
				if ($timeFrame == 'allTime') { $tF = ''; }
					elseif ($timeFrame == 'thisYear') {
						$tF = "AND `EndDate` >= '$current_year"."-01-01'";
					}
					elseif ($timeFrame == 'thisMonth') {
						$tString = $current_year."-".$current_month."-01"; //echo $tString;
						$tF = "AND `EndDate` >= '$tString'";
					}
					elseif ($timeFrame == 'thisWeek') {
						$tF = "AND `EndDate` >= '$week_start'";
					}
					elseif ($timeFrame == 'today') {
						$tF = "AND `EndDate` >= '$today'";
					}
					elseif ($timeFrame == 'custom') {

					}
	if ($reportBy == "vendor") {
		$tF = str_replace("End","",$tF);
	}

	global $Database;

	if ($reportBy == 'employee') {
		//echo 'tessst';

		// get list of employees by name
		$getemployees = "SELECT `EmployeeID`,`FirstName`,`LastName`, `Active` FROM `employees` ORDER BY `FirstName` ASC";
			$empArray = array();
			$stmt = $Database->prepare($getemployees);
			if ($stmt->execute())
			{
				$result = $stmt->get_result();
				
				for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
				{
					$result->data_seek($n);
					$row = $result->fetch_array();
					$eName = $row[1].' '.$row[2];

					array_push($empArray,$eName);
				}
				//var_dump($empArray);
				$result->close();
				//var_dump($empArray);

				// get employee totals.
				foreach ($empArray as $index => $value) 
				{
					
					//echo '<br />'.$value;
					$query_run = "SELECT `RowTotal` FROM `ticketdetails` WHERE `EmployeeName` = ? $tF";
					$stmt = $Database->prepare($query_run);
					$stmt->bind_param('s',$value);
					if ($stmt->execute())
					{
						$result = $stmt->get_result();
						//var_dump($result);
						//echo $result->num_rows; echo '<br />';
							
							$sum=0; // initial employee summer
							for ($n = 0; $n <= $result->num_rows - 1; $n++)
							{
								$result->data_seek($n);
								$row = $result->fetch_row();
								$sum=$sum + $row[0];
								//echo $row[0].'<br />';
							}

							//$rTotal = $row[0];
							//echo $rTotal;

							$result->close();
							//echo "SUM: : ".$sum;
							$totalsArray["$value"] = $sum;

							arsort($totalsArray); // sort for niceness.
						
					}
					else
					{
						return 'error; query failed';
					}
				}

				// done summing
				if (!empty($totalsArray)) {
					$output = $totalsArray;
				} else {
					$output = 'empty dataset.';
				}
				//var_dump($output);

			}
			else 
			{
				return 'error; query failed';
			}

	}
	elseif ($reportBy == 'vendor') {
		// get list of vendors
		$getv = "SELECT `VendorID`,`Name` FROM `vendors` ORDER BY `Name` ASC";
			$vArray = array();
			$stmt = $Database->prepare($getv);
			if ($stmt->execute())
			{
				$result = $stmt->get_result();
				
				for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
				{
					$result->data_seek($n);
					$row = $result->fetch_array();

					$vArray[$row[0]] = $row[1];
				}
				//var_dump($empArray);
				$result->close();
				//var_dump($vArray);

				foreach ($vArray as $index => $value) 
				{
					
					//echo '<br />'.$value;
					$query_run = "SELECT `Amount` FROM `transactions` WHERE `VendorID` = ? $tF";
					$stmt = $Database->prepare($query_run);
					$stmt->bind_param('s',$index);
					if ($stmt->execute())
					{
						$result = $stmt->get_result();
						//var_dump($result);
						//echo $result->num_rows; echo '<br />';
							
							$sum=0; // initial employee summer
							for ($n = 0; $n <= $result->num_rows - 1; $n++)
							{
								$result->data_seek($n);
								$row = $result->fetch_row();
								$sum=$sum + $row[0];
								//echo $row[0].'<br />';
							}

							//$rTotal = $row[0];
							//echo $rTotal;

							$result->close();
							//echo "SUM: : ".$sum;
							if ($sum < 0) {
								$totalsArray["$value"] = $sum * -1; // in this case all sums are probably negative because they are vendors.
							} // for simplicity we're only viewing the vendor totals in the negatives ("real" vendors..)

						
					}
					else
					{
						return 'error; query failed';
					}
				}

				if (!empty($totalsArray)) {
					// done summing
					arsort($totalsArray); // sort
					$output = array_slice($totalsArray, 0,100); // only top 100 to reduce complexity
					//var_dump($output);
				} else {
					$output = 'empty dataset.';
				}
			}
			else
			{
				return 'error; query failed';
			}

	}
	elseif ($reportBy == 'customer') {
		// get list of employees by name
		$getclients = "SELECT `ClientID`,`FirstName`,`LastName` FROM `clients`"; //" ORDER BY `ClientID` ASC";
		$cliArray = array();
		$stmt = $Database->prepare($getclients);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
			
			for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
			{
				$result->data_seek($n);
				$row = $result->fetch_array();
				//$cName = $row[1].' '.$row[2];
				$cliArray[$row[0]] = $row[1].' '.$row[2];
			}
			//var_dump($empArray);
			$result->close();
			//var_dump($cliArray);
		}
		else
		{
			return 'error; query failed';
		}

		// get cli totals.
				foreach ($cliArray as $index => $value) 
				{

					
					//echo '<br />'.$value;
					$query_run = "SELECT `RowTotal` FROM `ticketdetails` WHERE `ClientID` = ? $tF";
					$stmt = $Database->prepare($query_run);
					$stmt->bind_param('s',$index);
					if ($stmt->execute())
					{
						$result = $stmt->get_result();
						//var_dump($result);
						//echo $result->num_rows; echo '<br />';
							
							$sum=0; // initial employee summer
							for ($n = 0; $n <= $result->num_rows - 1; $n++)
							{
								$result->data_seek($n);
								$row = $result->fetch_row();
								$sum=$sum + $row[0];
								//echo $row[0].'<br />';
							}

							//$rTotal = $row[0];
							//echo $rTotal;

							$result->close();
							//echo "SUM: : ".$sum;
							$totalsArray["$value"] = $sum; // e.g. "Doug Franz => 1242.48"
						
					}
					else
					{
						return 'error; query failed';
					}
				}

				if (!empty($totalsArray)) {
					// done summing
					arsort($totalsArray); // this should return true
					$output = array_slice($totalsArray, 0,100); // take only top 100 clients (because there are 6,000+ which is a lot)

					//var_dump($output);
				} else {
					$output = 'empty dataset.';

				}
				
				
	}
	elseif ($reportBy == 'product/service') {
			/// here now....

	}

	// send all graphable data to reports.php via JSON where it will be preprocessed and then displayed with Google graphs.
	return json_encode($output);
	//var_dump($output);
}

//echo get_data('vendor','today');

//echo 'test';
?>
