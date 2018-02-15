<?php

// Tries to process an online payment through Chase Paymentech and will insert a record 
// into PAYMENT table.
// Basically whenever an online payment is processed this function is called and a record is added to 
// PAYMENT table. This table keeps all the payment information and has a link to the table to which 
// this payment is related like EVENT_DEPOSIT or ESP_BOOKINGS
// In this table we keep the history even if the payment fails.
// cc_info is an aaray with cc_no, cc_expiry in MMYY format and name_on_cc
function processOnlinePaymentChase($payment_amount , $transact_type , $cc_info)
{
  global $config_mgr ;
  
  if ($transact_type == PAYMENT_TRANSACTION_TYPE_PURCHASE)
    $chase_trans_code = "00" ; // Code for payment on Chase
  elseif ($transact_type == PAYMENT_TRANSACTION_TYPE_REFUND)
    $chase_trans_code = "04" ; // Code for refund on Chase Paymentech
  
  // The result to be returned.
  $result = array("error" => false,"error_desc" => "") ;
  
  $trn_properties = array
  (
    "User_Name" => "",
    "Secure_AuthResult" => "",
    "Ecommerce_Flag" => "",
    "XID" => "",
    "ExactID" => $config_mgr -> getParam("payment_gateway_id"),       //Payment Gateway I.E. CAD="A00049-01" USD="A00427-01"
    "CAVV" => "",
    "Password" => $config_mgr -> getParam("payment_gateway_pwd"),			//Gateway Password I.E. CAD="test1" USD="testus"
    "CAVV_Algorithm" => "",
    "Transaction_Type" => $chase_trans_code,          // Transaction Code I.E. Purchase="00" Pre-Authorization="01" etc.
    "Reference_No" => "" ,
    "Customer_Ref" => "",
    "Reference_3" => "" ,
    "Client_IP" => "",					                      // This value is only used for fraud investigation.
    "Client_Email" => "" ,			                      // This value is only used for fraud investigation.
    "Language" => "en" ,				                      // English="en" French="fr"
    "Card_Number" => $cc_info['cc_no'],               // For Testing, Use Test#s VISA="4111111111111111" MasterCard="5500000000000004" etc.
    "Expiry_Date" => $cc_info['cc_expiry'],           // This value should be in the format MMYY.
    "CardHoldersName" => $cc_info['name_on_cc'],
    "Track1" => "",
    "Track2" => "",
    "Authorization_Num" => "",
    "Transaction_Tag" => "",
    "DollarAmount" => $payment_amount ,
    "VerificationStr1" => "",
    "VerificationStr2" => "",
    "CVD_Presence_Ind" => "",
    "Secure_AuthRequired" => "",
    
    // Level 2 fields 
    "ZipCode" => "",
    "Tax1Amount" => "",
    "Tax1Number" => "",
    "Tax2Amount" => "",
    "Tax2Number" => "",
    
    "SurchargeAmount" => "",	// Used for debit transactions only
    "PAN" => ""							  // Used for debit transactions only
  );

  $trxn = array("Transaction" => $trn_properties);

  // If you are using our DEMO site at rpm-demo.e-xact.com with a Gateway ID of "AD...", you will need to use the host: api-demo.e-xact.com

  $soap_client = new SoapClient("https://api.e-xact.com/vplug-in/transaction/rpc-enc/service.asmx?wsdl");
  $trans_result = $soap_client -> __soapCall('SendAndCommit', $trxn);

  // Log the result for debugging purposes
  $msg_to_log = "<trans_result1>" . serialize($trans_result) . "</trans_result1>" ;
  
  
  // First check if any programming error happened
  if(isset($soap_client -> fault))
  {
    // there was a fault, inform
    $result['error'] = true ;
    $result['error_desc'] = "Program Error:  Code: {" . $soap_client -> faultcode . "}"
                          . " String: {" . $soap_client -> faultstring . "}" ;
    $msg_to_log .= "<error_desc1>" . $result['error_desc'] . "</error_desc1>" ;
  }

  // ************ Check processing status **************

  // Make sure to convert the return result to array for processing
  if (is_object($trans_result)) 
    $trans_result = get_object_vars($trans_result);
  elseif (is_array($trans_result)) 
    $trans_result = array_map(__FUNCTION__,$trans_result);

  $msg_to_log = "<trans_result2>" . serialize($trans_result) . "</trans_result2>" ;
   
  $error_flag = $trans_result['Transaction_Error'] ; 
  $trans_approved = $trans_result['Transaction_Approved'] ; 
  if ($error_flag || ! $trans_approved)
  {
    $result['error'] = true ;
    $result['error_desc'] = "Processing Error Number: " . $trans_result['Error_Number']
                          . "- " . $trans_result['EXact_Message'] 
                          . "- " . $trans_result['Bank_Message'] ;
    $msg_to_log .= "<error_desc2>" . $result['error_desc'] . "</error_desc2>" ;
  }
  elseif (! $trans_approved)
  {
    $result['error'] = true ;
    $result['error_desc'] = "Transaction Not Approved: " . $trans_result['CTR'] ;
    $msg_to_log .= "<error_desc2>" . $result['error_desc'] . "</error_desc2>" ;
  }
  else
  {
    $result['trans_record'] = $trans_result['CTR'] ;  
    $msg_to_log .= "<trans_record>" . $trans_result['CTR'] . "</trans_record>" ;
  }
  // First log the message in the log file for extra safety
  $log_file_name = $GLOBALS['upload_prefix'] . "/" . log_folder . "/payment-" . date("Y-m-d") . ".log";
  error_log("\n<pay_log>" . $msg_to_log . "</pay_log>", 3, $log_file_name);  // log the error to the log file
  
  return $result ;
} // processOnlinePaymentChase

?>
