<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');

	$errorDetails = '';
	$error = false;
	$ticketSaveSuccess = '';
	$updateSuccess = '';
	$newRowAddSuccess = '';

	// all the data from the ticket was processed and sent here in the $_POST array through a JS function using AJAX.
	// here it needs to be saved properly in the DB
	
	// first assignment of variables for the main ticket data (easy stuff)
	//$tid = $_POST['tid']; // $tid seems uneccesary
	$TicketID = $_POST['TicketID'];
	$cid = $_POST['ClientID'];

	$initialRows_str = ltrim($_POST['initialRows']," "); // trim first space(s) -- really only should be one
	$irows_array = explode(" ", $initialRows_str); // this is now an array of initial rows. Will check later for discrepancy with $ira to delete rows per user selection.
	//echo "irows: ";
	//var_dump($irows_array);

	$tStatus = $_POST['TicketStatus']; // radio
	$otickstat = $_POST['tickstat']; // original status
	// flag status change for product stock count changes
	/*if ($tStatus != $otickstat) {
		$changeProdCount = true;
	} else {$changeProdCount = false;}
	*/ //not needed
	$cName = $_POST['ClientName'];
	if ($_POST['dtCreated'] == '') {
		$dtCreated = date("Y-m-d H:i:s"); // will use "NOW()" for datetime on new
	} else {
		$dtCreated = $_POST['dtCreated'];
	}
	$creator = $_POST['Creator'];
	$dateScheduled = $_POST['DateScheduled'];
	
	$payType1 = $_POST['PayType1'];
	$payType2 = $_POST['PayType2'];
	$payData1 = $_POST['PayData1'];
	$payData2 = $_POST['PayData2'];
	$tender1 = $_POST['Tender1'];
	$tender2 = $_POST['Tender2'];
	
	$subtotal = $_POST['Subtotal'];
	$tax = $_POST['Tax'];
	$due = $_POST['Due'];
	$tip = $_POST['Tip'];
	$tipfor = $_POST['TipFor'];
	$total = $_POST['Total'];

	$change = $_POST['Change'];
	$changeType = $_POST['ChangeType'];
	$ticketComment = $_POST['TicketComment'];

	$cFirstName = explode(" ",$cName)[0];
	$cLastName = str_replace(explode(" ",$cName)[0]." ","",$cName);
	$dateCreated = convertdate(explode(" ",$dtCreated)[0],"tomysql"); // will be blank if $dtCreated is blank
	if ($dtCreated != '') { 
	$ut = explode(" ",$dtCreated)[2];
		$ut = str_replace("a", " a", $ut);
		$ut = str_replace("p", " p", $ut);
 	$timeCreated = usertimetomysql($ut);
 	} else {
 		$timeCreated = '';
 	}

 	$dtCreated = $dateCreated.' '.$timeCreated;
 	$ds = convertdate($dateScheduled,"tomysql");

 	foreach ($_POST as $k => $v)
 	{
 		// fetches the first "StartTime-" and marks it for the ticket time scheduled
 		if (strpos($k,"StartTime-") !== false)
 		{
 			$timeScheduled = $_POST[$k];
 			break;
 		}
 	}
	
	if ($tStatus == "Closed") {
		$dtClosed = date("Y-m-d",time())." ".date("H:i:s",time());
	} else {
		$dtClosed = NULL;
	}

	// (1st PART) ========================== 
	// save the ticket as a whole
	// =====================================

	if ($TicketID == '') // if a new ticket -- use NOW() for creation datetime
	{
		// save the new ticket
		$stmt = $Database->prepare("INSERT INTO `tickets` VALUES(?,?,?,NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);");
		$stmt->bind_param("sssssssssisssssssssssssssss",$a='',$b='',$creator,$ds,$timeScheduled,$c=NULL,$d=NULL,$dtClosed,$tStatus,$cid,$cFirstName,$cLastName,$subtotal,$tax,$tip,$tender1,$payType1,$payData1,$tender2,$payType2,$payData2,$change,$changeType,$total,$ticketComment,$f=NULL,$tipfor);
		if ($stmt->execute())
		{
			$ticketSaveSuccess = true;
			$stmt->close();
			$tid = $Database->insert_id;
			$TicketID = $tid; // overwrite empty $TicketID with the just-made one.
		}
		else {
			$ticketSaveSuccess = false; $error = true; $errorDetails .= 'failed to save new ticket to tickets tables in DB. ';
		}

		// dtCanceled, cancelComment, Comment and dtEmailReminder are NULL for now

	}
	else
	{
		// update old ticket
		$stmt = $Database->prepare("UPDATE `tickets` SET `ExtraID` = ?, `Creator` = ?, `dtCreated` = ?, `DateScheduled` = ?, `TimeScheduled` = ?, `dtCanceled` = ?, `CancelComment` = ?, `dtClosed` = ?, `TicketStatus` = ?, `ClientID` = ?, `FirstName` = ?, `LastName` = ?, `Subtotal` = ?, `Tax` = ?, `Tip` = ?, `Tender1` = ?, `PayType1` = ?, `PayData1` = ?, `Tender2` = ?, `PayType2` = ?, `PayData2` = ?, `Change` = ?, `ChangeType` = ?, `Total` = ?, `Comment` = ?, `dtEmailReminder` = ?, `TipFor` = ? WHERE `TicketID` = ?;");
		$stmt->bind_param("sssssssssisssssssssssssssssi", $a='',$creator,$dtCreated,$ds,$timeScheduled,$b=NULL,$c=NULL,$dtClosed,$tStatus,$cid,$cFirstName,$cLastName,$subtotal,$tax,$tip,$tender1,$payType1,$payData1,$tender2,$payType2,$payData2,$change,$changeType,$total,$ticketComment,$zz='',$tipfor,$TicketID);
		if ($stmt->execute())
		{
			$ticketSaveSuccess = true;
			$stmt->close();
		}
		else {
			$ticketSaveSuccess = false; $error = true; $errorDetails .= 'failed to update with old ticket in table in DB). ';
		}
	}
	

	// (2nd PART) ======================== now handle the individual rows
	$c = 0; // count for individual ticket detail rows
	$ira = array(); // stores the Individual row data ID number e.g. $ira[0] = '12583'; $ira[1] = '12584', etc.

	foreach ($_POST as $k => $v)
	{
		// counts by occurences of "Employee" in the array key
		if (strpos($k, 'Employee') !== false)
		{
			$ira[$c] = str_replace("Employee-","",$k);
			$c++;
		}
	}

	// first check to delete unwanted existing rows.
	// if $ira differs from $irows_array
	$toDelete = array(); // will contain flagged IDs to delete from MySQL
	foreach ($irows_array as $index => $rowID)
	{
		if (!in_array($rowID, $ira))
		{
			array_push($toDelete,$rowID);
		}
	}

	//var_dump($toDelete);
	// PERFORM ticketdetails ROW DELETIONS as needed.
	if (!empty($toDelete))
	{
		foreach ($toDelete as $i => $rowID)
		{
			// update old ticket
			$stmt = $Database->prepare("DELETE FROM `ticketdetails` WHERE `RowID` = ?");
			$stmt->bind_param("s", $rowID);
			if ($stmt->execute())
			{
				$rowDelete = true;
				$stmt->close();
			}
			else {
				$error = true; $errorDetails .= '-failed to delete an individual detail row. ';
			}
		}
	}


