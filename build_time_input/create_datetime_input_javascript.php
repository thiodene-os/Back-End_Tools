<?php
// Creates a date/time jscript and returns result
function createDateTimeElementJS()
{
  $java_script = "";

  if ($this -> how_to_create == "EditElement"
    || $this -> how_to_create == "NewElement")
  {
    $date_part_name = $this -> element_name . "_date";
    $hour_part_name = $this -> element_name . "_hour";
    $minute_part_name = $this -> element_name . "_min";

    $java_script = "  \nif (result != false)";
    $java_script .= "  \n{";

    $java_script .= "\n    var date_part = $.trim(document.getElementById('" . $date_part_name . "').value) ;";
    $java_script .= "\n    var hour_part = document.getElementById('" . $hour_part_name . "').value ;";
    $java_script .= "\n    var minute_part = document.getElementById('" . $minute_part_name . "').value ;";
    $java_script .= "\n    var full_input = date_part + ' ' + hour_part + ':' + minute_part ; ";

    if ($this -> min_len != 0)
    {
      $alert_message = getTagContentByAttrib($this -> form_element_root, $this -> element_root, "failure_msg", "for_rule", "min_len");
      $alert_message = str_replace("#col_name#", $this -> element_label, $alert_message);

      $java_script .= "\n    if (full_input.length != " . DT_STR_LEN_SHORT . ")";
      $java_script .= "\n   {";
      $java_script .= "\n     alert (\"" . $alert_message . "\") ;";
      $java_script .= "\n     result = false ;";
      $java_script .= "\n   }";
    }
    else
    {
      $java_script .= "\n    if (full_input.length != " . DT_STR_LEN_SHORT . " && full_input.length != 0 && trim(full_input) != \"00:00\")";
      $java_script .= "\n   {";
      $java_script .= "\n     alert (\"Error in " . $this -> element_label . ". It can be either empty or exactly " . DT_STR_LEN_SHORT . " characters.\") ;";
      $java_script .= "\n     result = false ;";
      $java_script .= "\n   }";
    }

    $java_script .= "\n  }\n";
  }

  //debug($java_script,"java_script","File: " . __FILE__ . " Line: " . __LINE__) ;
  return $java_script;
} // createDateTimeElementJS
?>
