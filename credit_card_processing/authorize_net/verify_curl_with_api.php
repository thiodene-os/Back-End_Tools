<?php 

$ch = curl_init('https://apitest.authorize.net/xml/v1/request.api');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$data = curl_exec($ch);
curl_close($ch);

?>
