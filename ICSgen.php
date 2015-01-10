<?php

$servername = 'localhost:3306';
$username = 'root';
$password = 'ELD333ny';

// Create connection
$conn = mysql_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysql_error());
}
// echo "Connected successfully" . mysql_error();

mysql_select_db('lectio_skema') or die(mysql_error());

// define header
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=lectiosdk.ics');


$result = mysql_query("SELECT id, summary, description, dtstart, dtend, location FROM moduler");


// begin ics file
$output = "BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
PRODID:-//Lectio SDK//Lectio SDK//EN
";

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $summary = $row['summary'] ;
    $description = $row['description'];
    $dtstart = $row['dtstart'];
    $dtend = $row['dtend'];
    $location = $row['location'];


    $output .= "BEGIN:VEVENT
SUMMARY: ".$summary." \r\n
DESCRIPTION: ".$description." \r\n
UID:".md5( $dtstart)." \r\n
DTSTART;TZID= ".$dtstart." \r\n
DTEND;TZID= ".$dtend." \r\n
LOCATION: ".$location." \r\n
END:VEVENT\r\n";

}

$output .= "END:VCALENDAR";

echo $output;

?>