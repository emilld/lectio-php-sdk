<!DOCTYPE html>

<?php
    // initialize lectio sdk
    require_once("lectio.php");
    $lectio = new lectio("402", "4763366305");
?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Lectio PHP SDK Example: List activities of day</title>
</head>
<body>
    <h2>List of activities on 29th of january, 2013 (29-01-2013)</h2>
    
    <?php
        // parse the activities of given day, 29-01-2013
        $activities = $lectio->getActivities('29-01-2013');
        
        $amountOfModules = sizeof($activities);
        
        // echo module informations
        echo "Amount of modules: ".$amountOfModules;
        
        for ($i = 0; $i < $amountOfModules; $i++){
            if ($i == 0){
                echo "<dl>";
            }
            
            $htmlStr = <<<HTML__
<dt><b>{$activities[$i]['time']}</b></dt>
    <dd>State: {$activities[$i]['state']}</dd>
    <dd>Class: {$activities[$i]['class']}</dd>
    <dd>Homework: {$activities[$i]['homework']}</dd>
        
<br>
HTML__;
            echo $htmlStr;
        }
        echo "</dl>";
    ?>
</body>
</html>
