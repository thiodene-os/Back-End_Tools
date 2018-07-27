<?php

// PHP 7.0

// MySQL database connection
// Define the Database parameters
define ('DB_USER', 'root') ;
define ('DB_HOST', '167.114.10.49') ;
define ('DB_PASSWORD', 'GAhftYwxCvw93L5e');
define ('DB_NAME', 'sims');
// Try the connection to MySQL  + Select the relevant database
$dbc = @mysqli_connect(DB_HOST, DB_USER , DB_PASSWORD) ;
if ($dbc)
{
  if(!mysqli_select_db($dbc, DB_NAME))
  {
    trigger_error("Could not select the database!<br>MYSQL Error:" . mysqli_error($dbc)) ;
    exit();
  }
}
else
{
  trigger_error("Could not connect to MySQL!<br>MYSQL Error:" . mysqli_error($dbc));
  exit();
}

// Website images and files folder
define ("IMAGES_FOLDER","/var/www/html/sims2/images") ;
define ("TEMPLATES_PATH","/var/www/html/sims2/templates") ;
define ("LIBRARIES_PATH","/var/www/html/sims2/includes/php/libs") ;

define ("INCLUDES_PATH_PHP","/var/www/html/sims2/includes/php") ;
define ("INCLUDES_PATH_CSS","/var/www/html/sims2/includes/css") ;
define ("INCLUDES_PATH_HTML","/var/www/html/sims2/includes/html") ;
define ("INCLUDES_PATH_JS","/var/www/html/sims2/includes/javascript") ;
define ("INCLUDES_PATH_EXTERNAL","/var/www/html/sims2/includes/external") ;

// All the necessary includes for the global page to display
include 'constants.php' ;
include 'libs/lib_main.php' ;

?>
