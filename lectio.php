<?php
/**
* Lectio PHP SDK
*
* Provides a SDK for accessing Lectio
* Usage:
*   $lectio = new lectio("SCHOOL_ID", "STUDENT_ID");
*   $activitiesArray = $lectio->getActivities("dd-mm-yyyy");
*/

include_once('simple_html_dom.php');

class lectio{
    private $schoolid = null;
    private $studentid = null;
 
    /**
    * Class Constructor
    *
    * Construct the class and assign the id of the school and student
    * @param string $schoolid The id of the school
    * @param string $studentid The id of the student
    */
    public function __construct($schoolid = null, $studentid = null){
        $this->schoolid = $schoolid;
        $this->studentid = $studentid;
    }
    
    /**
    * Get activities by date
    * 
    * Retrieves the activities of a day as an array with the following information: state, time, homework and class
    * @param string $date The date of the day you want the activities extracted from. The format of the date is dd-mm-yyyy. If this is null, then it retrieves the activities of the current day.
    */ 
    public function getActivities($date = null){
        // dd-mm-yyyy
        if ($date == null){
            $date = time();
        } else {
            $date = strtotime($date);
        }
        
        $dayOfWeek = date("w", $date);
        $parsedActivites = array();
        
        $html = $this->getTimetable($this->getWeekYear($date));
        
        if ($html){
            $timetable = $html->find('.s2skema', 0);
            
            foreach($timetable->find('tr') as $tr){
                $tdIndex = 0;
                
                foreach  ($tr->find('td') as $td){
                    if ($tdIndex == $dayOfWeek){
                        foreach ($td->find('a') as $link){
                            if ($link->href != null){
                                $html = file_get_html('https://www.lectio.dk'.$link->href);
                                array_push($parsedActivites, $this->parseActivity($html));
                            }
                        }
                    }
                    $tdIndex++;
                    
                }
            }
        }
        
        return $parsedActivites;
    }
    
    /**
    * Parse activity from HTML
    * 
    * Parses activity information from given HTML-page and returns an array.
    * @param string $html The HTML page of the timetable
    */ 
    public function parseActivity($html){
        $div = $html->find('.islandContent', 0);
        $table = $div->find('table', 0);
        
        $time = null;
        $class = null;
        $note = null;
        $homework = null;
        $status = null;
        
        foreach ($table->find('tr') as $tr){
            $th = $tr->find('th', 0);
            $td = $tr->find('td', 0);
            
            if ($th != null && $td != null){
                $thText = $th->text();
                $tdText = $td->text();
                
                if ($thText == "Tidspunkt:"){
                    $time = $this->sanitize($tdText);
                } else if ($thText == "Hold:"){
                    $class = $this->sanitize($td->find('a', 0)->innertext);
                } else if ($thText == "Note:"){
                    $note = $this->sanitize($tdText);
                } else if ($thText == "Lektier:"){
                    $homework = $this->sanitize($tdText);
                } else if ($thText == "Status:"){
                    $status = $tdText;
                }
            }
        }
        
        $homeworkBundle = null;
        
        if ($homework != null && strlen($homework) > 0){
            $homeworkBundle = $homework;
        }
        
        if ($note != null && strlen($note) > 0){
            if (strlen($homeworkBundle) > 0){
                $homeworkBundle = $homeworkBundle . " - ";
            }
            
            $homeworkBundle = $homeworkBundle . $note;
        }
        
        return array('state'=>$status, 'time'=>$time, 'homework'=>$homeworkBundle, 'class'=>$class);
    }
    
    /**
    * Get timetable
    * 
    * Retrieve the HTML of the timetable
    * @param string $week The week of the timetable
    */
    public function getTimetable($week = null){
        $htmlContent = file_get_html(sprintf("https://www.lectio.dk/lectio/%s/SkemaNy.aspx?type=elev&elevid=%s&week=%s", $this->schoolid, $this->studentid, $week));
        return $htmlContent;
    }
    
    /**
    * Sanitize parsed information
    * 
    * Cleans up the parsed code so that it is pure text
    * @param string $html HTML of the activity
    */ 
    public function sanitize($html){
        $spacesCollapsed = mb_ereg_replace('(\s){2,}', ' ', $html);
        $breaksRemoved = mb_ereg_replace('<br />', '', $spacesCollapsed);
        $entitiesDecoded = html_entity_decode($breaksRemoved, ENT_NOQUOTES, 'UTF-8');
        return trim($entitiesDecoded); 
    }
    
    /**
    * Get week number and year
    * 
    * Retrieves a date in the format of ww-yyyy
    * @param string $date The date of the activity
    */ 
    public function getWeekYear($date){
        $weekYear = "";
        
        $week = (int)date('W', $date);
        $year = (int)date('Y', $date);
        
        if ($week < 10){
            $weekYear = "0".$week;
        } else {
            $weekYear = $week;
        }
        
        $weekYear = $weekYear.$year;
        
        return $weekYear;
    }
}
?>
