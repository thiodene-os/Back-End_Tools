<?php
# Basic PHP Strutural page before going to large websites

require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/php/common.php');
require_once(TEMPLATES_PATH . "/template_main.php");

/*
    Now you can handle all your php logic outside of the template
    file which makes for very clean code!
*/

$setInIndexDotPhp = "Hey! I was set in the index.php file.";

// Must pass in variables (as an array) to use in template
$variables = array(
    'setInIndexDotPhp' => $setInIndexDotPhp
);

renderLayoutWithContentFile("template_home.php", $variables);

?>
