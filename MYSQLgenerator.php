<?php
$servername = 'localhost:3306';
$username = 'root';
$password = 'password';

// Create connection
$conn = mysql_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysql_error());
}
echo "Connected successfully" . mysql_error();

mysql_select_db('lectio_skema') or die(mysql_error());

/*
mysql_query("CREATE TABLE moduler(
	id INT NOT NULL AUTO_INCREMENT, 
	PRIMARY KEY(id),
	 summary VARCHAR(64), 
	 description VARCHAR(512),
	 dtstart VARCHAR(32),
	 dtend VARCHAR(32),
	 location VARCHAR(32) )")
	 or die(mysql_error());  

echo "Table Created!";
*/

// Empty the table
mysql_query("TRUNCATE TABLE moduler");

// initialize lectio sdk
require_once('lectio.php');
$lectio = new lectio("600", "6936775795");

// constants used for events
$timezone = "Europe/Copenhagen";
$schoolAddress = "Odense Katedralskole";

// loop through the next 14 days and add each day's activities to the ics file
for ($i = 0; $i <= 14; $i++){
	$date = date('d-m-Y');

	echo "world";

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

		$state = $activities[$j]['state'];

		if ($state == "Aflyst") {
			continue;
		}

		$summary = $activities[$j]['class'] . " " . $activities[$j]['classroom'];
		// $description = "State" . $state . "\\nLektier: " . $activities[$j]['homework'];
		$description = $activities[$j]['homework'];
		$dtstart = $timezone.":".date("Ymd\THis", strtotime($dateStart));
		$dtend = $timezone.":".date("Ymd\THis", strtotime($dateEnd));
		$location = $schoolAddress;

		echo $summary;
		echo $description;
		echo $dtstart;
		echo $dtend;
		echo $location;

		// put into to mysql query
		$query = "INSERT INTO `lectio_skema`.`moduler` (
						`id` ,
						`summary` ,
						`description` ,
						`dtstart` ,
						`dtend` ,
						`location`
					)
					VALUES (
						NULL , '$summary', '$description', '$dtstart', '$dtend', '$location'
					)";	
		
		mysql_query($query) or trigger_error(mysql_error()." in ".$query) ;


		// put it all into ics format
		/*
		$output .= "BEGIN:VEVENT
SUMMARY:".$activities[$j]['class']."
DESCRIPTION:State: ".$activities[$j]['state']."\\nHomework: ".$activities[$j]['homework']."
UID:".md5($activities[$j]['time'])."
DTSTART;TZID=".$timezone.":".date("Ymd\THis", strtotime($dateStart))."
DTEND;TZID=".$timezone.":".date("Ymd\THis", strtotime($dateEnd))."
LOCATION:".$schoolAddress."
END:VEVENT\n";
		*/
	}
}

?>
