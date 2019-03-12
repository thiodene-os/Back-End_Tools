<?php #Script 13.1 - register.php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/includes/php/session_profiles.php');

// MAILGUN
# First, instantiate the SDK with your API credentials and define your domain.
require ($_SERVER['DOCUMENT_ROOT'] . '/includes/external/mailgun/vendor/autoload.php');
use Mailgun\Mailgun;
# Instantiate the client.
$mgClient = new Mailgun('key-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
$domain = 'mg.ioneec.com';

$page_title = 'IONEEC - Create An Account';
$bread_crumb = '<span class="breadc"><a href="http://www.ioneec.com">HOME</a> > Create An Account</span>';
$user_block = '<div id="sign">Already have an account? <a href="http://www.ioneec.com/signin">Sign in!</a></div>';

// Update visits
$query99 = "UPDATE prog_ioneec SET nvisits = nvisits + 1, currentvisits = currentvisits + 1 WHERE prog_id='2'";
$result99 = mysql_query($query99) or trigger_error("Query: $query99\n<br>MySQL Error: " . mysql_error());

// Include the configuration file for error management and such
require_once ($_SERVER['DOCUMENT_ROOT'] . '/includes/php/config.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/external/recaptcha/recaptchalib.php');
$publickey = "6LXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"; // you got this from the signup page
$privatekey = "6LXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

$display_block = " ";

if (isset($_POST['submitted'])) { // Handle the form

	// Check for an email address.
	if(preg_match ('/^[[:alnum:]][a-z0-9_\.\-]*@[a-z0-9\.\-]+\.[a-z]{2,4}$/i', stripslashes(trim($_POST['email'])))) {
		$e = escape_data($_POST['email']);
	} else {
		$e = FALSE;
		$display_block .= "<p><font color=\"red\">Please enter a valid email address!</font></p>";
	}

	// Check for a username.
	if (preg_match ('/^[[:alnum:]_\.\' \-]{2,25}$/i', stripslashes(trim($_POST['username'])))) {
		$un = escape_data($_POST['username']);
	} else {
		$un = FALSE;
		$display_block .= "<p><font color=\"red\">Please enter your username!</font></p>";
	}
	
	// Check for a password and match against the confirmed password.
	if(preg_match('/^[[:alnum:]]{4,40}$/i', stripslashes(trim($_POST['password1'])))) {
		if ($_POST['password1'] == $_POST['password2']) {
			$p = escape_data($_POST['password1']);
		} else {
			$p = FALSE;
			$display_block .= "<p><font color=\"red\" >Your password did not match the confirmed password!</font></p>";
		}
	} else {
		$p = FALSE;
		$display_block .= "<p><font color=\"red\">Please enter a valid password!</font></p>";
	}
	
	// Validate Recaptcha
	$resp = recaptcha_check_answer ($privatekey,$_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {
  		$display_block .= '<p><font color="red">The two words weren\'t entered correctly!</font></p>';
		$r = FALSE;
	} else {
		$r = TRUE;
	}

	if ($e && $p && $un && $r) { // If everything's OK.
	
		// Make sure the email address is available.
		$query = "SELECT member_id FROM members WHERE email='$e' AND deleted IS NULL";
		$result = mysql_query($query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysql_error());

		if (mysql_num_rows($result) == 0) { // Available
		
			// Create the activation code.
			$a = md5(uniqid(rand(), true));
			
			$query7 = "SELECT member_id FROM members WHERE email='$e'";
			$result7 = mysql_query($query7) or trigger_error("Query: $query7\n<br>MySQL Error: " . mysql_error());
			if (mysql_num_rows($result7) != 0) {
				$row7 = mysql_fetch_array($result7, MYSQL_NUM);
				$del = $row7[0];
			} else {
				$del = FALSE;
			}
			
			// Add (or reactivate) the member.
			if ($del){
				$query2 = "UPDATE members SET password=SHA('$p'), username='$un', deposit='100.00', active='$a', deleted=NULL WHERE member_id='$del'";
				$result2 = mysql_query($query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysql_error());
			} else {
				$query2 = "INSERT INTO members (email, password, username, deposit, reg_datetime, active) VALUES ('$e', SHA('$p'), '$un', '100.00', NOW(),'$a')";
				$result2 = mysql_query ($query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysql_error());
			}
			
			if (mysql_affected_rows() == 1) { // If it ran OK.
				// If the member never registered before
				if (!$del) {
					$memberid = @mysql_insert_id();
				}
				
				// Give invitation samples to the member
				if (!$del) {
					$query60 = "INSERT INTO samples (title, external_url, sampletype_id, api_id, embed, added_by, added_datetime, get_started) VALUES ('How To Stimulate Your Art', 'https://www.slideshare.net/slideshow/embed_code/key/dNU5LNihFnI66v', '4', '6', '[Script-Embed]', '$memberid', NOW(), 'y')";
					$result60 = mysql_query ($query60) or trigger_error("Query: $query60\n<br>MySQL Error: " . mysql_error());
				
					$query61 = "INSERT INTO samples (title, external_url, sampletype_id, api_id, embed, added_by, added_datetime, get_started) VALUES ('5 Reasons to use IONEEC', 'https://www.slideshare.net/slideshow/embed_code/key/BZpDOwB9e9SRZu', '4', '6', '[Script-Embed]', '$memberid', NOW(), 'y')";
					$result61 = mysql_query ($query61) or trigger_error("Query: $query61\n<br>MySQL Error: " . mysql_error());
				
					$query62 = "INSERT INTO samples (title, external_url, additional_url, sampletype_id, api_id, embed, added_by, added_datetime, get_started) VALUES ('Welcome To IONEEC', 'https://scontent.cdninstagram.com/hphotos-xpt1/t51.2885-15/e35/12519288_945339168846758_1487615760_n.jpg', '12519288_945339168846758_1487615760_n.jpg', '3', '4', '[Script-Embed]', '$memberid', NOW(), 'y')";
					$result62 = mysql_query ($query62) or trigger_error("Query: $query62\n<br>MySQL Error: " . mysql_error());
				} else {
					$query60 = "INSERT INTO samples (title, external_url, sampletype_id, api_id, embed, added_by, added_datetime, get_started) VALUES ('How To Stimulate Your Art', 'https://www.slideshare.net/slideshow/embed_code/key/dNU5LNihFnI66v', '4', '6', '[Script-Embed]', '$del', NOW(), 'y')";
					$result60 = mysql_query ($query60) or trigger_error("Query: $query60\n<br>MySQL Error: " . mysql_error());
				
					$query61 = "INSERT INTO samples (title, external_url, sampletype_id, api_id, embed, added_by, added_datetime, get_started) VALUES ('5 Reasons to use IONEEC', 'https://www.slideshare.net/slideshow/embed_code/key/BZpDOwB9e9SRZu', '4', '6', '[Script-Embed]', '$del', NOW(), 'y')";
					$result61 = mysql_query ($query61) or trigger_error("Query: $query61\n<br>MySQL Error: " . mysql_error());
				
					$query62 = "INSERT INTO samples (title, external_url, additional_url, sampletype_id, api_id, embed, added_by, added_datetime, get_started) VALUES ('Welcome To IONEEC', 'https://scontent.cdninstagram.com/hphotos-xpt1/t51.2885-15/e35/12519288_945339168846758_1487615760_n.jpg', '12519288_945339168846758_1487615760_n.jpg', '3', '4', '[Script-Embed]', '$del', NOW(), 'y')";
					$result62 = mysql_query ($query62) or trigger_error("Query: $query62\n<br>MySQL Error: " . mysql_error());
				}
				
				// Update the Mailing List
				if ($del) {
					$query5 = "SELECT mailing_id FROM mailing_list WHERE email='$e'";
					$result5 = mysql_query($query5) or trigger_error("Query: $query5\n<br>MySQL Error: " . mysql_error());
					if (mysql_num_rows($result5) != 0) {
						$row5 = mysql_fetch_array($result5, MYSQL_NUM);
						$mlid = $row5[0];
						$query51 = "UPDATE mailing_list SET name='$un', member_id='$del', deleted=NULL WHERE mailing_id='$mlid'";
						$result51 = mysql_query($query51) or trigger_error("Query: $query51\n<br>MySQL Error: " . mysql_error());
					} else {
						$query51 = "INSERT INTO mailing_list (email, name, member_id, added_by, added_datetime) VALUES ('$e', '$un', '$del', '1', NOW())";
						$result51 = mysql_query ($query51) or trigger_error("Query: $query51\n<br>MySQL Error: " . mysql_error());
					}
				} else {
					$query5 = "SELECT mailing_id FROM mailing_list WHERE email='$e'";
					$result5 = mysql_query($query5) or trigger_error("Query: $query5\n<br>MySQL Error: " . mysql_error());
					if (mysql_num_rows($result5) != 0) {
						$row5 = mysql_fetch_array($result5, MYSQL_NUM);
						$mlid = $row5[0];
						$query51 = "UPDATE mailing_list SET name='$un', member_id='$memberid', deleted=NULL WHERE mailing_id='$mlid'";
						$result51 = mysql_query($query51) or trigger_error("Query: $query51\n<br>MySQL Error: " . mysql_error());
					} else {
						$query51 = "INSERT INTO mailing_list (email, name, member_id, added_by, added_datetime) VALUES ('$e', '$un', '$memberid', '1', NOW())";
						$result51 = mysql_query ($query51) or trigger_error("Query: $query51\n<br>MySQL Error: " . mysql_error());
					}
				}
				
				// Add (or reactivate) IONEEC as a contact + The member as a contact to IONEEC
				if ($del) {
					$query32 = "SELECT contact_id FROM mailcontact WHERE member_id='$del' AND contactmember_id='1'";
					$result32 = mysql_query($query32) or trigger_error("Query: $query32\n<br>MySQL Error: " . mysql_error());
					if (mysql_num_rows($result32) == 0) {
						$query3 = "INSERT INTO mailcontact (member_id, contactmember_id, added_by, added_datetime) VALUES ('$memberid', '1', '1', NOW())";
						$result3 = mysql_query ($query3) or trigger_error("Query: $query3\n<br>MySQL Error: " . mysql_error());
					} else {
						$row32 = mysql_fetch_array($result32, MYSQL_NUM);
						$cid = $row32[0];
						$query3 = "UPDATE mailcontact SET deleted=NULL WHERE contact_id='$cid'";
						$result3 = mysql_query($query3) or trigger_error("Query: $query3\n<br>MySQL Error: " . mysql_error());
					}
				} else {
					$query3 = "INSERT INTO mailcontact (member_id, contactmember_id, added_by, added_datetime) VALUES ('$memberid', '1', '1', NOW())";
					$result3 = mysql_query ($query3) or trigger_error("Query: $query3\n<br>MySQL Error: " . mysql_error());
				}
				
				if ($del) {
					$query32 = "SELECT contact_id FROM mailcontact WHERE member_id='1' AND contactmember_id='$del'";
					$result32 = mysql_query($query32) or trigger_error("Query: $query32\n<br>MySQL Error: " . mysql_error());
					if (mysql_num_rows($result32) == 0) {
						$query31 = "INSERT INTO mailcontact (member_id, contactmember_id, added_by, added_datetime) VALUES ('1', '$memberid', '1', NOW())";
						$result31 = mysql_query ($query31) or trigger_error("Query: $query31\n<br>MySQL Error: " . mysql_error());
					} else {
						$row32 = mysql_fetch_array($result32, MYSQL_NUM);
						$cid = $row32[0];
						$query3 = "UPDATE mailcontact SET deleted=NULL WHERE contact_id='$cid'";
						$result3 = mysql_query($query3) or trigger_error("Query: $query3\n<br>MySQL Error: " . mysql_error());
					}
				} else {
					$query31 = "INSERT INTO mailcontact (member_id, contactmember_id, added_by, added_datetime) VALUES ('1', '$memberid', '1', NOW())";
					$result31 = mysql_query ($query31) or trigger_error("Query: $query31\n<br>MySQL Error: " . mysql_error());
				}
				
				// Add (or reactivate) IONEEC as a favorite + The member as one of IONEEC's favorites
				if ($del) {
					$query42 = "SELECT favorite_id FROM favorites WHERE member_id='$del' AND favoritemember_id='1'";
					$result42 = mysql_query($query42) or trigger_error("Query: $query42\n<br>MySQL Error: " . mysql_error());
					if (mysql_num_rows($result42) == 0) {
						$query4 = "INSERT INTO favorites (member_id, favoritemember_id, added_by, added_datetime) VALUES ('$memberid', '1', '1', NOW())";
						$result4 = mysql_query ($query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysql_error());
					} else {
						$row42 = mysql_fetch_array($result42, MYSQL_NUM);
						$fid = $row42[0];
						$query4 = "UPDATE favorites SET deleted=NULL WHERE favorite_id='$fid'";
						$result4 = mysql_query($query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysql_error());
					}
				} else {
					$query4 = "INSERT INTO favorites (member_id, favoritemember_id, added_by, added_datetime) VALUES ('$memberid', '1', '1', NOW())";
					$result4 = mysql_query ($query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysql_error());
				}
				
				if ($del) {
					$query42 = "SELECT favorite_id FROM favorites WHERE member_id='1' AND favoritemember_id='$del'";
					$result42 = mysql_query($query42) or trigger_error("Query: $query42\n<br>MySQL Error: " . mysql_error());
					if (mysql_num_rows($result42) == 0) {
						$query4 = "INSERT INTO favorites (member_id, favoritemember_id, added_by, added_datetime) VALUES ('1', '$memberid', '1', NOW())";
						$result4 = mysql_query ($query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysql_error());
					} else {
						$row42 = mysql_fetch_array($result42, MYSQL_NUM);
						$fid = $row42[0];
						$query4 = "UPDATE favorites SET deleted=NULL WHERE favorite_id='$fid'";
						$result4 = mysql_query($query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysql_error());
					}
				} else {
					$query4 = "INSERT INTO favorites (member_id, favoritemember_id, added_by, added_datetime) VALUES ('1', '$memberid', '1', NOW())";
					$result4 = mysql_query ($query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysql_error());
				}

				// Send a welcome message to the event316 email box
				$welcomemsg = "Thanks again for signing up to IONEEC!\n\n";
				$welcomemsg .= "Please find all the instructions into the help section,";
				$welcomemsg .= "and feel free to contact us in case you encounter bugs or find hard to execute some tasks!";
				$welcomemsg .= "\n\n IONEEC, The team.";
				if ($del) {
					$query4 = "INSERT INTO mailbox (member_id, sender_id, mail_topic, mail_content, ext_email, read_status, reply_status, received_datetime)
					VALUES ('$del', '1', 'Welcome to IONEEC!', '$welcomemsg', '$e', 'u', 'u', NOW())";
				} else {
					$query4 = "INSERT INTO mailbox (member_id, sender_id, mail_topic, mail_content, ext_email, read_status, reply_status, received_datetime)
					VALUES ('$memberid', '1', 'Welcome to IONEEC!', '$welcomemsg', '$e', 'u', 'u', NOW())";
				}
				$result4 = mysql_query ($query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysql_error()); 

				// Send the email.
				$body = "Thank you for registering at IONEEC. To activate your account, please click on this link: \n\n";
				if ($del) {
					$body .= "http://www.ioneec.com/signup/activate.php?x=" . $del . "&y=$a";
				} else {
					$body .= "http://www.ioneec.com/signup/activate.php?x=" . $memberid . "&y=$a";
				}
				$body .= "\n\n IONEEC, The team.";
				
				// MAILGUN
				# Make the call to the client.
				$result = $mgClient->sendMessage($domain, array(
					'from'    => 'IONEEC <no_reply@ioneec.com>',
					'to'      => "$e",
					'subject' => 'Registration Confirmation with IONEEC',
					'text'    => "$body"
				));

				// Finish the page.
				$page = "http://www.ioneec.com/signup/confirm.php";
				header("Location:" . $page);
				mysql_close();
				exit();
				
			} else { // If it did not run OK.
				$display_block .="<p><font color=\"red\">You could not be registered due to a system error.
				We apologize for any inconvenience and encourage you to try again later...</font></p>";
			}
		} else { // The email address is not available.
			$display_block .= "<p><font color=\"red\">That email address has already been registered. If you have forgotten your password,
			use the link to have your password sent to you.</font></p>";
		}
		
	} else { // If one of the data tests failed.
		$display_block .= "<p><font color=\"red\">Please try again.</font></p>";
	}
	
	mysql_close(); // Close the database connection.
	
} // End of the main Submit conditional.
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $page_title; ?></title>
<script type="text/javascript">
window.onload = function(){ 
	//Get submit button
	var submitbutton = document.getElementById("tfq");
	//Add listener to submit button
	if(submitbutton.addEventListener){
		submitbutton.addEventListener("click", function() {
			if (submitbutton.value == 'Enter your Email address!'){//Customize this text string to whatever you want
				submitbutton.value = '';
			}
		});
	}
}
</script>
<style type="text/css">
<!--
@import url(/includes/css/css_global.css);
-->
</style>
</head>

<body>

<?php include ($_SERVER['DOCUMENT_ROOT'] . '/includes/html/header.html'); ?>
<table width="940" border="0" cellspacing="0" cellpadding="0" class="lateent">
	<tr>
	<td width="100%" height="35" bgcolor="#F0F0F0"><span class="boxtopic">REGISTER TO IONEEC</span></td>
	</tr>
</table>
<table width="940" border="0" cellspacing="0" cellpadding="0">
 				 <tr>
				   <td align="left" valign="top">
						<h2 class="mtitle">Create an account...</h2>
						<p class="mtext">...share your samples & stimulate your art!!</p>
						<?php echo $display_block; ?>
						<form method="post" action="index.php">
						<table class="reg">
							<tr>
							<td noWrap height=10>
							</td>
							<td>
							</td>
							</tr>
							<tr>
							<td style="FONT-SIZE: x-small; FONT-FAMILY: Verdana">Email address<SPAN style="COLOR: #22a622">*</SPAN></td>
							<td></td>
							</tr>
							<tr>
							<td><INPUT type="text" maxLength=40 size=40 name=email value="<?php if (isset($_POST['email'])) echo $_POST['email'];?>"  class="imp"></td>
							<td></td>
							</tr>
						</table>
						<table class="reg">
							<tr>
							<td noWrap height=5></td>
							</tr>
							<tr>
							<td style="FONT-SIZE: x-small; FONT-FAMILY: Verdana">Create your  user ID<SPAN style="COLOR: #22a622">*</SPAN></td>
							</tr>
							<tr>
							<td><INPUT type="text" maxLength=25 size=30 name=username value="<?php if (isset($_POST['username'])) echo $_POST['username'];?>"  class="imp"></td>
							</tr>
							<tr>
							<td noWrap height=10></td>
							</tr>
							<tr>
							<td style="FONT-SIZE: x-small; FONT-FAMILY: Verdana">Create your password<SPAN style="COLOR: #22a622">*</SPAN></td>
							</tr>
							<tr>
							<td><INPUT type="password" maxLength=40 size=30 name=password1 class="imp"></td>
							</tr>
							<tr>
							<td style="FONT-SIZE: x-small; FONT-FAMILY: Verdana">Re-enter your password<SPAN style="COLOR: #22a622">*</SPAN></td>
							</tr>
							<tr>
							<td><INPUT type="password" maxLength=40 size=30 name=password2  class="imp"></td>
							</tr>
							<tr>
							<td><?php echo recaptcha_get_html($publickey); ?>
							</td>
							</tr>
							<tr>
							<td noWrap height=20></td>
							</tr>
							<tr>
							<td align="left" valign="middle"><input type="image" src="/images/register.jpg" name="submit" width="126" height="35" class="regimage">
						<input type="hidden" name="submitted" value="TRUE"></td>
							</tr>
						</table>
						</form>
				   </td>
 				 </tr>
						</table>

<?php include ($_SERVER['DOCUMENT_ROOT'] . '/includes/html/footer.html'); ?>
</body>

</html>
<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/includes/php/session_end.php');
?>
