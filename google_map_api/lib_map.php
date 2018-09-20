<?php

// Main libraries for Mapping features

// Builds and returns the Main map
function buildMainMap()
{

  // Add an image first for debug and change asap
  //$final_map = "
//<img class=\"main_map\" src=\"images/heatmapapi.png\" />" ;

  $final_map = "
<div id=\"map\"></div>" ;

  return $final_map ;
} // buildMainMap

// Builds and returns the Geo position of the equipment
function buildGeoPosition($equipment)
{

  // Connect to db
  $dbc = db_connect_sims() ;

  // Get the last position of the scentinel
  $query = "SELECT sample.id AS sample_id, sample.lat, sample.lon
        FROM sample
        WHERE equipement = " . $equipment
        . " ORDER BY sampledat DESC LIMIT 1" ;
  $result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc));
  $row = mysqli_fetch_array($result, MYSQLI_NUM) ;
  $lat = $row[1] ;
  $lon = $row[2] ;
  
  $final_geoposition = '{lat: ' . $lat . ', lng: ' .  $lon . '};' ;
  
  // Close db
  db_close($dbc) ;
  
  return $final_geoposition ;
} //buildGeoPosition


// Builds and returns polygons for Wind speed and direction
function buildWindPolygon($equipment)
{

  // Connect to db
  $dbc = db_connect_sims() ;
  
  $wind_speed = 'wind speed' ;
  $wind_direction = 'wind direction' ;
  $nsensor = 0 ;

  // First make sure this equipement has wind strength and direction sensors!
  // Based on their Unit: "degree" and "m/s"
  $query = "SELECT sensor.id AS sensor_id
        FROM sensor
        WHERE equipement = " . $equipment
        . " AND name = '" . $wind_speed . "'" ;
  $result = mysqli_query($dbc, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc));
  if (mysqli_num_rows($result) > 0)
  {
    $row = mysqli_fetch_array($result, MYSQLI_NUM) ; 
    $speed_sensor_id = $row[0] ;
    $nsensor++ ;
  }
  
  $query2 = "SELECT sensor.id AS sensor_id
        FROM sensor
        WHERE equipement = " . $equipment
        . " AND name = '" . $wind_direction . "'" ;
  $result2 = mysqli_query($dbc, $query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysqli_error($dbc));
  if (mysqli_num_rows($result2) > 0)
  {
    $row2 = mysqli_fetch_array($result2, MYSQLI_NUM) ; 
    $direction_sensor_id = $row2[0] ;
    $nsensor++ ;
  }
  
  // If both sensors are there build the Polygon
  if ($nsensor == 2)
  {
    // Now select the very last updated values of the Wind direction for that equipement
    // Get the last wind speed sample measured by that Sensor
    $query3 = "SELECT sample.id AS sample_id, sample.value AS sample_value
          FROM sample
          WHERE equipement = " . $equipment 
          . " AND sensor = " . $speed_sensor_id 
          . " ORDER BY sampledat DESC LIMIT 1" ;
    $result3 = mysqli_query($dbc, $query3) or trigger_error("Query: $query3\n<br>MySQL Error: " . mysqli_error($dbc));
    $row3 = mysqli_fetch_array($result3, MYSQLI_NUM) ;
    $last_speed_value = $row3[1] ;
    
    // Get the last wind speed sample measured by that Sensor
    $query4 = "SELECT sample.id AS sample_id, sample.value AS sample_value, lat, lon
          FROM sample
          WHERE equipement = " . $equipment 
          . " AND sensor = " . $direction_sensor_id
          . " ORDER BY sampledat DESC LIMIT 1" ;
    $result4 = mysqli_query($dbc, $query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysqli_error($dbc));
    $row4 = mysqli_fetch_array($result4, MYSQLI_NUM) ;
    $last_direction_value = $row4[1] ;
    $lat = $row4[2] ;
    $lon = $row4[3] ;
    
    list($triangle_coordinates, $arrow_coordinates, $color_code) = calcPolygonCoordinates($lat, $lon, $last_speed_value, $last_direction_value) ;
    
    // Construct the part to display the arrow on the google map
    $final_arrow = '        // Define a symbol using a predefined path (an arrow)
        // supplied by the Google Maps JavaScript API.
        var lineSymbol = {
          path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW
        };

        // Create the polyline and add the symbol via the icons property.
        var line = new google.maps.Polyline({
          path: [{lat: ' . $arrow_coordinates[1][1] . ', lng: ' . $arrow_coordinates[1][2] . '}
          , {lat: ' . $arrow_coordinates[0][1] . ', lng: ' . $arrow_coordinates[0][2] . '}],
          strokeColor: \'' . $color_code . '\',
          strokeWeight: 2,
          icons: [{
            icon: lineSymbol,
            offset: \'100%\'
          }],
          map: map
        });
' ;
    
    // Construct the Javascript part to display the polygon on the google map
    // First Coordinates
    $final_polygon = $final_arrow . '  // Polygon Coordinates
  var triangleCoords = [
    new google.maps.LatLng(' . $triangle_coordinates[0][1] . ', ' . $triangle_coordinates[0][2] . '),'
    . '    new google.maps.LatLng(' . $triangle_coordinates[1][1] . ', ' . $triangle_coordinates[1][2] . '),'
    . '    new google.maps.LatLng(' . $triangle_coordinates[2][1] . ', ' . $triangle_coordinates[2][2] . ')'
    . '    ];' ;
  
    // Styling and Controling
    $final_polygon .= '  // Styling & Controls
  myPolygon = new google.maps.Polygon({
    paths: triangleCoords,
    editable: false,
    strokeColor: \'' . $color_code . '\',
    strokeOpacity: 0.7,
    strokeWeight: 2,
    fillColor: \'' . $color_code . '\',
    fillOpacity: 0.35
  });' ;
  
    // Displaying the Polygon
    $final_polygon .= '  myPolygon.setMap(map);
  //google.maps.event.addListener(myPolygon, "dragend", getPolygonCoords);
  google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
  //google.maps.event.addListener(myPolygon.getPath(), "remove_at", getPolygonCoords);
  google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
  ' ;
  
  }
  else
  {
    $final_polygon = '' ;
    $color_code = '#FFFFFF' ;
  }
  
  // Close db
  db_close($dbc) ;
  
  return array($final_polygon, $color_code) ;
} //buildWindPolygon


