<?php

function createSalesOrderAfterPaymentConfirmation($customer_id)
{
  $result = false ;
  
  // Get the subtotal and grand total
  $sql_str = "SELECT SUM(SUB_TOTAL) AS TOTAL_SUM FROM SHOPPING_CART
              WHERE SESSION_ID = '" . session_id() . "'" ;
  $qry = new dbQuery($sql_str,"File: " . __FILE__ . " Line: " . __LINE__) ;
  $total_rec = $qry -> getSingleRecord() ;
  $sub_total = $total_rec['TOTAL_SUM'] ;
  $tax1 = $tax2 = $tax3 = 0 ;
  $grand_total = $tax1 + $tax2 + $tax3 + $total_rec['TOTAL_SUM'] ;
  
  // Get the Customer contact ID
  $customer_contact_id = lookupColumnById("CUSTOMER_CONTACT","LNK_CUSTOMER",$customer_id,"UID") ;
  
  // Based on customer ID and user session
  // create a new Sales Order
  $do_record = new doRecord("SALES_ORDER") ;
  $new_rec = array() ;
  $new_rec['LNK_CUSTOMER'] = $customer_id  ;
  $new_rec['LNK_CUSTOMER_CONTACT'] = $customer_contact_id  ;
  $new_rec['ORDER_TYPE'] = 1  ;
  $new_rec['LNK_BILL_TO'] = $customer_id  ;
  $new_rec['LNK_SHIP_TO'] = $customer_id  ;
  $new_rec['ORDER_STATUS'] = ORDER_STATUS_CONFIRMED  ;
  $new_rec['SHIPPING_STATUS'] = ORDER_SHIPPING_STATUS_NOT_SHIPPED  ;
  $new_rec['ORDER_DATE'] = date("Y-m-d")  ;
  $new_rec['CURRENCY'] = getHostCurrency()  ;
  $new_rec['SUB_TOTAL'] = $sub_total  ;
  $taxes_to_apply = lookupColumnById("CUSTOMER","UID",$customer_id,"TAXES_TO_APPLY") ;
  $new_rec['TAX_DESCRIPTOR'] = buildTaxDescriptor($taxes_to_apply) ;
  $new_rec['TAX1_AMOUNT'] = $tax1  ;
  $new_rec['TAX2_AMOUNT'] = $tax2  ;
  $new_rec['TAX3_AMOUNT'] = $tax3  ;
  $new_rec['GRAND_TOTAL'] = $grand_total  ;
  $new_rec['ORDER_SOURCE'] = SALES_ORDER_SOURCE_WEB  ;
  $do_record -> new_record = $new_rec ;
  if (! $do_record -> insert()) 
    return Null ; 
  else
  {
    $order_id = $do_record -> id_column_val ;
    $result = $order_id ;
  }
  unset($new_rec) ;
  unset($do_record) ;
  
  // Add all the sales order items from the shopping cart
  $sql_str = "SELECT SHOPPING_CART.*, SHOPPING_CART.UID AS SCART_ID 
              FROM SHOPPING_CART WHERE SHOPPING_CART.SESSION_ID = '" . session_id() . "'" ;
  $qry = new dbQuery($sql_str,"File: " . __FILE__ . " LINE " . __LINE__) ;
  $scart_recs = $qry -> getRecords() ;
  $scart_rec_count = $qry -> getCount() ;
  unset($qry) ;
  
  $row_num = 1 ;
  foreach($scart_recs as $scart_item_rec)
  {
    $product_id = $scart_item_rec['LNK_PRODUCT']  ;
    $product_rec = lookupRecordbyId("PRODUCT_GEN","UID",$product_id
                                        ,"SKU,PRODUCT_NAME") ;
    
    $do_record = new doRecord("SALES_ORDER_ITEM") ;
    $new_rec = array() ;
    $new_rec['LNK_ORDER'] = $order_id  ;
    $new_rec['LNK_PRODUCT'] = $scart_item_rec['LNK_PRODUCT']  ;
    $new_rec['ROW_NUM'] = $row_num  ;
    $new_rec['PRODUCT_SKU'] = $product_rec['SKU'] ;
    $new_rec['PRODUCT_NAME'] = $product_rec['PRODUCT_NAME'] ;
    $new_rec['QTY_ORDERED'] = $scart_item_rec['QTY_ORDERED']  ;
    $new_rec['QTY_PROMISED'] = 0  ;
    $new_rec['QTY_SHIPPED'] = 0  ;
    $new_rec['QTY_REMAINING'] = 0  ;
    $new_rec['UNIT_PRICE'] = $scart_item_rec['UNIT_PRICE']  ;
    $new_rec['SUB_TOTAL'] = $scart_item_rec['SUB_TOTAL']  ;
    $new_rec['TAX1_AMOUNT'] = $tax1  ;
    $new_rec['TAX2_AMOUNT'] = $tax2  ;
    $new_rec['TAX3_AMOUNT'] = $tax3  ;
    $new_rec['GRAND_TOTAL'] = $tax1 + $tax2 + $tax3 + $scart_item_rec['SUB_TOTAL']  ;
    $do_record -> new_record = $new_rec ;
    if (! $do_record -> insert()) 
      return Null ; 
    else 
    {
      $order_item_id = $do_record -> id_column_val ;
      //after INSERT is done properly we delete the record from shopping_cart
      $do_record_del = new doRecord("SHOPPING_CART");
      $do_record_del -> id_column_val = $scart_item_rec['SCART_ID'];
      $do_record_del -> deleteRecords();
      $row_num++ ;
    }
    unset($new_rec) ;
    unset($do_record) ;

  }

  return $result ;
  
} // createSalesOrderAfterPaymentConfirmation

?>
