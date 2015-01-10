<?php

// initialize lectio sdk
require_once('../lectio.php');
$lectio = new lectio("600", "6936775795");

// constants used for events
$timezone = "Europe/Copenhagen";
$schoolAddress = "Odense Katedralskole";

// define header
// header('Content-type: text/calendar; charset=utf-8');
// header('Content-Disposition: inline; filename=lectiosdk.ics');

// begin ics file
$output = "BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
PRODID:-//Lectio SDK//Lectio SDK//EN
";

// loop through the next 14 days and add each day's activities to the ics file
for ($i = 0; $i <= 14; $i++){
	$date = date('d-m-Y');

	if ($i > 0) {
		$date = date('d-m-Y', strtotime($date.' + '.$i.' days'));
	}

	$activities = $lectio->getActivities($date);
	$amountOfModules = sizeof($activities);

	// loop through all the activites on the given day and add them to the ics file
	for ($j = 0; $j < $amountOfModules; $j++){
		// check what time the class is
		$dateStart = null;
		$dateEnd = null;

		// TODO: this currently only works for my school's specific module system
		if (strpos($activities[$j]['time'], "1. modul") != false){
			$dateStart = date('Y-m-d', strtotime($date))." 08:00:00";
			$dateEnd = date('Y-m-d', strtotime($date))." 09:40:00";
		} else if (strpos($activities[$j]['time'], "2. modul") != false){
			$dateStart = date('Y-m-d', strtotime($date))." 09:50:00";
			$dateEnd = date('Y-m-d', strtotime($date))." 11:30:00";
		} else if (strpos($activities[$j]['time'], "3. modul") != false){
			$dateStart = date('Y-m-d', strtotime($date))." 12:00:00";
			$dateEnd = date('Y-m-d', strtotime($date))." 13:40:00";
		} else if (strpos($activities[$j]['time'], "4. modul") != false){
			$dateStart = date('Y-m-d', strtotime($date))." 13:50:00";
			$dateEnd = date('Y-m-d', strtotime($date))." 15:30:00";
		} else {
			// TODO: full day activity
			$dateStart = date('Y-m-d', strtotime($date));
			$dateEnd = date('Y-m-d', strtotime($date));
		}

		// put it all into ics format
		$output .= "BEGIN:VEVENT
SUMMARY:".$activities[$j]['class']."
DESCRIPTION:State: ".$activities[$j]['state']."\\nHomework: ".$activities[$j]['homework']."
UID:".md5($activities[$j]['time'])."
DTSTART;TZID=".$timezone.":".date("Ymd\THis", strtotime($dateStart))."
DTEND;TZID=".$timezone.":".date("Ymd\THis", strtotime($dateEnd))."
LOCATION:".$schoolAddress."
END:VEVENT\n";
	}
}

$output .= "END:VCALENDAR";

echo $output;

?>
