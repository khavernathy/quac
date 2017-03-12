<?php
# PHP Calendar (version 2.3), written by Keith Devens
# http://keithdevens.com/software/php_calendar
#  see example at http://keithdevens.com/weblog
# License: http://keithdevens.com/software/license

function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $first_day = 0, $prev_link, $next_link)
{

	$first_of_month = gmmktime(0,0,0,$month,1,$year);
	#remember that mktime will automatically correct if invalid dates are entered
	# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
	# this provides a built in "rounding" feature to generate_calendar()

	$day_names = array(); #generate all the day names according to the current locale
	for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name

	list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
	$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;  #note that some locales don't capitalize month and day names	
	


	#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
	
	$calendar = '<div id="calendar-box"><table class="calendar">'."\n".
		'<caption class="calendar-month">'.$prev_link.'&nbsp;&nbsp;&nbsp;&nbsp;';

	$calendar .=  '<form class="cal-select" method="GET" action="manage.php">
					<input type="hidden" name="display" value="month" />
					<select name="month">';
	$months = array(1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December');
	
	for ($x = 1; $x <= 12; $x++)
	{
		if (isset($_GET['month']) || isset($_SESSION['lastMonthViewMonth']))
		{
			if (isset($_GET['month'])) { $m = $_GET['month']; } else { $m = $_SESSION['lastMonthViewMonth']; }
			if ($m == $x)
				$calendar .=  '<option value="'.$x.'" SELECTED>'.$months[$x].'</option>';
			else
				$calendar .=  '<option value="'.$x.'">'.$months[$x].'</option>';
		}
		else
		{
			if (date('n', time()) == $x)
				$calendar .=  '<option value="'.$x.'" SELECTED>'.$months[$x].'</option>';
			else
				$calendar .=  '<option value="'.$x.'">'.$months[$x].'</option>';
		}
		
	}
	$calendar .=  '</select> <select name="year">';
	for ($x = 2010; $x <= 2070; $x++)
	{	
		if (isset($_GET['year']) || isset($_SESSION['lastMonthViewYear']))
		{
			if (isset($_GET['year'])) { $y = $_GET['year']; } else { $y = $_SESSION['lastMonthViewYear']; }
			if ($y == $x)
				$calendar .=  '<option value="'.$x.'" SELECTED>'.$x.'</option>';
			else
				$calendar .=  '<option value="'.$x.'">'.$x.'</option>';
		}
		else
		{
			if (date('Y', time()) == $x)
				$calendar .=  '<option value="'.$x.'" SELECTED>'.$x.'</option>';
			else
				$calendar .=  '<option value="'.$x.'">'.$x.'</option>';
		}
	}
	$calendar .=  '</select> <input type="submit" value="view" /></form>';
	
	$calendar .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$next_link."</caption>\n<tr class=\"day-names-row\">";

	if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
		#if day_name_length is >3, the full name of the day will be printed
		foreach($day_names as $d)
			$calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
		$calendar .= "</tr>\n<tr class=\"day-row\">";
	}
	
	// begin calendar days
	if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
	for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		if($weekday == 7){
			$weekday   = 0; #start a new week
			$calendar .= "</tr>\n<tr class=\"day-row\">";
		}
		if(isset($days[$day]) and is_array($days[$day]))
		{
			@list($classes, $content) = $days[$day];
			if(is_null($content))  $content  = $day;
			$calendar .= '<td'.($classes ? ' class="'.htmlspecialchars($classes).'">' : '>').
				($content).'</td></a>';
		}
		else $calendar .= '<td>'.$day.'</td>';
	}
	if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days

	return $calendar."</tr>\n</table></div>\n";
}
?>