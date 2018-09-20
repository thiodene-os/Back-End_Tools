<?php

// Main libraries for Chart features

// Builds and returns the Top Input Fields (Dates, Sensors Update buttons etc...)
function buildTopInputFields($equipment, $date_range, $begin_date, $end_date)
{
  // Build the top input bar
  $final_top_input = "" ;
  
  // Connect to db
  $dbc = db_connect_sims() ;
  
  // If both Begin and End date submitted don't display Last: '1 Week' value
  if (strlen($begin_date) > 0 && strlen($end_date) > 0)
    $dates_submitted = true ;
  else
    $dates_submitted = false ;
  
  // Get all the Scentinals and the respective company name and build the top input select field
	$query = "SELECT equipement.id AS equip_id, equipement.name AS equip_name
            , equipement.company AS company_id
            , company.name AS company_name
            FROM equipement 
            INNER JOIN company ON equipement.company = company.id 
            ORDER BY equipement.id DESC" ;
	$result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc)) ;
	if (@mysqli_num_rows($result) != 0) 
  {
    $final_top_input .= "<form method=\"get\" action=\"index.php\">
                  <label>Scentinal:</label>
                  <select class=\"input_select\" name=\"equipment\">" ;
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) 
    {
      $equipement_id = $row['equip_id'] ;
      $equipement_name = $row['equip_name'] ;
      $company_id = $row['company_id'] ;
      $company_name = $row['company_name'] ;
      
      // Build the option fields
      if ($equipement_id == $equipment)
        $final_top_input .= "<option value=\"" . $equipement_id . "\" selected>" . $equipement_name . " -:- " . $company_name . "</option>";
      else
        $final_top_input .= "<option value=\"" . $equipement_id . "\">" . $equipement_name . " -:- " . $company_name .  "</option>";

		}
    $final_top_input .= "</select>" ;
	}
  
  $date_interval = buildIntervalDateSelectInput($dates_submitted, $date_range) ;
  
  $final_top_input .= "
                  &emsp;<label>Last:</label> $date_interval
                  &emsp; <label>Begin Date:</label><input type=\"text\" id=\"begin_date\" class=\"input_date\" name=\"begin_date\" value=\"$begin_date\" />  
                  <label>End Date:</label><input type=\"text\" id=\"end_date\" class=\"input_date\" name=\"end_date\" value=\"$end_date\" />
                  <button class=\"button_update\">Update</button></form>" ;
                  
  // Close db
  db_close($dbc) ;
  
  return $final_top_input ;
} // buildTopInputFields 


function calcDateRangeFromLastSample($equipment)
{
  // Connect to db
  $dbc = db_connect_sims() ;
  
  // Get the last sample measured by that Equipment
  $query = "SELECT sample.id AS sample_id, sample.sampledat
        FROM sample
        WHERE equipement = " . $equipment 
        . " ORDER BY sampledat DESC LIMIT 1";
  $result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc));
  $row = mysqli_fetch_array($result, MYSQLI_NUM) ;
  
  $last_sample_time = $row[1] ;
  
  $test_date_range = strtotime("-1 week") ;
  
  // Close db
  db_close($dbc) ;
  
  return $new_date_range ;
} // calcDateRangeFromLastSample




