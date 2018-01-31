<?php

// Adds a product to current user shopping cart
function addProductToShoppingCart($product_id, $qty, $unit_price, $note = null)
{ 
  // check if a combination of same session id, SKU, and note already exists
  $session_id = session_id();
  if(is_null($note))
    $note_comparison = 'IS NULL'; // reconsile different formats required to query NULL and strings
  else
    $note_comparison = "= '$note'";
  $sql_str = "SELECT * FROM SHOPPING_CART WHERE SESSION_ID = '$session_id' AND LNK_PRODUCT = 
              $product_id AND ITEM_NOTES $note_comparison ORDER BY UID DESC LIMIT 1";
  $qry = new dbQuery($sql_str,"File: " . __FILE__ . " LINE " . __LINE__);
  $rec_count = $qry -> getRecordsCount();
  if($rec_count > 0) // then update quantities instead of creating a new line
  {
    $scart_rec = $qry -> getRecords();
    $scart_rec = $scart_rec[0];
    $new_qty = $scart_rec['QTY_ORDERED'] + $qty;
    $new_subtotal = $new_qty * $unit_price;
    $do_record = new doRecord('SHOPPING_CART');
    $do_record -> id_column_val = $scart_rec['UID'];
    $do_record -> new_record = array('QTY_ORDERED' => $new_qty,
                                     'UNIT_PRICE' => $unit_price,
                                     'CURRENCY' => $_SESSION['currency'],
                                     'SUB_TOTAL' => $new_subtotal); // overwrite price, just in case                                              
    $result = $do_record -> update();
  }
  else // insert a new line into shopping cart (session id already noted from business rules)
  {
    $do_record = new doRecord('SHOPPING_CART') ;
    $new_rec = array() ;
    $new_rec['LNK_PRODUCT'] = $product_id ;
    $new_rec['QTY_ORDERED'] = $qty ;
    $new_rec['UNIT_PRICE'] = $unit_price ;
    $new_rec['CURRENCY'] = $_SESSION['currency'];
    $new_rec['ITEM_NOTES'] = $note ;
    $do_record -> new_record = $new_rec ;
    $result = $do_record -> insert() ;
  }
  
  // If it went fine, then show the shopping cart link
  if ($result)
  {  
    $return_str = "item_add_return = '" . str_replace('\'', '"', shoppingCartOrOrderLink()) . "';";
    $return_str .= '$("div#shop_cart_summary").remove();';
    $return_str .= '$("div#main_header").after(item_add_return);';
  } 
    
  return $return_str;
} // addProductToShoppingCart

?>
