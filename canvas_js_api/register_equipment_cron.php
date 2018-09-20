<?php

require_once('/var/www/html/includes/php/crontab/common_cron.php') ;

// Connect to db
$dbc = db_connect_sims() ;
$dbc_local = db_connect_local() ;

// Construct an array of all the date ranges from Table
$date_range = array() ;
$query3 = "SELECT date_range.id AS daterange_id, daterange FROM date_range" ;
$result3 = mysqli_query($dbc_local, $query3) or trigger_error("Query: $query3\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
while ($row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC))
{
  // store the date range for multi equipment use
  $date_range[] = $row3['daterange_id'] ;
}


// First Get a list of all the equipments
$query = "SELECT equipement.id AS equipment_id
          FROM equipement"
          . " ORDER BY equipment_id ASC" ; // LIMIT 1 for testing
$result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc)) ;
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
{
  $equipment_id = $row['equipment_id'] ;
  // Check if records of CanvasJS exists in the local DB
  // If not create them
  $query2 = "SELECT canvas_js_equipment.id AS canvasjs_id
        FROM canvas_js_equipment
        WHERE equipment_id = " . $equipment_id ;
  $result2 = mysqli_query($dbc_local, $query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
  if (mysqli_num_rows($result2) == 0)
  {
    // If no records populate it with empty CanvasJS fields
    //$row2 = mysqli_fetch_array($result2, MYSQLI_NUM) ;
    // Use the date range data to create new empty records for this equipment using INSERT
    foreach ($date_range as $dr) 
    {
      //$players_flattened[] = $p['player'] ;
      // Insert all the date range records for this equipment
      $query4 = "INSERT INTO canvas_js_equipment (equipment_id, daterange_id) VALUES (" . $equipment_id . ", " . $dr . "); " ;
      $result4 = mysqli_query($dbc_local, $query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
      
    }
    
  }
  else
  {
    // Complete the ones that are missing for that equipment! (In case more date ranges are added....)
    foreach ($date_range as $dr)
    {
      $query5 = "SELECT canvas_js_equipment.id AS canvasjs_id
            FROM canvas_js_equipment
            WHERE equipment_id = " . $equipment_id
            . " AND daterange_id = " . $dr ;
      $result5 = mysqli_query($dbc_local, $query5) or trigger_error("Query: $query5\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
      if (mysqli_num_rows($result5) == 0)
      {
        $query4 = "INSERT INTO canvas_js_equipment (equipment_id, daterange_id) VALUES (" . $equipment_id . ", " . $dr . "); " ;
        $result4 = mysqli_query($dbc_local, $query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
      }
    }
    
  }
  
}

// Close db
db_close($dbc) ;
db_close($dbc_local) ;

echo "Register OK; \n" ;

?>