// Builds and returns the Interval Dates as a select input
function buildIntervalDateSelectInput($dates_submitted, $date_range= false)
{
  // Build the top input bar
  $select_interval = "<select class=\"input_select date_range\" name=\"date_range\">" ;
  
  // Hardcode it for now
  $select_interval .= "<option value=\"\">----</option>";

  if ($date_range == "current")
    $select_interval .= "<option value=\"current\" selected>Current</option>";
  else if (!$date_range && !$dates_submitted)
    $select_interval .= "<option value=\"current\" selected>Current</option>";
  else
    $select_interval .= "<option value=\"current\">Current</option>";

  if ($date_range == "24 hours")
    $select_interval .= "<option value=\"24 hours\" selected>24 Hours</option>";
  else
    $select_interval .= "<option value=\"24 hours\">24 Hours</option>";
  
  if ($date_range == "48 hours")
    $select_interval .= "<option value=\"48 hours\" selected>48 Hours</option>";
  else
    $select_interval .= "<option value=\"48 hours\">48 Hours</option>";
  
  if ($date_range == "72 hours")
    $select_interval .= "<option value=\"72 hours\" selected>72 Hours</option>";
  else
    $select_interval .= "<option value=\"72 hours\">72 Hours</option>";
  
  if ($date_range == "1 week")
    $select_interval .= "<option value=\"1 week\" selected>1 Week</option>";
  else
    $select_interval .= "<option value=\"1 week\">1 Week</option>";
  
  if ($date_range == "2 weeks")
    $select_interval .= "<option value=\"2 weeks\" selected>2 Weeks</option>";
  else
    $select_interval .= "<option value=\"2 weeks\">2 Weeks</option>";
  
  if ($date_range == "1 month")
    $select_interval .= "<option value=\"1 month\" selected>1 Month</option>";
  else
    $select_interval .= "<option value=\"1 month\">1 Month</option>";
  
  if ($date_range == "3 months")
    $select_interval .= "<option value=\"3 months\" selected>3 Months</option>";
  else
    $select_interval .= "<option value=\"3 months\">3 Months</option>";
  /*
  if ($date_range == "6 months")
    $select_interval .= "<option value=\"6 months\" selected>6 Months</option>";
  else
    $select_interval .= "<option value=\"6 months\">6 Months</option>";

  if ($date_range == "1 year")
    $select_interval .= "<option value=\"1 year\" selected>1 Year</option>";
  else
    $select_interval .= "<option value=\"1 year\">1 Year</option>";

  if ($date_range == "2 years")
    $select_interval .= "<option value=\"2 years\" selected>2 Years</option>";
  else
    $select_interval .= "<option value=\"2 years\">2 Years</option>";
  
  if ($date_range == "5 years")
    $select_interval .= "<option value=\"All\" selected>All</option>"; 
  else
    $select_interval .= "<option value=\"All\">All</option>"; 
  
  */
  
  $select_interval .= "</select>" ;

  return $select_interval ;
} // buildIntervalDateSelectInput 

// Builds and returns Charts for Meteorological Data
function displayMetChart($chart_number)
{

  // Add the chartContainer DIV
  $final_met_chart = "  <div id=\"chartContainer" . $chart_number . "\" style=\"height: 400px; width: 100%; float:right;\">
  </div>" ;

  return $final_met_chart ;
} // buildMetChart

