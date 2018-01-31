<?php

// Tries to process an online payment and will insert a record into PAYMENT table.
// Basically whenever an online payment is processed this function is called and a 
// record is added to PAYMENT table. This table keeps all the payment information 
// and has a link to the table to which 
// this payment is related like EVENT_DEPOSIT or ESP_BOOKINGS
// In this table we keep the history even if the payment fails.
// cc_info is an aaray with cc_num, expiry_month (MM)
// expiry_year (YY) and name_on_cc
// Order number is usually the event id or booking id for special events bookings
// Order name appears on the payment record and is usually customer name
// If cu_contact_id is given, then order name is built from there and order_name var 
// will be ignored.
function processOnlinePaymentBeanStream($order_num , $order_name , $payment_amount 
                                  , $transact_type , $cc_info , $cu_contact_id = null)
{
  global $config_mgr ;
  
  // Set transaction type
  if ($transact_type == PAYMENT_TRANSACTION_TYPE_PURCHASE)
    $trn_type = "P" ; 
  elseif ($transact_type == PAYMENT_TRANSACTION_TYPE_REFUND)
    $trn_type = "R" ; // Code for refund 
  else
  {
    setGlobalMsg("Please pick Purchase or Refund for Transactions Type.") ;
    return ;
  }
  
  // The result to be returned.
  $result = array("error" => false,"error_desc" => "") ;
  
  // ****************************************************************************
  // ************************* Send to Payment Gateway **************************
  
  // Initialize and use curl to connect
  $curl_obj = curl_init();

  // Get curl to POST
  curl_setopt($curl_obj,CURLOPT_POST,1);
  curl_setopt($curl_obj,CURLOPT_SSL_VERIFYHOST,0);
  curl_setopt($curl_obj,CURLOPT_SSL_VERIFYPEER,0);

  // Instruct curl to suppress the output from the system, and to directly
  // return the transfer instead. (Output will be stored in $txResult.)
  curl_setopt($curl_obj, CURLOPT_RETURNTRANSFER,1) ;

  // This is the location of the system
  curl_setopt($curl_obj,CURLOPT_URL,"https://www.beanstream.com/scripts/process_transaction.asp");

  // Build the curl request to be posted 
  $req = "requestType=BACKEND" ;
  $req .= "&trnType=" . $trn_type ;
  $req .= "&merchant_id=" . $config_mgr -> getParam("first_data_merchant_id") ;
  $req .= "&username=" . $config_mgr -> getParam("first_data_user_name") ;
  $req .= "&password=" . $config_mgr -> getParam("first_data_password") ;
  $req .= "&trnCardOwner=" . str_replace(" ","+",$cc_info['name_on_cc']) ;
  $req .= "&trnCardNumber=" . $cc_info['cc_num'] ;
  $req .= "&trnExpMonth=" . $cc_info['expiry_month'] ;
  $req .= "&trnExpYear=" . $cc_info['expiry_year'] ;
  $req .= "&trnOrderNumber=" . $order_num ;
  $req .= "&trnAmount=" . $payment_amount ;
  $req .= "&ordEmailAddress=" . $config_mgr -> getParam("first_data_email_for_auto_receipt") ;
  
  // Find customer name for ordName
  if (! is_null($cu_contact_id))
  {
    $cu_contact_rec = lookupColumnById("CUSTOMER_CONTACT","UID",$cu_contact_id
                                                ,"FIRST_NAME,LAST_NAME,LNK_CUSTOMER") ;
    $customer_rec = lookupColumnById("CUSTOMER","UID",$cu_contact_rec['LNK_CUSTOMER']
                                                        ,"CUSTOMER_TYPE,CUSTOMER_NAME") ;
    if ($customer_rec['CUSTOMER_TYPE'] == CUSTOMER_TYPE_CORPORATE)
      $ord_name = $customer_rec['CUSTOMER_NAME'] ; // For corporate customers use the company name
    else
      $ord_name = $cu_contact_rec['FIRST_NAME'] . "+" . $cu_contact_rec['LAST_NAME'] ; // For personal customers use the contact name
  }
  // Replace those weird characters in order name
  $order_name = str_replace("&","",$order_name) ;  
  $order_name = str_replace("'","",$order_name) ;
  $order_name = str_replace(" ","+",$order_name) ;
  $req .= "&ordName=" . $order_name ;
  $req .= "&ordPhoneNumber=" . $config_mgr -> getParam("first_data_def_phone_number") ;
  $req .= "&ordAddress1=" . str_replace(" ","+",$config_mgr -> getParam("first_data_def_address")) ;
  $req .= "&ordCity=" . str_replace(" ","+",$config_mgr -> getParam("first_data_def_city")) ;
  $req .= "&ordProvince=" . str_replace(" ","+",$config_mgr -> getParam("first_data_def_province")) ;
  $req .= "&ordPostalCode=" . str_replace(" ","+",$config_mgr -> getParam("first_data_def_postal_code")) ;
  $req .= "&ordCountry=" . str_replace(" ","+",$config_mgr -> getParam("first_data_def_country")) ; 

  // These are the transaction parameters that we will POST
  curl_setopt($curl_obj,CURLOPT_POSTFIELDS,$req);

  // Now POST the transaction. $response will contain the system response
  $response = curl_exec($curl_obj) ;
  curl_close($curl_obj);
  // ******************** End of Sending to Payment Gateway *********************
  // ****************************************************************************
  
  // If there is html in the response, it means it was not approved and there is 
  // a non-formatted error
  $trans_result = array() ;
  if (hasHTMLTags($response)) // This should never happen
    $trans_result['response'] = $response ; 
  else
  {  
    // Extract all name=value pairs from response and store them in an array
    $response = explode("&",$response) ;
    
    foreach($response as $cur_response)
    {
      $resp_arr = explode("=",$cur_response) ;
      $trans_result[$resp_arr[0]] = $resp_arr[1] ;
    }
  } // not hmtl response
  
  // Log the server response for debugging purposes
  $msg_to_log = "<trans_result1>" . serialize($trans_result) . "</trans_result1>" ;

  // ************ Check processing status **************

  $trans_approved = $trans_result['trnApproved'] ; 
  if (! $trans_approved)
  {
    $result['error'] = true ;
    $result['error_desc'] = "Transaction Not Approved: " . $trans_result['messageText'] ;
    $msg_to_log .= "<error_desc2>" . $result['error_desc'] . "</error_desc2>" ;
  }
  else
  {
    // Create a trnaction record to be saved. Wrap the transaction id so we can later
    // access it
    $trans_record = "Transaction Id: <trn_id>" . $trans_result['trnId'] . "</trn_id>" 
                  . "\n Message Id: " . $trans_result['messageId'] 
                  . "\n Message: " . $trans_result['messageText'] 
                  . "\n card Type : " . $trans_result['cardType'] 
                  . "\n Trans Type : " . $trans_result['trnType'] 
                  . "\n Payment Method : " . $trans_result['paymentMethod'] 
                  . "\n Order Number : " . $order_num ;
  
    $result['trans_record'] = $trans_record ;  
    $msg_to_log .= "<trans_record>" . $trans_record . "</trans_record>" ;
  }
  // First log the message in the log file for extra safety
  $log_file_name = $GLOBALS['upload_prefix'] . "/" .log_folder . "/payment-" . date("Y-m-d") . ".log";
  error_log("\n<pay_log>" . $msg_to_log . "</pay_log>", 3, $log_file_name);  // log the error to the log file
  
  return $result ;
} // processOnlinePaymentBeanStream

?>
