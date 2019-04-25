<?php

// Beware of the distance difference between Latitude and Longitude
// The difference is 0.663 or 1/0.663 = 1.5085 (Depending on how LAT and LON are determined)

function calcPolygonCoordinates($lat, $lon, $wind_speed, $wind_direction)
{
  // Test lat/lon factor
  //$wind_speed = 9 ;
  //$wind_direction = 90 ; // Test for 0 and 90
  $lat_lon_factor = 0.663 ; // 1.5085 ; 0.663
  
  // Initial wind factor is 1 (Max is 5)
  //$wind_speed_factor = 6 ;
  if ($wind_speed <= 1.4) // Calm -> Light Air
  {
    //$wind_speed_factor = 2 ;
    $color_code = '#46e246'; // Ademir: Good: rgb(70,226,70)
  }
  elseif ($wind_speed > 1.4 && $wind_speed <= 3) // Light Breeze
  {
    //$wind_speed_factor = 3 ;
    $color_code = '#ffff00'; // Ademir: Moderate: rgb(255,255,0)
  }
  elseif ($wind_speed > 3 && $wind_speed <= 5) // Gentle Breeze
  {
    //$wind_speed_factor = 4 ;
    $color_code = '#ff9900'; // Ademir: Little unhealthy: rgb(255, 153, 0)
  }
  elseif ($wind_speed > 5 && $wind_speed <= 7.8)  // Moderate Breeze
  {
    //$wind_speed_factor = 5 ;
    $color_code = '#ff0000'; // Ademir: Unhealthy: rgb(255,0,0)
  }
  elseif ($wind_speed > 7.8 && $wind_speed <= 10)  // Fresh Breeze
  {
    //$wind_speed_factor = 6 ;
    $color_code = '#99004d'; // Ademir: Very Unhealthy: rgb(153,0,77)
  }
  else   // Strong Gale
  {
    //$wind_speed_factor = 7;
    $color_code = '#7e0123'; // Ademir: Hazardous: rgb(126,1,35)
  }
  
  
  //$color_code = '#46e246';
  $length_factor = 15 ; // 8 initially
  if ($wind_speed <= 10)
    $wind_speed_factor = 1.5 * $wind_speed ;
  else
    $wind_speed_factor = 1.5 * 10 ; // Max 10
  
  //$triangle_coordinates = array() ;
  // Initial coordinates based on the shape of the triangle (Multiplied by wind factor on Y for triangle length!)
  $triangle_tip_x = 0 * $wind_speed_factor ;
  $triangle_tip_y = 0 * $length_factor ;
  $triangle_left_x = -0.001 * $wind_speed_factor ;
  $triangle_left_y = -0.001 * $length_factor ;
  $triangle_right_x = 0.001 * $wind_speed_factor ;
  $triangle_right_y = -0.001 * $length_factor ;
  
  // Arrow on top of triangle to show wind direction
  $arrow_tip_x = 0 * $wind_speed_factor ;
  $arrow_tip_y = -0.00025 * $length_factor ;
  $arrow_back_x = 0 * $wind_speed_factor ;
  $arrow_back_y = -0.00075 * $length_factor ;
  
  // 2 additional Side Arrows for wind direction
  $arrow2_tip_x = -0.0002 * $wind_speed_factor ;
  $arrow2_tip_y = -0.0004 * $length_factor ;
  $arrow2_back_x = -0.0007 * $wind_speed_factor ;
  $arrow2_back_y = -0.0009 * $length_factor ;
  $arrow3_tip_x = 0.0002 * $wind_speed_factor ;
  $arrow3_tip_y = -0.0004 * $length_factor ;
  $arrow3_back_x = 0.0007 * $wind_speed_factor ;
  $arrow3_back_y = -0.0009 * $length_factor ;
  
  // Now rotate the triangle with respect to the wind direction in degrees
  $triangle_tip_nx = $triangle_tip_x * cos(deg2rad($wind_direction)) - $triangle_tip_y * sin(deg2rad($wind_direction)) ;
  $triangle_tip_ny = $lat_lon_factor * ($triangle_tip_x * sin(deg2rad($wind_direction)) + $triangle_tip_y * cos(deg2rad($wind_direction))) ;
  $triangle_left_nx = $triangle_left_x * cos(deg2rad($wind_direction)) - $triangle_left_y * sin(deg2rad($wind_direction)) ;
  $triangle_left_ny = $lat_lon_factor * ($triangle_left_x * sin(deg2rad($wind_direction)) + $triangle_left_y * cos(deg2rad($wind_direction))) ;
  $triangle_right_nx = $triangle_right_x * cos(deg2rad($wind_direction)) - $triangle_right_y * sin(deg2rad($wind_direction)) ;
  $triangle_right_ny = $lat_lon_factor * ($triangle_right_x * sin(deg2rad($wind_direction)) + $triangle_right_y * cos(deg2rad($wind_direction))) ;
  
  // Arrow
  $arrow_tip_nx = $arrow_tip_x * cos(deg2rad($wind_direction)) - $arrow_tip_y * sin(deg2rad($wind_direction)) ;
  $arrow_tip_ny = $lat_lon_factor * ($arrow_tip_x * sin(deg2rad($wind_direction)) + $arrow_tip_y * cos(deg2rad($wind_direction))) ;
  $arrow_back_nx = $arrow_back_x * cos(deg2rad($wind_direction)) - $arrow_back_y * sin(deg2rad($wind_direction)) ;
  $arrow_back_ny = $lat_lon_factor * ($arrow_back_x * sin(deg2rad($wind_direction)) + $arrow_back_y * cos(deg2rad($wind_direction))) ;
  
  // 2 side Arrows
  $arrow2_tip_nx = $arrow2_tip_x * cos(deg2rad($wind_direction)) - $arrow2_tip_y * sin(deg2rad($wind_direction)) ;
  $arrow2_tip_ny = $lat_lon_factor * ($arrow2_tip_x * sin(deg2rad($wind_direction)) + $arrow2_tip_y * cos(deg2rad($wind_direction))) ;
  $arrow2_back_nx = $arrow2_back_x * cos(deg2rad($wind_direction)) - $arrow2_back_y * sin(deg2rad($wind_direction)) ;
  $arrow2_back_ny = $lat_lon_factor * ($arrow2_back_x * sin(deg2rad($wind_direction)) + $arrow2_back_y * cos(deg2rad($wind_direction))) ;
  $arrow3_tip_nx = $arrow3_tip_x * cos(deg2rad($wind_direction)) - $arrow3_tip_y * sin(deg2rad($wind_direction)) ;
  $arrow3_tip_ny = $lat_lon_factor * ($arrow3_tip_x * sin(deg2rad($wind_direction)) + $arrow3_tip_y * cos(deg2rad($wind_direction))) ;
  $arrow3_back_nx = $arrow3_back_x * cos(deg2rad($wind_direction)) - $arrow3_back_y * sin(deg2rad($wind_direction)) ;
  $arrow3_back_ny = $lat_lon_factor * ($arrow3_back_x * sin(deg2rad($wind_direction)) + $arrow3_back_y * cos(deg2rad($wind_direction))) ;
  
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
  
  // 2 side Arrows
  $arrow2_tip_lat = $lat + $arrow2_tip_ny ;
  $arrow2_tip_lon = $lon + $arrow2_tip_nx ;
  $arrow2_back_lat = $lat + $arrow2_back_ny ;
  $arrow2_back_lon = $lon + $arrow2_back_nx ;
  $arrow3_tip_lat = $lat + $arrow3_tip_ny ;
  $arrow3_tip_lon = $lon + $arrow3_tip_nx ;
  $arrow3_back_lat = $lat + $arrow3_back_ny ;
  $arrow3_back_lon = $lon + $arrow3_back_nx ;
  
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
    array("back",$arrow_back_lat,$arrow_back_lon),
    array("tip2",$arrow2_tip_lat,$arrow2_tip_lon),
    array("back2",$arrow2_back_lat,$arrow2_back_lon),
    array("tip3",$arrow3_tip_lat,$arrow3_tip_lon),
    array("back3",$arrow3_back_lat,$arrow3_back_lon)
  ) ;
  
  return array($triangle_coordinates, $arrow_coordinates, $color_code) ;
} //calcPolygonCoordinates

?>
