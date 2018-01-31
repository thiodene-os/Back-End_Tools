<?php

// Gets the total value in the current user shopping cart
function shoppingCartValue() 
{
  $sql_str = "SELECT SUM(SUB_TOTAL) AS TOTAL_SUM FROM SHOPPING_CART 
                WHERE SESSION_ID = '" . session_id() . "'" ;
  $qry = new dbQuery($sql_str,"File: " . __FILE__ . " LINE " . __LINE__) ;
  $totals_rec = $qry -> getSingleRecord() ;
  unset($qry) ;
  $result = $totals_rec['TOTAL_SUM'] ;
  if (empty($result))
    $result = 0 ;
  
  return $result ;
} // shoppingCartValue 


?>
