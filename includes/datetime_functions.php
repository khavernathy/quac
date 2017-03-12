<?php
function convertdate($date,$func) 
{
	if ($date == '')
	{ return ''; }
	else
	{
		if ($func == 'tomysql')
		{ //insert conversion to MySQL database
		$atoms = explode("-",$date);
		$month = $atoms[0]; $day = $atoms[1]; $year = $atoms[2];
		$date = "$year-$month-$day";
		return $date;
		}
		elseif ($func == 'touser')
		{ //output conversion to User
		$atoms = explode("-",$date);
		$year = $atoms[0]; $month = $atoms[1]; $day = $atoms[2];
		$date = "$month-$day-$year";
		return $date;
		}
	}
}

function generateTimes($input = '',$end = '')
{
	// Makes a <select> element with user friendly times but MySQL friendly values
	$iH = ''; $iM = ''; $iS = '';

	if ($input != '')
	{
		$atoms = explode(":",$input);
		$iH = $atoms[0]; $iM = $atoms[1]; $iS = $atoms[2];
	}
		
	if ($end == '') $output = '<select id="time" name="time">';
	elseif ($end == "end") $output = '<select id="endtime" name="endtime">';
	$timestamp1 = strtotime("6:00:00");
	
	for ($h = date("H",$timestamp1); $h <= 11; $h++)
	{	

		if ($h != "06" && $h != "11" && strpos($h,"0") == false) $h = "0".$h;
		for ($m = 0; $m <= 45; $m = $m + 15)
		{
			if ($m == 0) $m = "00";
			$output .= '<option value="'.$h.':'.$m.':00"';
			if ("$iH:$iM:$iS" == "$h:$m:00") { $output .= " SELECTED"; }
			$output .= '>'.$h.':'.$m.' am</option>';
		}
	}
	
	$timestamp2 = strtotime("12:00:00");
	
	for ($h = date("H",$timestamp2); $h <= 21; $h++)
	{
		if ($iH == $h) $k = 1;
		for ($m = 0; $m <= 45; $m = $m + 15)
		{	
			if ($m == 0) $m = "00";
			$output .= '<option value="'.$h.':'.$m.':00" ';
			if ("$iH:$iM:$iS" == "$h:$m:00") { $output .= " SELECTED"; }
			$output .= '>'.(($h == 12) ? $h : ($h-12)).':'.$m.' pm</option>';
		}
	}
	
	$output .= '</select>';
	
	return $output;
}

function timetouser($input) // takes mysql time XX:XX:XX and converts to X:XXam/pm
{
	$atoms = explode(":",$input);
	$h= $atoms[0];
	$m = $atoms[1]; 
	$s = $atoms[2]; 
	$output = date("h:i a",mktime($h,$m,$s,1,1,2000));
	
	/*
	if ($h[0] == "0" && $h != "00") 
	{
		$h = str_replace("0","",$h);
	}
	if ($h > 12) 
	{
		$h = ($h - 12); $i = "pm";
	}
	elseif ($h==12 && $m=="00")
	{
		$i = "pm";
	}
	else 
	{
		$i = "am";
	}
	$output = $h.':'.$m.$i;
	*/
	return $output;
}

function usertimetomysql($input) // takes X:XXam/pm and converts to mysql 00:00:00
{
	return date("H:i:s",strtotime($input));
}

function convertphone($pn,$type)
{
	if ($type == "touser") {
		$output = '('.$pn[0].$pn[1].$pn[2].') '.$pn[3].$pn[4].$pn[5].'-'.$pn[6].$pn[7].$pn[8].$pn[9];
	} elseif ($type == "tomysql") {
		$output = preg_replace( '/[^0-9]/', '', $pn );
	}
	return $output;
}

function timeToDec($inputTime)
{
	$atoms = explode(":",$inputTime);
	$outputDec = round($atoms[0] + ($atoms[1] / 60),2);
	return $outputDec;
}
?>