// Builds and returns Charts for Meteorological Table
function buildMetTable($equipment, $chart_number)
{

  // Connect to db
  $dbc = db_connect_sims() ;
  
  // Add the frozen table to be replaced asap
  $final_met_table = "
  <table class=\"scroll met_data\">
  <thead>
  <tr>
  <th>Parameter</th>
  <th>Value</th>
  <th>Data Unit</th>
  <th>Updated</th>
  <th>&nbsp;</th>
  </tr>
  </thead>" ;

  // Get all the Met sensor (Not PPM or PPB for now) for the equipement 86
  $plot_number = 0 ;
	$query = "SELECT sensor.id AS sensor_id, sensor.name AS sensor_name
            , sensor.dataunit AS sensor_dataunit
            FROM sensor
            WHERE equipement = " . $equipment 
              . " AND dataunit NOT IN ('PPM','PPB')"
              . " AND sensor.id IN (SELECT sensor FROM sample)"
            . " ORDER BY sensor.id ASC" ;            ;
	$result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc));
	if (@mysqli_num_rows($result) != 0) 
  {
    
    $final_met_table .= "<tbody>" ;
    
    $naxis = 0 ;
    $nsensor = 0 ;
    $sensor_arr = array() ;
    $axisy = '' ;
    $dataunit_arr = array() ;
    $dataunit_str = ' ' ;
    list ($naxis_full, $naxis_half) = calcNumberOfYAxesCanvasJS($equipment) ;
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) 
    {
      $sensor_id = $row['sensor_id'] ;
      $sensor_name = $row['sensor_name'] ;
      $sensor_dataunit = $row['sensor_dataunit'] ;
      if (strlen($sensor_dataunit) == 0)
        $sensor_dataunit = 'uu' ;
      
      // register the dataunit
      if (substr_count($dataunit_str, $sensor_dataunit) == 0)
      {
        $dataunit_arr[$naxis] = $sensor_dataunit ;
        $sensor_arr[$sensor_name] = array_search($sensor_dataunit, $dataunit_arr) ;
        $dataunit_str .= $sensor_dataunit ;
        
        if ($naxis_half > 0)
        {
          // If more than 4 build scales on the other side Y2
          if ($naxis <= $naxis_half - 1)
          {
            $axisyindex = array_search($sensor_dataunit, $dataunit_arr) ;
            $axisytype = "primary";
          }
          else
          {
            $axisyindex = array_search($sensor_dataunit, $dataunit_arr) - $naxis_half ;
            $axisytype = "secondary";
          }
        }
        else
        {
          $axisyindex = array_search($sensor_dataunit, $dataunit_arr) ;
          $axisytype = "primary";
        }
        $naxis++;
      }
      else
      {
        if ($naxis_half > 0)
        {
          // If more than 4 build scales on the other side Y2
          if ($naxis <= $naxis_half - 1)
          {
            $axisyindex = array_search($sensor_dataunit, $dataunit_arr) ;
            $axisytype = "primary";
          }
          else
          {
            $axisyindex = array_search($sensor_dataunit, $dataunit_arr) - $naxis_half ;
            $axisytype = "secondary";
          }
        }
        else
        {
          $axisyindex = array_search($sensor_dataunit, $dataunit_arr) ;
          $axisytype = "primary";
        }
      }
      $nsensor++;
      
      
      
      
      // Get the last sample measured by that Sensor
	    $query2 = "SELECT sample.id AS sample_id, sample.value AS sample_value, sampledat
            FROM sample
            WHERE equipement = " . $equipment 
            . " AND sensor = " . $sensor_id 
            . " ORDER BY sampledat DESC LIMIT 1" ;
      $result2 = mysqli_query($dbc, $query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysqli_error($dbc));
      $row2 = mysqli_fetch_array($result2, MYSQLI_NUM) ;
      $last_sample_value = $row2[1] ;
      $last_sample_time = timeElapsedString($row2[2]) ;
      
      $final_met_table .= "<tr><td>" . $sensor_name . "</td><td>" . $last_sample_value . "</td><td>" . $sensor_dataunit . "</td><td>" . $last_sample_time 
                       . "<td><span class=\"option selectable remove\" chart=\"" . $chart_number . "\" plot_number=\"" . $plot_number 
                       . "\" series=\"" . $plot_number . "\">PLOT<span class=\"check_plot\"><i class=\"fa fa-check fa-2x\"></i></span></span>"
                       . "<span class=\"option selectable add\" chart=\"" . $chart_number . "\" plot_number=\"" . $plot_number 
                       . "\" series=\"" . $plot_number . "\" sensor_name=\"" . $sensor_name . "\" axisyindex=\"" . $axisyindex 
                       . "\" axisytype=\"" . $axisytype . "\">PLOT</span></td>"
                       . "</tr>";
      $plot_number++ ;
		}
    $final_met_table .= "</tbody>" ;
	}


  $final_met_table .= "

</table>" ;

  // Close db
  db_close($dbc) ;

  return $final_met_table ;
} // buildMetTable

// Builds and returns Charts for Sensor Data
function displaySensorChart($chart_number)
{

  // Add the chartContainer DIV
  $final_sensor_chart = "  <div id=\"chartContainer" . $chart_number . "\" style=\"height: 400px; width: 100%;\">
  </div>" ;
  
  return $final_sensor_chart ;
} // buildSensorChart

