<?php
include '../common/common_include.php';

//$fplan_id = '613' ;
$event_id = '10092' ;

list ($event_guests,$fplan_guests) = getNumberOfFloorPlanGuests($event_id) ;
echo 'Event Guests: ' . $event_guests -> total . ', Flor Plan Guests: ' . $fplan_guests -> total ;

?>
