<?php

if ($in_type == "time")
{
  // Build Time Inputs from the Start Hour and the Interval Minutes
  $minute_step = $tag_root -> getAttribute("minute_step") ;
  $start_hour = $tag_root -> getAttribute("start_hour") ;

  $result .= '<span azbd_type="time" class="itin_multi_wrap" id="' . $tag_id . '" azbd_type="time">' ;
  $result .= '<select class="time_hour">'
             . '<option value="' . DEF_SHOW_ON_NO_VALUE . '" selected="selected">' 
             . DEF_SHOW_ON_NO_VALUE . '</option>' ;
  for ($i = $start_hour ; $i <= 23 ; $i++)
  {
    // Convert display to am/pm        
    if ($i == 0)
      $show_value = "12 AM" ;
    elseif ($i < 12)
      $show_value = $i . " AM" ;
    elseif ($i == 12)
      $show_value = $i . " PM" ;
    else
      $show_value = ($i - 12) . " PM" ;

    $result .= "<option value=\"" . ($i < 10 ? "0" . $i : $i) . "\"";
    $result .= ">" . $show_value . "</option>";
  }
  $result .= '</select>' ;

  $result .= ':<select class="time_minute">
              <option value="' . DEF_SHOW_ON_NO_VALUE . '" selected="selected">' 
              . DEF_SHOW_ON_NO_VALUE . '</option>' ;
  for ($i = 0; $i < 60; $i += $minute_step)
  {
    if ($i > 9)
      $show_value = $i;
    else
      $show_value = "0" . $i;
    $result .= "<option value=\"" . $show_value . "\"";
    $result .= ">" . $show_value . "</option>";
  }
  $result .= '</select></span>' ;
} // time

?>