// Builds and returns Table for Sensor Data
function buildSensorTable($equipment, $chart_number)
{

  // Connect to db
  $dbc = db_connect_sims() ;

  // Add the frozen table to be replaced asap
  $final_sensor_table = "
  <table class=\"scroll sensor_data\">
  <thead>
  <tr>
  <th>Sensors</th>
  <th>Concentration</th>
  <th>Updated</th>
  <th>&nbsp;</th>
  </tr>
  </thead>" ;

  // Get all the Met sensor (Not PPM for now) for the equipement 86
  $plot_number = 0 ;
	$query = "SELECT sensor.id AS sensor_id, sensor.name AS sensor_name
            , sensor.dataunit AS sensor_dataunit
            FROM sensor
            WHERE equipement = " . $equipment 
            . " AND dataunit IN ('PPM','PPB')"
            . " AND sensor.id IN (SELECT sensor FROM sample)"
            . " ORDER BY sensor.id ASC" ;
	$result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc));
	if (@mysqli_num_rows($result) != 0) 
  {
    $final_sensor_table .= "<tbody>" ;
    $axisytype = "primary";
    $axisyindex = 0 ;
    
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
      $sensor_id = $row['sensor_id'] ;
      $sensor_name = $row['sensor_name'] ;
      $sensor_dataunit = $row['sensor_dataunit'] ;
      
      // Get the last sample measured by that Sensor
	    $query2 = "SELECT sample.id AS sample_id, sample.value AS sample_value, sampledat
            FROM sample
            WHERE equipement = " . $equipment 
            . " AND sensor = " . $sensor_id
            . " ORDER BY sampledat DESC LIMIT 1";
      $result2 = mysqli_query($dbc, $query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysqli_error($dbc));
      $row2 = mysqli_fetch_array($result2, MYSQLI_NUM) ;
      $last_sample_value = $row2[1] ;
      $last_sample_time = timeElapsedString($row2[2]) ;
      
      $final_sensor_table .= "<tr><td>" . $sensor_name . "</td><td>" . $last_sample_value . "</td><td>" . $last_sample_time 
                       . "<td><span class=\"option selectable remove\" chart=\"" . $chart_number . "\" plot_number=\"" . $plot_number 
                       . "\" series=\"" . $plot_number . "\">PLOT<span class=\"check_plot\"><i class=\"fa fa-check fa-2x\"></i></span></span>"
                       . "<span class=\"option selectable add\" chart=\"" . $chart_number . "\" plot_number=\"" . $plot_number 
                       . "\" series=\"" . $plot_number . "\" sensor_name=\"" . $sensor_name . "\" axisyindex=\"" . $axisyindex 
                       . "\" axisytype=\"" . $axisytype . "\">PLOT</span></td>"
                       . "</tr>";
      $plot_number++ ;
		}
    $final_sensor_table .= "</tbody>" ;
	}

  $final_sensor_table .= "

</table>" ;

  // Close db
  db_close($dbc) ;

  return $final_sensor_table ;
} // buildSensorTable

