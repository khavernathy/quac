<?php
header('Content-type: application/json');
session_start();
include('../includes/database.php');
include('../includes/datetime_functions.php');
$output = array();
$output['details'] = '';
$output['ticket'] = '';
if (!isset($_GET['selected_id']))
{
	$output .= 'No ID was specified';
}
else
{
	$id = $_GET['selected_id'];
	$stmt = $Database->prepare("SELECT * FROM `tickets` WHERE `TicketID` = ?");
	$stmt->bind_param("s", $id);
	if ($stmt->execute())
	{
		$res = $stmt->get_result();
		//$total_rows = $res->num_rows;
		$res->data_seek(0); //gets individual row
		$output['ticket'] = $res->fetch_array(); // $res->fetch_assoc() fetches the key names instead of numbers
		$res->close();
		
		$stmt2 = $Database->prepare("SELECT * from `ticketdetails` WHERE `TicketID` = ?");
		$stmt2->bind_param("s", $id);
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
}
echo json_encode($output);

/* e.g. output for TicketID = 1033;

{
"details":
[
{"0":14,"RowID":14,"1":1033,"TicketID":1033,"2":6220,"ClientID":6220,"3":"Darlene","FirstName":"Darlene","4":"Fuerst","LastName":"Fuerst","5":"S","Type":"S","6":"0000-00-00","DateCreated":"0000-00-00","7":"13:07:00","TimeCreated":"13:07:00","8":"0000-00-00","StartDate":"0000-00-00","9":"0000-00-00","EndDate":"0000-00-00","10":"15:15:00","StartTime":"15:15:00","11":"17:25:00","EndTime":"17:25:00","12":"Christina","CreatedBy":"Christina","13":100,"EmployeeID":100,"14":"Michelle Monroe","EmployeeName":"Michelle Monroe","15":"Massage Micro lift","Description":"Massage Micro lift","16":200,"Price":200,"17":1,"Quantity":1,"18":200,"RowTotal":200,"19":"0000-00-00","DateClosed":"0000-00-00","20":"16:13:00","TimeClosed":"16:13:00"},

{"0":15,"RowID":15,"1":1033,"TicketID":1033,"2":6220,"ClientID":6220,"3":"Darlene","FirstName":"Darlene","4":"Fuerst","LastName":"Fuerst","5":"P","Type":"P","6":"0000-00-00","DateCreated":"0000-00-00","7":"13:07:00","TimeCreated":"13:07:00","8":"0000-00-00","StartDate":"0000-00-00","9":"0000-00-00","EndDate":"0000-00-00","10":"00:00:00","StartTime":"00:00:00","11":"00:00:00","EndTime":"00:00:00","12":"Christina","CreatedBy":"Christina","13":100,"EmployeeID":100,"14":"Michelle Monroe","EmployeeName":"Michelle Monroe","15":"Massage Promo 2009","Description":"Massage Promo 2009","16":60,"Price":60,"17":1,"Quantity":1,"18":60,"RowTotal":60,"19":"0000-00-00","DateClosed":"0000-00-00","20":"16:13:00","TimeClosed":"16:13:00"},

{"0":16,"RowID":16,"1":1033,"TicketID":1033,"2":6220,"ClientID":6220,"3":"Darlene","FirstName":"Darlene","4":"Fuerst","LastName":"Fuerst","5":"S","Type":"S","6":"0000-00-00","DateCreated":"0000-00-00","7":"13:07:00","TimeCreated":"13:07:00","8":"0000-00-00","StartDate":"0000-00-00","9":"0000-00-00","EndDate":"0000-00-00","10":"17:25:00","StartTime":"17:25:00","11":"18:25:00","EndTime":"18:25:00","12":"Christina","CreatedBy":"Christina","13":100,"EmployeeID":100,"14":"Michelle Monroe","EmployeeName":"Michelle Monroe","15":"Facial","Description":"Facial","16":45,"Price":45,"17":1,"Quantity":1,"18":45,"RowTotal":45,"19":"0000-00-00","DateClosed":"0000-00-00","20":"16:13:00","TimeClosed":"16:13:00"},

{"0":17,"RowID":17,"1":1033,"TicketID":1033,"2":6220,"ClientID":6220,"3":"Darlene","FirstName":"Darlene","4":"Fuerst","LastName":"Fuerst","5":"P","Type":"P","6":"0000-00-00","DateCreated":"0000-00-00","7":"13:07:00","TimeCreated":"13:07:00","8":"0000-00-00","StartDate":"0000-00-00","9":"0000-00-00","EndDate":"0000-00-00","10":"00:00:00","StartTime":"00:00:00","11":"00:00:00","EndTime":"00:00:00","12":"Christina","CreatedBy":"Christina","13":100,"EmployeeID":100,"14":"Michelle Monroe","EmployeeName":"Michelle Monroe","15":"Botox Serum","Description":"Botox Serum","16":45,"Price":45,"17":1,"Quantity":1,"18":45,"RowTotal":45,"19":"0000-00-00","DateClosed":"0000-00-00","20":"16:13:00","TimeClosed":"16:13:00"},

{"0":18,"RowID":18,"1":1033,"TicketID":1033,"2":6220,"ClientID":6220,"3":"Darlene","FirstName":"Darlene","4":"Fuerst","LastName":"Fuerst","5":"P","Type":"P","6":"0000-00-00","DateCreated":"0000-00-00","7":"13:07:00","TimeCreated":"13:07:00","8":"0000-00-00","StartDate":"0000-00-00","9":"0000-00-00","EndDate":"0000-00-00","10":"00:00:00","StartTime":"00:00:00","11":"00:00:00","EndTime":"00:00:00","12":"Christina","CreatedBy":"Christina","13":100,"EmployeeID":100,"14":"Michelle Monroe","EmployeeName":"Michelle Monroe","15":"30 day perfector set","Description":"30 day perfector set","16":63.5,"Price":63.5,"17":1,"Quantity":1,"18":63.5,"RowTotal":63.5,"19":"0000-00-00","DateClosed":"0000-00-00","20":"16:13:00","TimeClosed":"16:13:00"}

],

"ticket":
{
"0":1033,"TicketID":1033,"1":15,"ExtraID":15,"2":"Christina","Creator":"Christina","3":"2010-01-06 13:07:21","dtCreated":"2010-01-06 13:07:21","4":"2010-01-08","DateScheduled":"2010-01-08","5":"00:00:00","TimeScheduled":"00:00:00","6":"0000-00-00 00:00:00","dtCanceled":"0000-00-00 00:00:00","7":"","CancelComment":"","8":"2010-01-09 16:13:00","dtClosed":"2010-01-09 16:13:00","9":"Closed","TicketStatus":"Closed","10":6220,"ClientID":6220,"11":"Darlene","FirstName":"Darlene","12":"Fuerst","LastName":"Fuerst","13":413.5,"Subtotal":413.5,"14":7.5999999046326,"Tax":7.5999999046326,"15":0,"Tip":0,"16":421.10000610352,"Tender1":421.10000610352,"17":"Check","PayType1":"Check","18":"Check #3037","PayData1":"Check #3037","19":0,"Tender2":0,"20":"","PayType2":"","21":"","PayData2":"","22":0,"Change":0,"23":"Cash","ChangeType":"Cash","24":421.10000610352,"Total":421.10000610352,"25":"","Comment":"","26":"0000-00-00 00:00:00","dtEmailReminder":"0000-00-00 00:00:00"
}

}

*/

?>