<?php # Script 12.4 - mysql_connect.php

// This file contains the database access information.
// This file also establishes a connection to MySQL and selects the database.
// This file also defines the escape_data() function.

// Set the database access information as constants.
DEFINE ('DB_USER', '******');
DEFINE ('DB_PASSWORD', '*******');
DEFINE ('DB_HOST', 'localhost:3306');
//DEFINE ('DB_HOST', '127.0.0.1');
DEFINE ('DB_NAME', 'admin_forevent');

if ( $dbc = @mysql_connect (DB_HOST, DB_USER, DB_PASSWORD)) { // Make the connection.

	if(!mysql_select_db(DB_NAME)) { //If it can't select the database.
		
		// Handle the error.
		trigger_error("Could not select the database!<br>MySQL error:" . mysql_error());
		
		// Print a message to the user, include the footer, and kill the script.
		exit();
		
	} // End of mysql_select_db IF.
	
} else { // If it couldn't connect to MySQL.

	// Print a message to the user, include the footer, and kill the script.
	trigger_error("Could not connect to MySQL!\n<br>MySQL Error: " . mysql_error());
	exit();
	
} // End of $dbc IF.

// Create a function for escaping the data.
function escape_data($data) {

	// Address magic Quotes.
	if (ini_get('magic_quotes_gpc')) {
		$data = stripslashes($data);
	}

	// Check for mysql_real_escape_string() support.
	if (function_exists('mysql_real_escape_string')) {
		global $dbc; // Need the connection.
		$data = mysql_real_escape_string(trim($data),$dbc);
	} else {
		$data = mysql_escape_string(trim($data));
	}
	
	// Return the escaped value.
	return $data;
	
} // End of function.

// function resize for jpeg, gif, png!
function resize($img, $thumb_width, $newfilename) 
{ 
  $max_width=$thumb_width;

    //Check if GD extension is loaded
    if (!extension_loaded('gd') && !extension_loaded('gd2')) 
    {
        trigger_error("GD is not loaded", E_USER_WARNING);
        return false;
    }

    //Get Image size info
    list($width_orig, $height_orig, $image_type) = getimagesize($img);
    
    switch ($image_type) 
    {
        case 1: $im = imagecreatefromgif($img); break;
        case 2: $im = imagecreatefromjpeg($img);  break;
        case 3: $im = imagecreatefrompng($img); break;
        default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
    }

	if($height_orig > $width_orig) {
		$aspect_ratio = (float) $width_orig / $height_orig;
		$thumb_width = round($max_width * $aspect_ratio);
		$thumb_height = $max_width;
	} elseif ($width_orig > $height_orig) {
		$aspect_ratio = (float) $height_orig / $width_orig;
		$thumb_height = round($max_width * $aspect_ratio);
		$thumb_width = $max_width;
	} else {
		$thumb_height = $max_width;
		$thumb_width = $max_width;
	}
    
    $newImg = imagecreatetruecolor($thumb_width, $thumb_height);
    
    /* Check if this image is PNG or GIF, then set if Transparent*/  
    if(($image_type == 1) OR ($image_type==3))
    {
        imagealphablending($newImg, false);
        imagesavealpha($newImg,true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $thumb_width, $thumb_height, $transparent);
    }
    imagecopyresampled($newImg, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);
    
    //Generate the file, and rename it to $newfilename
    switch ($image_type) 
    {
        case 1: imagegif($newImg,$newfilename); break;
        case 2: imagejpeg($newImg,$newfilename);  break;
        case 3: imagepng($newImg,$newfilename); break;
        default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
    }
 
    return $newfilename;
}

// echo resize("test4.png", 120, "thumb_test4.png") 

?>