// Builds and returns JS data for each sensors of the equipement
function getDbSensorDataCanvasJS($equipment, $date_range, $met = false)
{
  // Connect to db
  $dbc_local = db_connect_local() ;
  
  // First get the Date range ID from local connection
  $query = "SELECT date_range.id AS daterange_id FROM date_range WHERE daterange = '" . $date_range . "'" ;
  $result = mysqli_query($dbc_local, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
  $row = mysqli_fetch_array($result, MYSQLI_NUM) ;

  $daterange_id = $row[0] ;
  
  if ($met)
  {
    // Now get the data from the local DB for the equipment
    $query2 = "SELECT canvas_js_equipment.id AS canvasjs_id, chart_container_met, series_plot_met 
               FROM canvas_js_equipment
               WHERE daterange_id = " . $daterange_id
               . " AND equipment_id = " . $equipment ;
  }
  else
  {
    // Now get the data from the local DB for the equipment
    $query2 = "SELECT canvas_js_equipment.id AS canvasjs_id, chart_container, series_plot 
               FROM canvas_js_equipment
               WHERE daterange_id = " . $daterange_id
               . " AND equipment_id = " . $equipment ;
  }

  $result2 = mysqli_query($dbc_local, $query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
  $row2 = mysqli_fetch_array($result2, MYSQLI_NUM) ;
  
  $final_sensor_chart_js = $row2[1] ;
  $final_sensor_series_js = $row2[2] ;
  
  // Close db
  db_close($dbc_local) ;

  return array($final_sensor_chart_js, $final_sensor_series_js) ;
  
} // getDbSensorDataCanvasJS

// Builds and returns JS data for each sensors of the equipement
function buildSensorDataCanvasJS($equipment, $begin_date, $end_date, $chart_number, $met = false)
{
  // Connect to db
  $dbc = db_connect_sims() ;
  
  // Get the equipment Name
  $query4 = "SELECT equipement.name FROM equipement
            WHERE id = " . $equipment;
  $result4 = mysqli_query($dbc, $query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysqli_error($dbc));
  $row4 = mysqli_fetch_array($result4, MYSQLI_NUM) ;
  $equipment_name = $row4[0] ;
  
  // Initialize the CanvasJS values
  $final_sensor_chart_js = '' ;
  $final_sensor_series_js = '' ;
  
  // Add the condition for Met data
  if ($met)
  {
    $sign = 'NOT' ;
    $naxis = 0 ;
    $nsensor = 0 ;
    $sensor_arr = array() ;
    $axisy = '' ;
    $dataunit_arr = array() ;
    $dataunit_str = ' ' ;
    list ($naxis_full, $naxis_half) = calcNumberOfYAxesCanvasJS($equipment) ;
    // Go through each different dataunit and build a scale
    // First get all the different MET sensors from this equipement ordered!
    $query3 = "SELECT sensor.id AS sensor_id, sensor.name AS sensor_name
              , sensor.dataunit AS sensor_dataunit
              FROM sensor
              WHERE equipement = " . $equipment 
              . " AND dataunit " . $sign . " IN ('PPM','PPB')"
              . " ORDER BY sensor.id ASC" ;
    $result3 = mysqli_query($dbc, $query3) or trigger_error("Query: $query3\n<br>MySQL Error: " . mysqli_error($dbc));
    while ($row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC))
    {
      $sensor_dataunit = $row3['sensor_dataunit'] ;
      if (strlen($sensor_dataunit) == 0)
        $sensor_dataunit = 'uu' ;
      $sensor_name =  $row3['sensor_name'] ;
      
      // register the dataunit
      if (substr_count($dataunit_str, $sensor_dataunit) == 0)
      {
        $dataunit_arr[$naxis] = $sensor_dataunit ;
        $sensor_arr[$sensor_name] = array_search($sensor_dataunit, $dataunit_arr) ;
        $dataunit_str .= $sensor_dataunit ;
        
        if ($naxis_half > 0)
        {
          // If more than 4 build scales on the other side Y2
          if ($naxis <= $naxis_half - 1)
          {
            if ($naxis == 0)
              $axisy .= '		axisY:[';
            $axisy .= '{
              title: "' . $sensor_dataunit . '",
              titleFontSize: 14,
              lineColor: "#000000",
              titleFontColor: "#000000",
              labelFontColor: "#000000"
            }' ;
            if ($naxis == $naxis_half - 1)
              $axisy .= '],' ;
            else
              $axisy .= ',' ;
              
          }
          else
          {
            if ($naxis == $naxis_half)
              $axisy .= '
            axisY2:[';
            $axisy .= '{
              title: "' . $row3['sensor_dataunit'] . '",
              titleFontSize: 14,
              lineColor: "#000000",
              titleFontColor: "#000000",
              labelFontColor: "#000000"
            }' ;
            if ($naxis == $naxis_full - 1)
              $axisy .= '],' ;
            else
              $axisy .= ',' ;
          }
        } 
        else
        {
          if ($naxis == 0)
            $axisy .= '		axisY:[';
          $axisy .= '{
            title: "' . $sensor_dataunit . '",
            titleFontSize: 14,
            lineColor: "#000000",
            titleFontColor: "#000000",
            labelFontColor: "#000000"
          }' ;
          if ($naxis == $naxis_full - 1)
            $axisy .= '],' ;
          else
            $axisy .= ',' ;
        }
        $naxis++;
      }
      else
        $sensor_arr[$sensor_name] = array_search($sensor_dataunit, $dataunit_arr) ;
      $nsensor++;
    }
  }
  else
  {
    $sign = '' ;
    // Select the type of Gas data Unit (To update asap!)
    $query4 = "SELECT sensor.id AS sensor_id, sensor.dataunit AS sensor_dataunit
              FROM sensor
              WHERE equipement = " . $equipment 
              . " AND dataunit " . $sign . " IN ('PPM','PPB')"
              . " LIMIT 1" ;
    $result4 = mysqli_query($dbc, $query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysqli_error($dbc));
    $row4 = mysqli_fetch_array($result4, MYSQLI_NUM) ;
    $sensor_dataunit = $row4[1] ;
    
    $axisy = '		axisY:[{
			title: "' . $sensor_dataunit . '",
      titleFontSize: 14,
			lineColor: "#000000",
			titleFontColor: "#000000",
			labelFontColor: "#000000"
		}],' ;
  }
  
  // First get all the different sensors from this equipement ordered!
	$query = "SELECT sensor.id AS sensor_id, sensor.name AS sensor_name
            , sensor.dataunit AS sensor_dataunit
            FROM sensor
            WHERE equipement = " . $equipment 
            . " AND dataunit " . $sign . " IN ('PPM','PPB')"
            . " ORDER BY sensor.id ASC" ;
	$result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc));
	if (@mysqli_num_rows($result) != 0) 
  {
    // Construct the chart container for each sensors
    $final_sensor_chart_js .= "  
    chart[$chart_number] = new CanvasJS.Chart(\"chartContainer$chart_number\",
    {
      title:{
        text: \"$equipment_name\"
      },
      $axisy
      data: [
      " ;
    
    // Construct the plot series for the Chart to be displayed
    $final_sensor_series_js .= " if (chart_number == $chart_number)
      {
    " ;
    $plot_series = 0;
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
      // Handle the date interval
      //$sql_date_interval = strtotime("-1 week") ;
      
      // Default Begin date (1 week ago)
      if ($begin_date == '')
        $begin_timestamp = strtotime("-1 week") ;
      else
        $begin_timestamp = strtotime($begin_date) ;
      // Default End date (Now)
      if ($end_date == '')
        $end_timestamp = strtotime("now") ;
      else
        $end_timestamp = strtotime($end_date) ;
      
      $sensor_id = $row['sensor_id'] ;
      $sensor_name = $row['sensor_name'] ;
      // Now get all the sample value for the sensors and build the CanvasJS data
      // Get the values and timestamp to build the CanvasJS data
      $query2 = "SELECT sample.id AS sample_id, sample.value, sample.sampledat, sensor.name
                FROM sample
                INNER JOIN sensor ON sample.sensor = sensor.id
                WHERE sample.equipement = " . $equipment 
                . " AND sample.sensor = " . $sensor_id
                . " AND sampledat >= " . $begin_timestamp
                . " AND sampledat <= " . $end_timestamp
                . " ORDER BY sampledat ASC" ;
      $result2 = mysqli_query($dbc, $query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysqli_error($dbc));
      if (@mysqli_num_rows($result2) != 0) 
      {
        if ($met)
        {
          $nyindex = $sensor_arr[$sensor_name] ;
          if ($naxis_half > 0)
          {
            if ($nyindex < $naxis_half)
            {
              $nyindex = $nyindex ;
              $axisytype = '' ;
            }
            else
            {
              $nyindex = $nyindex - $naxis_half ;
              $axisytype = 'axisYType: "secondary",
            ' ;
            }
          }
          else
          {
              $nyindex = $nyindex ;
              $axisytype = '' ;
          }
          
          $final_sensor_chart_js .= "     {        
          type: \"spline\",
          name: \"$sensor_name\",
          showInLegend: true,
          axisYIndex: " . $nyindex . ",
          "
          . $axisytype . 
          "lineThickness: 1,
          xValueType: \"dateTime\",
          dataPoints: [" ;
        }
        else
        {
          $final_sensor_chart_js .= "     {        
          type: \"spline\",
          name: \"$sensor_name\",
          showInLegend: true,
          lineThickness: 1,
          xValueType: \"dateTime\",
          dataPoints: [" ;
        }
        
        // Construct the plot series for the Chart to be displayed
        if ($plot_series > 0)
          $final_sensor_series_js .= " else" ;
        $final_sensor_series_js .= " if (series_to_plot == $plot_series)
        {" ;
        $plot_series++ ;
        while ($row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC))
        {
          $milli_timestamp = $row2['sampledat'] * 1000 ;
          // Verify that the value is numeric as the SQL field has been defined as TEXT!!!!!!!
          if (is_numeric($row2['value']))
            $sample_value = $row2['value'] ;
          else
            $sample_value = 0;
          
          // Add each data samples to the CanvasJS files
          $final_sensor_chart_js .= '{ x: ' . $milli_timestamp . ', y: ' . $sample_value . ' },';
          $final_sensor_series_js .= 'dataPoints.push({ x: ' . $milli_timestamp . ', y: ' . $sample_value . ' });' ;
          
        }
        
        $final_sensor_chart_js .= " 
        ]
      }," ;
        
        $final_sensor_series_js .= " 
        }" ;
        
      }
      
      
    }
    
    $final_sensor_chart_js .= "
    ]
  });
  
  chart[$chart_number].render();" ;
    
    $final_sensor_series_js .= " 
    }" ;
    
  }
  
  // Close db
  db_close($dbc) ;

  return array($final_sensor_chart_js, $final_sensor_series_js) ;
  
} // buildSensorDataCanvasJS

