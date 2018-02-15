<?php

// ** All the functions required to connect to payment gateway and process online payment **

//require '/plugin/authorize/vendor/autoload.php' ;
require ($_SERVER["DOCUMENT_ROOT"]."/plugin/authorize/vendor/autoload.php") ;
include ($_SERVER["DOCUMENT_ROOT"]."/plugin/authorize/Constants.php") ;
use net\authorize\api\contract\v1 as AnetAPI ;
use net\authorize\api\controller as AnetController ;
define("AUTHORIZENET_LOG_FILE", "phplog") ;

//function chargeCreditCard($amount)
function processOnlinePaymentAuthorizeNet($amount, $cc_info)
{
  
  global $config_mgr ;
  // Get the information entered by the user
  $pay_info = json_decode($cc_info) ;
  // First process payment
  $cc_info = array('customer_id' => $pay_info -> customer_id
                  ,'cc_no' => $pay_info -> cc_no
                  ,'cc_expiry' => $pay_info -> cc_expiry
                  ,'cvd' => $pay_info -> cvd
                  ,'name_on_cc' => $pay_info -> name_on_cc) ;
  
  // Get the customer's information 
  //$contact_rec = lookupRecordbyId("CUSTOMER_CONTACT","UID",$cc_info['customer_id']
                                        //,"FIRST_NAME,LAST_NAME,EMAIL") ; 
                                        
  // Get the customer's billing address
  $sql_str = "SELECT *, CUSTOMER.UID AS CUSTOMER_ID
              , CUSTOMER_CONTACT.FIRST_NAME, CUSTOMER_CONTACT.LAST_NAME 
              FROM CUSTOMER 
              INNER JOIN CUSTOMER_CONTACT ON CUSTOMER_CONTACT.LNK_CUSTOMER = CUSTOMER.UID
              WHERE CUSTOMER.UID = " . $cc_info['customer_id'] ;
  $qry = new dbQuery($sql_str,"File: " . __FILE__ . " LINE " . __LINE__) ;
  $customer_rec = $qry -> getRecord() ;
  unset($qry) ;
  
  //Complete the address in one line
  if ($customer_rec['ADDR_LINE2'])
    $address = $customer_rec['ADDR_LINE1'] . ' ' . $customer_rec['ADDR_LINE2'] ;
  else
    $address = $customer_rec['ADDR_LINE1'] ;

  
  
  /* Create a merchantAuthenticationType object with authentication details
     retrieved from the constants file */
  $merchantAuthentication = new AnetAPI\MerchantAuthenticationType() ;
  $merchantAuthentication->setName($config_mgr -> getParam("payment_gateway_id")) ;
  $merchantAuthentication->setTransactionKey($config_mgr -> getParam("payment_gateway_pwd")) ;
  
  // Set the transaction's refId
  $refId = 'ref' . time();
  // Create the payment data for a credit card
  $creditCard = new AnetAPI\CreditCardType() ;
  $creditCard->setCardNumber($cc_info['cc_no']) ;
  $creditCard->setExpirationDate($cc_info['cc_expiry']) ;
  $creditCard->setCardCode($cc_info['cvd']) ;
  // Add the payment data to a paymentType object
  $paymentOne = new AnetAPI\PaymentType() ;
  $paymentOne->setCreditCard($creditCard) ;
  // Create order information
  $order = new AnetAPI\OrderType() ;
  $order->setInvoiceNumber("10101") ; // Change it asap
  $order->setDescription("Golf Shirts") ; // Change it asap
  // Set the customer's Bill To address
  $customerAddress = new AnetAPI\CustomerAddressType();
  $customerAddress->setFirstName($customer_rec['FIRST_NAME']) ;
  $customerAddress->setLastName($customer_rec['LAST_NAME']) ;
  $customerAddress->setCompany($customer_rec['CUSTOMER_NAME']) ;
  $customerAddress->setAddress($address) ;
  $customerAddress->setCity($customer_rec['CITY']) ;
  $customerAddress->setState($customer_rec['PROVINCE']) ;
  $customerAddress->setZip($customer_rec['POSTAL_CODE']) ;
  $customerAddress->setCountry("CANADA") ;
  // Set the customer's identifying information
  $customerData = new AnetAPI\CustomerDataType() ;
  $customerData->setType("individual") ;
  $customerData->setId($customer_rec['CUSTOMER_ID']) ;
  $customerData->setEmail($customer_rec['MAIN_EMAIL']) ;
  // Add values for transaction settings
  $duplicateWindowSetting = new AnetAPI\SettingType() ;
  $duplicateWindowSetting->setSettingName("duplicateWindow") ;
  $duplicateWindowSetting->setSettingValue("60") ;
  // Add some merchant defined fields. These fields won't be stored with the transaction,
  // but will be echoed back in the response.
  $merchantDefinedField1 = new AnetAPI\UserFieldType() ;
  $merchantDefinedField1->setName("customerLoyaltyNum") ;
  $merchantDefinedField1->setValue($customer_rec['CUSTOMER_ID']) ;
  $merchantDefinedField2 = new AnetAPI\UserFieldType() ;
  $merchantDefinedField2->setName("favoriteColor") ;
  $merchantDefinedField2->setValue("blue") ;
  // Create a TransactionRequestType object and add the previous objects to it
  $transactionRequestType = new AnetAPI\TransactionRequestType() ;
  $transactionRequestType->setTransactionType("authCaptureTransaction") ;
  $transactionRequestType->setAmount($amount) ;
  $transactionRequestType->setOrder($order) ;
  $transactionRequestType->setPayment($paymentOne) ;
  $transactionRequestType->setBillTo($customerAddress) ;
  $transactionRequestType->setCustomer($customerData) ;
  $transactionRequestType->addToTransactionSettings($duplicateWindowSetting) ;
  $transactionRequestType->addToUserFields($merchantDefinedField1) ;
  $transactionRequestType->addToUserFields($merchantDefinedField2) ;
  // Assemble the complete transaction request
  $request = new AnetAPI\CreateTransactionRequest() ;
  $request->setMerchantAuthentication($merchantAuthentication) ;
  $request->setRefId($refId) ;
  $request->setTransactionRequest($transactionRequestType) ;
  // Create the controller and get the response
  $controller = new AnetController\CreateTransactionController($request) ;
  $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX) ;
  
  if ($response != null) {
      $result = '' ;
      // Check to see if the API request was successfully received and acted upon
      if ($response->getMessages()->getResultCode() == \SampleCode\Constants::RESPONSE_OK) {
      //if ($response->getMessages()->getResultCode() == 'Ok'){
          // Since the API request was successful, look for a transaction response
          // and parse it to display the results of authorizing the card
          $tresponse = $response->getTransactionResponse() ;
      
          if ($tresponse != null && $tresponse->getMessages() != null) {
              $result .= " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n" ;
              $result .= " Transaction Response Code: " . $tresponse->getResponseCode() . "\n" ;
              $result .= " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n" ;
              $result .= " Auth Code: " . $tresponse->getAuthCode() . "\n" ;
              $result .= " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n" ;
          } else {
              $result .= "Transaction Failed \n" ;
              if ($tresponse->getErrors() != null) {
                  $result .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n" ;
                  $result .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n" ;
              }
          }
          // Or, print errors if the API request wasn't successful
      } else {
          $result .= "Transaction Failed \n" ;
          $tresponse = $response->getTransactionResponse() ;
          
          //debug($tresponse,"tresponse","File: " . __FILE__ . " Line: " . __LINE__) ;
          
          
          if ($tresponse != null && $tresponse->getErrors() != null) {
              $result .= " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n" ;
              $result .= " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n" ;
          } else {
              $result .= " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n" ;
              $result .= " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n" ;
          }
      }
  } else {
      $result =  "No response returned \n" ;
  }
  return $result;
} // processOnlinePaymentAuthorizeNet

?>