//	$dataCols = ["Employee","StartTime","Duration","Type","ID","Description","Price","Tax","Qty","Total","TDrowID"];
	$dbCols = ["RowID","TicketID","ClientID","FirstName","LastName","Type","DateCreated","TimeCreated","StartDate","EndDate","StartTime","EndTime","CreatedBy","EmployeeID","EmployeeName","Description","Price","Quantity","RowTotal","DateClosed","TimeClosed","Duration","ProductID","ServiceID","Taxable"];



	for ($x=0; $x <= count($ira) - 1; $x++) // ira= individual row array
	{

		// cycle through data for each ticket detail row
		$trid = $ira[$x];

		

		// if it's an existing ticket detail row
		//if ($_POST["TDrowID-".$trid] != "new")
		//echo "trid = ".$trid;  // e.g. 123, 17017, new6, new8, etc.
		if (!fnmatch('new*', $trid))
		{

			foreach ($dbCols as $ind => $colName)
			{
				$skip = false;
				if ($ind == 0) { $skip = true; }
				elseif ($ind == 1) { $cv = $TicketID; }
				elseif ($ind == 2) { $cv = $cid; }
				elseif ($ind == 3) { $cv = $cFirstName; } // fetches only first name
				elseif ($ind == 4) { $cv = $cLastName; } // fetches only last name
				elseif ($ind == 5) { $cv = $_POST["Type-".$trid]; }
				elseif ($ind == 6) { $cv = $dateCreated; } // fetches and converts only date created
				elseif ($ind == 7) { $cv = $timeCreated; } // fetches and converts only the time created (more complicated)
				elseif ($ind == 8) { $cv = $ds; } // converts date scheduled
				elseif ($ind == 9) { $cv = $ds; } // "                     "
				elseif ($ind == 10) { $cv = $_POST["StartTime-".$trid]; }
				elseif ($ind == 11) { $cv = date("H:i:s",strtotime($_POST["StartTime-".$trid]) + ($_POST["Duration-".$trid]*60)); } // adds duration to start time to calculate end time
				elseif ($ind == 12) { $cv = $creator; }
				elseif ($ind == 13) { $cv = $_POST["Employee-".$trid]; $skip = true; }
				elseif ($ind == 14) { $cv = $_POST["Employee-".$trid]; }
				elseif ($ind == 15) { $cv = $_POST["Description-".$trid]; }
				elseif ($ind == 16) { $cv = $_POST["Price-".$trid]; }
				elseif ($ind == 17) { $cv = $_POST["Qty-".$trid]; }
				elseif ($ind == 18) { $cv = $_POST["Total-".$trid]; }
				elseif ($ind == 19) { $cv = "0000-00-00"; }
				elseif ($ind == 20) { $cv = "00:00:00"; }
				elseif ($ind == 21) { $cv = $_POST["Duration-".$trid]; }
				elseif ($ind == 22) { $cv = $_POST["ID-".$trid]; }
				elseif ($ind == 23) { $cv = $_POST["ID-".$trid]; } 
				elseif ($ind == 24) { $cv = $_POST["Tax-".$trid]; }

				if ($skip == false)
				{
					$stmt = $Database->prepare("UPDATE `ticketdetails` SET `".$colName."` =  ? WHERE `RowID` = ?");
					$stmt->bind_param("ss", $cv, $trid);
					if ($stmt->execute())
					{
						if ($updateSuccess != false) { $updateSuccess = true; } // make sure to not overwrite any reported error
						$stmt->close();
					}
					else
					{
						$updateSuccess = false; $error = true; $errorDetails .= 'failed to update old ticket detail row with ID = '.$trid.' ';
					}
				}
			
			}


		}

		// or else, this is a new row
		//elseif ($_POST["TDrowID-".$trid] == "new")
		elseif (fnmatch('new*', $trid))
		{
			$stmt = $Database->prepare("INSERT INTO `ticketdetails` VALUES(?,?,?,?,?,?,CURDATE(),CURRENT_TIMESTAMP(),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);");
			$stmt->bind_param("ssssssssssssssssssssssi",$a='',$TicketID,$cid,$cFirstName,$cLastName,$_POST["Type-".$trid],$ds,$ds,$_POST["StartTime-".$trid],date("H:i:s",strtotime($_POST["StartTime-".$trid]) + ($_POST["Duration-".$trid]*60)),$creator,$b='',$_POST["Employee-".$trid],$_POST["Description-".$trid],$_POST["Price-".$trid], $_POST["Qty-".$trid], $_POST["Total-".$trid], $c="0000-00-00",$d="00:00:00",$_POST["Duration-".$trid], $_POST["ID-".$trid], $_POST["ID-".$trid], $_POST["Tax-".$trid]);
			if ($stmt->execute())
			{
				$newRowAddSuccess = true;
				$inserted_id = $stmt->insert_id;
				//echo "new insert TR: ".$inserted_id;
				$stmt->close();
			}
			else
			{
				$newRowAddSuccess = false; $error = true; $errorDetails .= 'failed to add new ticket detail row. ';
			}
		}

		// DONE with ticket row. run product stock count change if needed.
		// may get weird if user changes quantities a lot before / after saving and changing ticket status.
		// ==========
		// threrefore should make them re-open before modifying at all (pressing save)
		// ==========

		// get employee ID from name
		$en = $_POST["Employee-".$trid];
		$enParts = explode(" ",$en);
		$enF = $enParts[0]; // may become a problem later if user doesn't make first names one word.
		$enL = str_replace($enF.' ','',$en);

		$stmt=$Database->prepare("SELECT `EmployeeID` FROM `employees` WHERE `FirstName` = ? AND `LastName` = ?");
		$stmt->bind_param("ss",$enF,$enL);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
			$row = $result->fetch_array();
			$empID = $row[0]; // CAPTURED THE ID OF EMPLOYEE HERE using name.
			$stmt->close();
		}
		else
		{
			$error = true; $errorDetails .= ". Failed to fetch employee ID. ";
		}

		// get employee default fraction by id
		$stmt=$Database->prepare("SELECT `DefaultCut`,`DefaultProductCut` FROM `employees` WHERE `EmployeeID` = ?");
		$stmt->bind_param("s",$empID);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
			$row = $result->fetch_array();
			$defaultCut = round($row[0],3); // CAPTURED THE default fraction OF EMPLOYEE HERE using name.
			$defaultProductCut = round($row[1],3); // likewise for product commission
			$stmt->close();
		}
		else
		{
			$error = true; $errorDetails .= ". Failed to fetch employee default cut (pay frac.) ";
		}

		// =========================================== IF PRODUCT
		if ($_POST["Type-".$trid] == "P") 
		{
			$item_id = $_POST["ID-".$trid];
			$item_count = $_POST["Qty-".$trid];

				// Open -> Closed: remove a stock count for the product.
				if (($otickstat == "Open" && ($tStatus == "Closed" || $tStatus == "Canceled")) || (($otickstat == "Closed" && $tStatus == "Closed" && (strpos($trid,"new") === 0)))) {
					//echo 'trig';
					$changeSQL = "UPDATE `products` SET `StockCount` = `StockCount` - $item_count WHERE `ProductID` = '$item_id'";

					// add payroll element
					if (strpos($trid,"new") === 0) { $the_right_id = $inserted_id;} else {$the_right_id = $trid;}
					$stmt = $Database->prepare("INSERT INTO `current_payroll` VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
					$stmt->bind_param("sisssssssssssss",$xyzid='',$the_right_id,$empID,$_POST["Employee-".$trid],$abcid='',$_POST["ID-".$trid],$_POST["Type-".$trid],$_POST["Description-".$trid],$cName,$_POST["Price-".$trid],$_POST["Qty-".$trid],$_POST["Total-".$trid],$defaultProductCut,$t=$defaultProductCut * $_POST["Total-".$trid], convertdate($_POST["DateScheduled"],"tomysql"));
					if ($stmt->execute())
					{
						//great
						$stmt->close();
					}
					else
					{
						$error = true; $errorDetails .= 'failed to add service to current payroll, trid = '.$trid.' ';
					}
				} 
				// Closed -> Open: add a stock count back.
				// it's important the user closes tickets. will probably add this automated later (to save and close)
				else if (($otickstat == "Closed" || $otickstat == "Canceled") && $tStatus == "Open") 
				{
					$changeSQL = "UPDATE `products` SET `StockCount` = `StockCount` + $item_count WHERE `ProductID` = '$item_id'";

					// and remove product listing from payroll
					
					$stmt = $Database->prepare("DELETE FROM `current_payroll` WHERE `TRID` = ?");
					$stmt->bind_param("s",$trid);
					if ($stmt->execute())
					{
						$stmt->close();
					}
					else
					{
						$error = true; $errorDetails .= 'failed to remove product from current payroll, trid = '.$trid.'';
					}
				}


				// change product stock count, if needed
				if (isset($changeSQL)) 
				{
					$stmt = $Database->prepare($changeSQL);
					if ($stmt->execute())
					{
						// changed stock count fine.
						$stmt->close();
					}
					else {
						$error = true;
						$errorDetails .= ". failed to run SQL query to change product stock count.";
					}
				}
			
		}

		// ============================================ ELIF SERVICE
		elseif ($_POST["Type-".$trid] == "S")
		{
			//echo 'S found'; //works
			// Open -> Closed: add service to payroll db  / OR CLOSED-> CLOSED FOR NEW TRID

			//echo '['.$trid.', '.strpos($trid,"new").'];';
			//echo "strpos: ".strpos($trid,"new").' \n';
			if (!isset($inserted_id)) { $inserted_id = '';}		

			if (($otickstat == "Open" && ($tStatus == "Closed" || $tStatus == "Canceled")) || ($otickstat == "Closed" && $tStatus == "Closed" && (strpos($trid,"new") === 0))) 
			{
				if (strpos($trid,"new") === 0) { $the_right_id = $inserted_id;} else {$the_right_id = $trid;}
				//echo 'O -> C';
				$stmt = $Database->prepare("INSERT INTO `current_payroll` VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
					$stmt->bind_param("sisssssssssssss",$xyzid='',$the_right_id,$empID,$_POST["Employee-".$trid],$_POST["ID-".$trid],$abcid='',$_POST["Type-".$trid],$_POST["Description-".$trid],$cName,$_POST["Price-".$trid],$_POST["Qty-".$trid],$_POST["Total-".$trid],$defaultCut,$t=$defaultCut * $_POST["Total-".$trid], convertdate($_POST["DateScheduled"],"tomysql"));
					if ($stmt->execute())
					{
						//great
						$stmt->close();
						//echo 'inserted S row to current_payroll';
					}
					else
					{
						$error = true; $errorDetails .= 'failed to add service to current payroll, trid = '.$trid.' ';
					}
			}
			// Closed -> Open: remove service from payroll db
			else if (($otickstat == "Closed" || $otickstat == "Canceled") && $tStatus == "Open")
			{
				$stmt = $Database->prepare("DELETE FROM `current_payroll` WHERE `TRID` = ?");
					$stmt->bind_param("s",$trid);
					if ($stmt->execute())
					{
						$stmt->close();
					}
					else
					{
						$error = true; $errorDetails .= 'failed to remove service from current payroll, trid = '.$trid.'';
					}
				
			}
		}

	}

$errorDetails .= $Database->error; // returns last error 
$errorDetails .= "...done with error details.";

// finally save the ticket ID to session memory
$_SESSION['lastApptID'] = $TicketID;

//if ($ticketSaveSuccess != false && $updateSuccess != false && $newRowAddSuccess != false) 
if ($error != true)
{ echo "\nSuccess for TicketID: ".json_encode($TicketID)."\n";
	}
else { echo 'ERROR detected:'.$errorDetails; 
	 }
// finally echo out a response (success or failure etc.
echo json_encode($_POST);
echo "\n\nTicket detail row ID's saved: ".json_encode($ira);

//if (($updateSuccess == true || $updateSuccess == '') && ($newSuccess == true || $newSuccess == '')) { echo 'everything seems to have saved well'; }
?>