// Calculate the number of total Y-Axes for Met data in one equipment and the half displayed on the primary (left side)
// Number of axes equal the number of data units
function calcNumberOfYAxesCanvasJS($equipment)
{
  
  // Connect to db
  $dbc = db_connect_sims() ;
  
  $dataunit_arr = array() ;
  $ndataunit = 1 ;
  // First get all the different dataunits of Met sensors!
  $query = "SELECT sensor.id AS sensor_id, sensor.dataunit AS sensor_dataunit
            FROM sensor
            WHERE equipement = " . $equipment 
            . " AND dataunit NOT IN ('PPM','PPB')"
            . " ORDER BY sensor.id ASC" ;
  $result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc));
  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
  {
    // Go through each sensors and register each different data units
    $sensor_dataunit = $row['sensor_dataunit'] ;
    $is_registered = array_search($sensor_dataunit, $dataunit_arr) ;
    // If is not already registered enter it!
    if (!$is_registered)
    {
      $dataunit_arr[$ndataunit] = $sensor_dataunit ;
      $ndataunit++ ;
    }
  }
  
  // Calculate final Yaxis values (If more than 4 Data unit split in half if less or equals 4 display all the Y axes on the left side)
  $naxis_full = $ndataunit - 1 ;
  if ($naxis_full > 4)
    $naxis_half = ceil($naxis_full / 2) ;
  else
    $naxis_half = 0 ;
  
  // Close db
  db_close($dbc) ;
  
  return array($naxis_full, $naxis_half) ;
  
} // calcNumberOfYAxesCanvasJS

?>
