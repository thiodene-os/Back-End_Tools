// Get the number of Floor Plan guest once the Floor Plan has been started/saved
// for comparison with the Event number of guests initially registered
function getNumberOfFloorPlanGuests($event_id)
{
  // Get the floor Plan record and the respective Event ID
  // change this query to select is users have many Floor Plans
  $fplan_rec = lookupRecordById("FLOOR_PLAN","LNK_EVENT",$event_id,"ACTUAL_PLAN_XML") ;

  // Get the saved floor plan and loop through all elements on it to be later added
  // on screen
  $fplan_xml = trim($fplan_rec['ACTUAL_PLAN_XML']) ; 
  
  $fplan_adults = 0 ;
  $fplan_kids = 0 ;
  $fplan_babies = 0 ;
  if (! empty($fplan_xml))
  {
    $fplan_root = buildXMLRoot($fplan_xml) ;
    $fplan_nodes = $fplan_root -> childNodes ;
    foreach($fplan_nodes as $fplan_node)
    {
      if ($fplan_node -> nodeName != "circle" && $fplan_node -> nodeName != "rect")
        continue ; // Only circle and rect are table
      $fplan_adults += getElementContent($fplan_node,"adults") ;  
      $fplan_kids += getElementContent($fplan_node,"children") ;  
      $fplan_babies += getElementContent($fplan_node,"babies") ;  
    } //  Each node on floor plan
  }
  
  // Sum all the Floor Plan guests
  $fplan_guests = new stdClass() ;
  $fplan_guests -> adults = $fplan_adults ;
  $fplan_guests -> kids = $fplan_kids ;
  $fplan_guests -> babies = $fplan_babies ;
  $fplan_guests -> total = $fplan_adults + $fplan_kids + $fplan_babies ;

  // If needed get the Event total number of guests for comparison
  $event_rec = lookupRecordById("EVENT","UID",$event_id,"ADULTS,KIDS,BABIES") ;
  
  // Sum all the Floor Plan guests
  $event_guests = new stdClass() ;
  $event_guests -> adults = $event_rec['ADULTS'] ;
  $event_guests -> kids = $event_rec['KIDS'] ;
  $event_guests -> babies = $event_rec['BABIES'] ;
  $event_guests -> total = $event_rec['ADULTS'] + $event_rec['KIDS'] + $event_rec['BABIES'] ;
  
  // Return both values
  return array ($event_guests,$fplan_guests) ;
  
} // getNumberOfFloorPlanGuests
