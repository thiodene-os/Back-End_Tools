<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/php/common.php');
//Load Composer's autoloader
require(INCLUDES_PATH_EXTERNAL .'/vendor/autoload.php');
// Use Mailgun Libraries
use Mailgun\Mailgun;
// Mailgun credentials
# Instantiate the client.
$mgClient = new Mailgun('key-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
$domain = 'mg.forevent.com';

// Send a welcome message to the event316 email box
$body = "Thanks again for signing up to Forevent Map!\n\n";
$body .= "Please find all the instructions into the help section,";
$body .= "and feel free to contact us in case you encounter bugs or find hard to execute some tasks!";
$body .= "\n\n Forevent, The team.";

$e = "ayissi.serge@gmail.com" ;

// MAILGUN
# Make the call to the client.
$result = $mgClient->sendMessage($domain, array(
	'from'    => 'Forevent <no_reply@forevent.com>',
	'to'      => "$e",
	'subject' => 'Registration Confirmation with Forevent Map',
	'text'    => "$body"
));

?>
