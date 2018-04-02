<?php

  // Creates a date/time element and returns result
  function createDateTimeElement()
  {
    $element_result = "";

    if ($this -> how_to_create == "ViewOnlyElement")
    {
      // If there is a replace value prog, make sure not to apply format, as the programmer will do so himself
      $replace_value_prog = checkTagContent($this -> form_element_root, $this -> element_root, "replace_value_prog");
      if (! is_null($replace_value_prog))
        $element_result = $this -> checkShowInsteadOfValues($this -> cur_value);
      else
        $element_result = applyFormat($this -> cur_value, "DATE_TIME", $this -> form_element_root, $this -> element_root);
    }
    elseif ($this -> how_to_create == "EditElement"
            || $this -> how_to_create == "NewElement")
    {
      // For date/time type of elements, show three components: date part, hour part, minute part
      $date_part_name = $this -> element_name . "_date";
      $hour_part_name = $this -> element_name . "_hour";
      $minute_part_name = $this -> element_name . "_min";

      // Also find the value for each date, hour and minute part
      $date_part_val = substr($this -> cur_value, 0, 10);
      $hour_part_val = substr($this -> cur_value, 11, 2);
      //debug($hour_part_val,"hour_part_val","File: " . __FILE__ . " Line: " . __LINE__) ;
      $minute_part_val = substr($this -> cur_value, 14, 2);
      //debug($minute_part_val,"minute_part_val","File: " . __FILE__ . " Line: " . __LINE__) ;

      $tool_tip = checkTagContent($this -> form_element_root, $this -> element_root, "tooltip");

      // ****** First create date part with jquery date picker
      $element_result = "<input name=\"" . $date_part_name . "\" id=\"" . $date_part_name . "\"";
      $element_result .= " size=\"10\" maxlength=\"" . DATE_STR_LEN . "\"";
      if (! empty($date_part_val))
        $element_result .= " value=\"" . $date_part_val . "\"";
      $element_result .= " title=\"" . processFetched($tool_tip) . "\"";
      if ($this -> read_only == "Yes")
        $element_result .= " readonly=\"yes\"";
      $element_result .= " type=\"text\" " . $this -> display_attributes . " />";

      // See if we have to start from specific hour or not
      $start_hour = checkTagContent($this -> form_element_root, $this -> element_root, "start_hour") ;
      if (is_null($start_hour))
        $start_hour = 0 ;
      
      // See if we have to start from specific hour or not
      $end_hour = checkTagContent($this -> form_element_root, $this -> element_root, "end_hour") ;
      if (is_null($end_hour))
        $end_hour = 23 ;
      
      // See if we have to use am/pm format for the hour or 
      // military 0 to 23. By default it is military
      $use_am_pm_timing = false ; 
      if(checkTagContent($this -> form_element_root,$this -> element_root,"use_am_pm_timing") == "Yes")
        $use_am_pm_timing = true ;
      
      //****** Create the hour part
      $hour_part = "\n<select name=\"" . $hour_part_name . "\" id=\"" . $hour_part_name . "\""
                          . $this -> display_attributes . ">";
      for ($i = $start_hour ; $i <= $end_hour ; $i++)
      {
        if ($use_am_pm_timing)
        {  
          // Convert display to to am/pm        
          if ($i == 0)
            $show_text = "12 AM" ;
          elseif ($i < 12)
            $show_text = $i . " AM" ;
          elseif ($i == 12)
            $show_text = $i . " PM" ;
          else
            $show_text = ($i - 12) . " PM" ;
        }  
        else // If military timing, then put 0 before
        {
          if ($i < 10)
            $show_text = "0" . $i;
          else
            $show_text = $i;
        }
          
        $hour_part .= "<option value=\"" . ($i < 10 ? "0" . $i : $i) . "\"";
        if ($i == $hour_part_val)
          $hour_part .= " selected=\"selected\"";
        $hour_part .= ">" . $show_text . "</option>";
      }
      $hour_part .= "\n</select>";

      //****** Create the minute part
      
      // Because we allow minutes by 5, then round the minutes part to the nearest 5 like
      // 0, 5, 10...
      $min_val_remainder = $minute_part_val % 5 ;
      $minute_part_val -= $min_val_remainder ;
      if ($min_val_remainder >= 2.5)
        $minute_part_val += 5 ;
      
      
      $minute_interval = checkTagContent($this -> form_element_root,$this -> element_root,"minute_interval") ;
      if (is_null($minute_interval))
        $minute_interval = 5 ;
      
      $minute_part = "\n<select name=\"" . $minute_part_name . "\" id=\"" . $minute_part_name . "\""
                            . $this -> display_attributes . ">" ;
      for ($i = 0; $i < 60; $i += $minute_interval)
      {
        if ($i > 9)
          $show_text = $i;
        else
          $show_text = "0" . $i;
        $minute_part .= "<option value=\"" . $show_text . "\"";
        if ($i == $minute_part_val)
          $minute_part .= " selected=\"selected\"";
        $minute_part .= ">" . $show_text . "</option>";
      }
      $minute_part .= "\n</select>";

      $element_result .= $hour_part . " :" . $minute_part;

      // path to the image that shows the icon where user can click to open the date pop-up
      $jquery_ui_image_path = plugin_folder . "/jquery_ui/images";
      if (defined("base_folder"))
        $jquery_ui_image_path = base_folder . "/" . $jquery_ui_image_path;

      // Add the jquery date picker for the input.
      // Note: We have to keep this code here and not share it, in case we want to have different options per date
      // control
      $element_result .= "<script type=\"text/javascript\">"
        . "$(function()"
        . "{"
        . "  $(\"#" . $date_part_name . "\").datepicker("
        . "{"
        . "  showOn: 'button'"
        . "  , buttonImage: '/" . $jquery_ui_image_path . "/calendar.gif'"
        . "  , buttonImageOnly: true"
        . "  , dateFormat: 'yy-mm-dd'"
        . "  , changeMonth: true"
        . "  , changeYear: true"
        . "}"
        . ") ;"
        . "}) ;"
        . "</script>";
    }
    elseif ($this -> how_to_create == "HiddenElement")
    {
      $element_result = "<input name=\"" . $this -> element_name . "\" id=\"" . $this -> element_name . "\"";
      $element_result .= " size=\"" . DT_STR_LEN_SHORT . "\" maxlength=\"" . DT_STR_LEN_SHORT . "\"";
      if (!empty($this -> cur_value))
        $element_result .= " value=\"" . substr(processFetched($cur_value), 0, DT_STR_LEN_SHORT) . "\"";
      $element_result .= " type=\"hidden\">";
    }

    return $element_result;
  } // createDateTimeElement

?>