function calcPolygonCoordinates($lat, $lon, $wind_speed, $wind_direction)
{
  // Initial wind factor is 1 (Max is 5)
  //$wind_speed_factor = 6 ;
  if ($wind_speed <= 1.4) // Calm -> Light Air
  {
    $wind_speed_factor = 2 ;
    $color_code = '#46e246'; // Ademir: Good: rgb(70,226,70)
  }
  elseif ($wind_speed > 1.4 && $wind_speed <= 3) // Light Breeze
  {
    $wind_speed_factor = 3 ;
    $color_code = '#ffff00'; // Ademir: Moderate: rgb(255,255,0)
  }
  elseif ($wind_speed > 3 && $wind_speed <= 5) // Gentle Breeze
  {
    $wind_speed_factor = 4 ;
    $color_code = '#ff9900'; // Ademir: Little unhealthy: rgb(255, 153, 0)
  }
  elseif ($wind_speed > 5 && $wind_speed <= 7.8)  // Moderate Breeze
  {
    $wind_speed_factor = 5 ;
    $color_code = '#ff0000'; // Ademir: Unhealthy: rgb(255,0,0)
  }
  elseif ($wind_speed > 7.8 && $wind_speed <= 10)  // Fresh Breeze
  {
    $wind_speed_factor = 6 ;
    $color_code = '#99004d'; // Ademir: Very Unhealthy: rgb(153,0,77)
  }
  else   // Strong Gale
  {
    $wind_speed_factor = 7;
    $color_code = '#7e0123'; // Ademir: Hazardous: rgb(126,1,35)
  }
  
  //$triangle_coordinates = array() ;
  // Initial coordinates based on the shape of the triangle (Multiplied by wind factor on Y for triangle length!)
  $triangle_tip_x = 0 ;
  $triangle_tip_y = 0 * $wind_speed_factor ;
  $triangle_left_x = -0.001 ;
  $triangle_left_y = -0.001 * $wind_speed_factor ;
  $triangle_right_x = 0.001 ;
  $triangle_right_y = -0.001 * $wind_speed_factor ;
  
  /*
  // Arrow on top of triangle to show direction
  $arrow_tip_x = 0 ;
  $arrow_tip_y = 0.001 * $wind_speed_factor ;
  $arrow_back_x = 0 ;
  $arrow_back_y = 0 * $wind_speed_factor ;
  */
  
  // Arrow on top of triangle to show direction
  $arrow_tip_x = 0 ;
  $arrow_tip_y = 0.0005 * $wind_speed_factor ;
  $arrow_back_x = 0 ;
  $arrow_back_y = 0 * $wind_speed_factor ;
  
  // Now rotate the triangle with respect to the wind direction in degrees
  $triangle_tip_nx = $triangle_tip_x * cos(deg2rad($wind_direction)) - $triangle_tip_y * sin(deg2rad($wind_direction)) ;
  $triangle_tip_ny = $triangle_tip_x * sin(deg2rad($wind_direction)) + $triangle_tip_y * cos(deg2rad($wind_direction)) ;
  $triangle_left_nx = $triangle_left_x * cos(deg2rad($wind_direction)) - $triangle_left_y * sin(deg2rad($wind_direction)) ;
  $triangle_left_ny = $triangle_left_x * sin(deg2rad($wind_direction)) + $triangle_left_y * cos(deg2rad($wind_direction)) ;
  $triangle_right_nx = $triangle_right_x * cos(deg2rad($wind_direction)) - $triangle_right_y * sin(deg2rad($wind_direction)) ;
  $triangle_right_ny = $triangle_right_x * sin(deg2rad($wind_direction)) + $triangle_right_y * cos(deg2rad($wind_direction)) ;
  
  // Arrow
  $arrow_tip_nx = $arrow_tip_x * cos(deg2rad($wind_direction)) - $arrow_tip_y * sin(deg2rad($wind_direction)) ;
  $arrow_tip_ny = $arrow_tip_x * sin(deg2rad($wind_direction)) + $arrow_tip_y * cos(deg2rad($wind_direction)) ;
  $arrow_back_nx = $arrow_back_x * cos(deg2rad($wind_direction)) - $arrow_back_y * sin(deg2rad($wind_direction)) ;
  $arrow_back_ny = $arrow_back_x * sin(deg2rad($wind_direction)) + $arrow_back_y * cos(deg2rad($wind_direction)) ;
  
  // Position the tip of the triangle to the gps position of the equipment (Lat = Y, Lon = X)
  $triangle_tip_lat = $lat + $triangle_tip_ny ;
  $triangle_tip_lon = $lon + $triangle_tip_nx ;
  $triangle_left_lat = $lat + $triangle_left_ny ;
  $triangle_left_lon = $lon + $triangle_left_nx ;
  $triangle_right_lat = $lat + $triangle_right_ny ;
  $triangle_right_lon = $lon + $triangle_right_nx ;
  
  // Arrow
  $arrow_tip_lat = $lat + $arrow_tip_ny ;
  $arrow_tip_lon = $lon + $arrow_tip_nx ;
  $arrow_back_lat = $lat + $arrow_back_ny ;
  $arrow_back_lon = $lon + $arrow_back_nx ;
  
  $triangle_coordinates = array
  (
    array("tip",$triangle_tip_lat,$triangle_tip_lon),
    array("left",$triangle_left_lat,$triangle_left_lon),
    array("right",$triangle_right_lat,$triangle_right_lon)
  ) ;
  
  // Arrow
  $arrow_coordinates = array
  (
    array("tip",$arrow_tip_lat,$arrow_tip_lon),
    array("back",$arrow_back_lat,$arrow_back_lon)
  ) ;
  
  return array($triangle_coordinates, $arrow_coordinates, $color_code) ;
} //calcPolygonCoordinates

?>
