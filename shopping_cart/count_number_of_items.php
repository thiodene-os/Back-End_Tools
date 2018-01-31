<?php

// Gets the number of Items in the current user shopping cart (Even if price is 0)
function shoppingCartItems() 
{
  $sql_str = "SELECT SHOPPING_CART.UID AS SHOPPING_CART_ID FROM SHOPPING_CART 
                WHERE SESSION_ID = '" . session_id() . "'" ;
  $qry = new dbQuery($sql_str,"File: " . __FILE__ . " LINE " . __LINE__) ;
  $cart_items_count = $qry -> getRecordsCount();
  $cart_items_rec = $qry -> getSingleRecord() ;
  unset($qry) ;
  $result = $cart_items_count ;
  
  return $result ;
} // shoppingCartItems

?>
