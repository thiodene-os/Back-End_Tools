<?php # Script 13.11 - change_artist_name.php

require_once ($_SERVER['DOCUMENT_ROOT'] . '/includes/php/session_entries.php');

// MAILGUN
# First, instantiate the SDK with your API credentials and define your domain.
require ($_SERVER['DOCUMENT_ROOT'] . '/includes/external/mailgun/vendor/autoload.php');
use Mailgun\Mailgun;
# Instantiate the client.
$mgClient = new Mailgun('key-27782bf0245341e37866d723abd34201');
$domain = 'mg.ioneec.com';

$homemenu_block = "<table width=\"940\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"lateent\">
	<tr>
	<td align=\"center\" valign=\"middle\" width=\"100%\" height=\"50\">
		<DIV class=introc>
			<UL id=menuc>
			<LI><A href=\"http://www.ioneec.com/myioneec\">MYIONEEC</A> </LI>
			<LI><A href=\"http://www.ioneec.com/stimulator\">STIMULATOR</A> </LI>
			<LI><A href=\"http://www.ioneec.com/messages\">$messages</A> </LI>
			<LI class=actc><A href=\"http://www.ioneec.com/settings\">SETTINGS</A> </LI>
			<LI><A href=\"http://www.ioneec.com/members\">MEMBERS</A> </LI>
			<LI><A href=\"http://www.ioneec.com/help\">HELP</A> </LI>
			</UL>
		</DIV>
	</td>
	</tr>
</table>";

// Update visits
$query99 = "UPDATE prog_ioneec SET nvisits = nvisits + 1, currentvisits = currentvisits + 1 WHERE prog_id='21'";
$result99 = mysql_query($query99) or trigger_error("Query: $query99\n<br>MySQL Error: " . mysql_error());

$page_title = "IONEEC - Edit Group Emails";
$bread_crumb = '<span class="breadc"><a href="http://www.ioneec.com">HOME</a> > <a href="http://www.ioneec.com/myioneec">MYIONEEC</a> > <a href="http://www.forevent.com/settings">Settings</a> > Edit Group Emails</span>';
if (isset($_POST['mailing'])) {
	$mailing_block = "<select name=\"mailing\">";
	if ($_POST['mailing'] == 0) {
		$mailing_block .= "<OPTION value=0 selected>Select Destination Emails</OPTION>";
	} else {
		$mailing_block .= "<OPTION value=0>Select Destination Emails</OPTION>";
	}
	if ($_POST['mailing'] == 1) {
		$mailing_block .= "<OPTION value=1 selected>Test Emails</OPTION>";
	} else {
		$mailing_block .= "<OPTION value=1>Test Emails</OPTION>";
	}
	if ($_POST['mailing'] == 2) {
		$mailing_block .= "<OPTION value=2 selected>Members</OPTION>";
	} else {
		$mailing_block .= "<OPTION value=2>Members</OPTION>";
	}
	if ($_POST['mailing'] == 3) {
		$mailing_block .= "<OPTION value=3 selected>Subscribers (Non-Members)</OPTION>";
	} else {
		$mailing_block .= "<OPTION value=3>Subscribers (Non-Members)</OPTION>";
	}
	$mailing_block .= "</select>";
} else {
	$mailing_block = "<select name=\"mailing\">";
	$mailing_block .= "<OPTION value=0 selected>Select Destination Emails</OPTION>";
	$mailing_block .= "<OPTION value=1>Test Emails</OPTION>";
	$mailing_block .= "<OPTION value=2>Members</OPTION>";
	$mailing_block .= "<OPTION value=3>Subscribers (Non-Members)</OPTION>";
	$mailing_block .= "</select>";
}

// Version
$version = 1;
//$sending_limit = 1000;
$sending_limit = 1000;
$confirm = FALSE;

if (isset($_POST['confirmed'])) { // Handle the form.

	$mailing = $_POST['mailing'];
	$topic = escape_data($_POST['topic']);
	$t = $_POST['topic'];
	$content = escape_data($_POST['content']);
	$c = $_POST['content'];

	$nsent = $_POST['nsent'];
	if ($nsent > 1) {
		$start = ($nsent - 1) * $sending_limit;
	} else {
		$start = 0;
	}

	// Save Email into Database
	$query4 = "INSERT INTO group_email (topic, content, gmailing_id, added_by, added_datetime) VALUES ('$topic', '$content', '$mailing', '1',  NOW())";
	$result4 = mysql_query ($query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysql_error());
	
	if (mysql_affected_rows() == 1) { // If it ran OK.
		$newsid = @mysql_insert_id();
	
		if ($mailing == 1) { // Test group of emails
			$query = "SELECT email, name FROM mailing_list WHERE test IS NOT NULL AND deleted IS NULL LIMIT $start, $sending_limit";
			// Determine where in the database to start returning results.
			$query91 = "SELECT COUNT(*) FROM mailing_list WHERE test IS NOT NULL AND deleted IS NULL";
		
		} elseif ($mailing == 2) { // Members
			$query = "SELECT email, name FROM mailing_list WHERE member_id IS NOT NULL AND deleted IS NULL LIMIT $start, $sending_limit";
			// Determine where in the database to start returning results.
			$query91 = "SELECT COUNT(*) FROM mailing_list WHERE member_id IS NOT NULL AND deleted IS NULL";
			
		} else { // Subscribers non-members
			$query = "SELECT email, name FROM mailing_list WHERE member_id IS NULL AND deleted IS NULL LIMIT $start, $sending_limit";
			// Determine where in the database to start returning results.
			$query91 = "SELECT COUNT(*) FROM mailing_list WHERE member_id IS NULL AND deleted IS NULL";
			
		}
		$result = mysql_query($query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysql_error());
		
		// Calculate the total number of members
		$result91 = mysql_query($query91) or trigger_error("Query: $query91\n<br>MySQL Error: " . mysql_error());
		$row91 = mysql_fetch_array($result91, MYSQL_NUM);
		$num_mem = $row91[0];
		// Calculate the number of sendings.
		if ($num_mem > $sending_limit) { // More than 1 page.
			$nlimit = ceil($num_mem/$sending_limit);
		} else {
			$nlimit = 1;
		}
		
		
		
		$mlist = '';
		$rlist = '{';
		$iter =1;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if ($iter == 1) {
				$mlist .= $row['email'];
				$rlist .= '"' . $row['email'] . '": {"name":"' . $row['name'] . '", "email":"' . $row['email'] . '"}';
			} else {
				$mlist .= ', ' . $row['email'];
				$rlist .= ', "' . $row['email'] . '": {"name":"' . $row['name'] . '", "email":"' . $row['email'] . '"}';
			}
			$iter++;
		}
		$rlist .= '}';
	
		$body = "Dear %recipient.name%,\n\n";
		$body .= "$c\n\n";
		$body .= "IONEEC, The team.\n\n";
		$body .= "-----------------------------------------------\n";
		$body .= "IONEEC, Stimulate Your Art!\n";
		$body .= "#1105, 1 Grosvenor Street\n";
		$body .= "London, ON N6A 5B7 CANADA\n";
		$body .= "Voice: +1 519-719-8507 FAX: +1 519-601-0718\n";
		$body .= "Web: http://www.ioneec.com\n";

		# Make the call to the client.
		$result = $mgClient->sendMessage($domain, array(
		   'from'    => 'IONEEC <info@ioneec.com>',
			'to'      => "$mlist",
			'subject' => "$t",
			'text'    => "$body",
			'recipient-variables' => "$rlist"
		));
		
	
		if ($nsent < $nlimit) {
			$confirm = TRUE;
			$nsent++;
			$groupemail_block = "<div id=\"box\">
<h2 class=\"mtitle\">Edit A Group Email:</h2>
$display_block
<form method=\"post\" action=\"index.php\">
<table class=\"login\">
<tr>
<td style=\"FONT-SIZE: x-small; FONT-FAMILY: Verdana\"><b>Select the Mailing List:</b></td>
<td>$mailing_block</td>
</tr>
<tr>
<td height=20 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Topic:</span></td>
<td><INPUT style=\"FONT-FAMILY: Arial\" tabIndex=2 type=\"text\" maxLength=30 size=30 name=\"topic\" value=\"$t\"></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Content:</span></td>
<td><textarea name=\"content\" rows=8 cols=40 wrap=virtual>$c</textarea></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td>
</td>
<td noWrap height=20><input type=\"hidden\" name=\"confirmed\" value=\"TRUE\"><input type=\"hidden\" name=\"nsent\" value=\"$nsent\"></td>
</tr>
</form>	
<tr>
<td><div id=\"cancel\" align=\"right\">
  <a href=\"http://www.ioneec.com/settings/ioneec_entries\">cancel</a> 
  <font color=\"red\">Action Required:</font><form method=\"post\" action=\"index.php\"><input type=\"hidden\" name=\"mailing\" value=\"$mailing\"><input type=\"submit\" name=\"submit\" value=\"Edit\"><input type=\"hidden\" name=\"topic\" value=\"$t\"><input type=\"hidden\" name=\"content\" value=\"$c\"><input type=\"hidden\" name=\"edited\" value=\"TRUE\"></form><form method=\"post\" action=\"index.php\"><input type=\"submit\" name=\"submit\" value=\"Confirm $nsent/$nlimit\"><input type=\"hidden\" name=\"mailing\" value=\"$mailing\"><input type=\"hidden\" name=\"topic\" value=\"$t\"><input type=\"hidden\" name=\"content\" value=\"$c\"><input type=\"hidden\" name=\"confirmed\" value=\"TRUE\"><input type=\"hidden\" name=\"nsent\" value=\"$nsent\"></form>
</div></td>
</tr>
</table>
</div>";
		} else {
			$page = "http://www.ioneec.com/settings/ioneec_entries/index.php?" . "x=12402";
			header("Location:" . $page);
			mysql_close(); // Close the database connection.
			exit();
		}
	
	
	
	} else { // if it did not run OK.
	
		// Send a message to the error log, if desired.
		$display_block .= '<p><font color="red">The Group Email could not be added due to a system error. We apologize for any inconvenience.</font></p>';
	}


}

if (isset($_POST['submitted'])) { // Handle the form.
	
	$nsent = $_POST['nsent'];
	if ($nsent > 1) {
		$start = ($nsent - 1) * $sending_limit;
	} else {
		$start = 0;
	}
	
	// Check for a Mailing List
	if ($_POST['mailing'] == 0) {
		$mailing = FALSE;
		$display_block .= "<p><font color=\"red\">Please choose a Mailing List!</font></p>";
	} else {
		$mailing = $_POST['mailing'];
	
	
		if ($mailing == 1) { // Test group of emails
			$query = "SELECT email, name FROM mailing_list WHERE test IS NOT NULL AND deleted IS NULL LIMIT $start, $sending_limit";
			// Determine where in the database to start returning results.
			$query91 = "SELECT COUNT(*) FROM mailing_list WHERE test IS NOT NULL AND deleted IS NULL";
		
		} elseif ($mailing == 2) { // Members
			$query = "SELECT email, name ROM mailing_list WHERE member_id IS NOT NULL AND deleted IS NULL LIMIT $start, $sending_limit";
			// Determine where in the database to start returning results.
			$query91 = "SELECT COUNT(*) FROM mailing_list WHERE member_id IS NOT NULL AND deleted IS NULL";
			
		} else { // Subscribers non-members
			$query = "SELECT email, name FROM mailing_list WHERE member_id IS NULL AND deleted IS NULL LIMIT $start, $sending_limit";
			// Determine where in the database to start returning results.
			$query91 = "SELECT COUNT(*) FROM mailing_list WHERE member_id IS NULL AND deleted IS NULL";
			
		}
		$result = mysql_query($query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysql_error());
		
		// Calculate the total number of members
		$result91 = mysql_query($query91) or trigger_error("Query: $query91\n<br>MySQL Error: " . mysql_error());
		$row91 = mysql_fetch_array($result91, MYSQL_NUM);
		$num_mem = $row91[0];
		// Calculate the number of sendings.
		if ($num_mem > $sending_limit) { // More than 1 page.
			$nlimit = ceil($num_mem/$sending_limit);
		} else {
			$nlimit = 1;
		}
	
	
	}
	
	// Check for a Topic
	if (!empty($_POST['topic'])) {
		$topic = escape_data($_POST['topic']);
		$t = $_POST['topic'];
	} else {
		$topic = '';
		$t = FALSE;
		$display_block .= "<p><font color=\"red\">Please enter a valid Email Topic!</font></p>";
	}
	
	// Check for a Content
	if (!empty($_POST['content'])) {
		$content = escape_data($_POST['content']);
		$c = $_POST['content'];
	} else {
		$content = '';
		$c = FALSE;
		$display_block .= "<p><font color=\"red\">Please enter your Email Content!</font></p>";
	}

	if ($mailing && $t && $c) { // If everything's OK.	
		
		$confirm = TRUE;
		$groupemail_block = "<div id=\"box\">
<h2 class=\"mtitle\">Edit A Group Email:</h2>
$display_block
<form method=\"post\" action=\"index.php\">
<table class=\"login\">
<tr>
<td style=\"FONT-SIZE: x-small; FONT-FAMILY: Verdana\"><b>Select the Mailing List:</b></td>
<td>$mailing_block</td>
</tr>
<tr>
<td height=20 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Topic:</span></td>
<td><INPUT style=\"FONT-FAMILY: Arial\" tabIndex=2 type=\"text\" maxLength=30 size=30 name=\"topic\" value=\"$t\"></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Content:</span></td>
<td><textarea name=\"content\" rows=8 cols=40 wrap=virtual>$c</textarea></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td>
</td>
<td noWrap height=20><input type=\"hidden\" name=\"confirmed\" value=\"TRUE\"><input type=\"hidden\" name=\"nsent\" value=\"1\"></td>
</tr>
</form>	
<tr>
<td><div id=\"cancel\" align=\"right\">
  <a href=\"http://www.ioneec.com/settings/ioneec_entries\">cancel</a> 
<font color=\"red\">Action Required:</font> <form method=\"post\" action=\"index.php\"><input type=\"hidden\" name=\"mailing\" value=\"$mailing\"><input type=\"submit\" name=\"submit\" value=\"Edit\"><input type=\"hidden\" name=\"topic\" value=\"$t\"><input type=\"hidden\" name=\"content\" value=\"$c\"><input type=\"hidden\" name=\"edited\" value=\"TRUE\"></form> <form method=\"post\" action=\"index.php\"><input type=\"submit\" name=\"submit\" value=\"Confirm $nsent/$nlimit\"><input type=\"hidden\" name=\"mailing\" value=\"$mailing\"><input type=\"hidden\" name=\"topic\" value=\"$t\"><input type=\"hidden\" name=\"content\" value=\"$c\"><input type=\"hidden\" name=\"nsent\" value=\"1\"><input type=\"hidden\" name=\"confirmed\" value=\"TRUE\"></form>
</div></td>
</tr>
</table>
</div>";
		
	} else { // If everything wasn\"t OK.
		$display_block .= '<p><font color="red">Please try again.</font></p>';
		$groupemail_block = "<div id=\"box\">
<h2 class=\"mtitle\">Edit A Group Email:</h2>
$display_block
<form method=\"post\" action=\"index.php\">
<table class=\"login\">
<tr>
<td style=\"FONT-SIZE: x-small; FONT-FAMILY: Verdana\"><b>Select the Mailing List:</b></td>
<td>$mailing_block</td>
</tr>
<tr>
<td height=20 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Topic:</span></td>
<td><INPUT style=\"FONT-FAMILY: Arial\" tabIndex=2 type=\"text\" maxLength=30 size=30 name=\"topic\" value=\"$t\"></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Content:</span></td>
<td><textarea name=\"content\" rows=8 cols=40 wrap=virtual>$c</textarea></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td>
</td>
<td noWrap height=20><input type=\"hidden\" name=\"submitted\" value=\"TRUE\"><input type=\"hidden\" name=\"nsent\" value=\"1\"></td>
</tr>
<tr>
<td><div id=\"cancel\" align=\"right\">
  <a href=\"http://www.ioneec.com/settings/ioneec_entries\">cancel</a> 
  <input type=\"submit\" name=\"submit\" value=\"Confirm\">
</div></td>
</tr>
</table>
</form>	
</div>";
		
	}

} else {
	if (!$confirm) {
		$groupemail_block = "<div id=\"box\">
<h2 class=\"mtitle\">Edit A Group Email:</h2>
$display_block
<form method=\"post\" action=\"index.php\">
<table class=\"login\">
<tr>
<td style=\"FONT-SIZE: x-small; FONT-FAMILY: Verdana\"><b>Select the Mailing List:</b></td>
<td>$mailing_block</td>
</tr>
<tr>
<td height=20 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Topic:</span></td>
<td><INPUT style=\"FONT-FAMILY: Arial\" tabIndex=2 type=\"text\" maxLength=30 size=30 name=\"topic\" value=\"\"></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Content:</span></td>
<td><textarea name=\"content\" rows=8 cols=40 wrap=virtual></textarea></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td>
</td>
<td noWrap height=20><input type=\"hidden\" name=\"submitted\" value=\"TRUE\"><input type=\"hidden\" name=\"nsent\" value=\"1\"></td>
</tr>
<tr>
<td><div id=\"cancel\" align=\"right\">
  <a href=\"http://www.ioneec.com/settings/ioneec_entries\">cancel</a> 
  <input type=\"submit\" name=\"submit\" value=\"Confirm\">
</div></td>
</tr>
</table>
</form>	
</div>";
	}
}

if (isset($_POST['edited'])) { // Handle the form.
	$t = $_POST['topic'];
	$c = $_POST['content'];
	
	$groupemail_block = "<div id=\"box\">
<h2 class=\"mtitle\">Edit A Group Email:</h2>
$display_block
<form method=\"post\" action=\"index.php\">
<table class=\"login\">
<tr>
<td style=\"FONT-SIZE: x-small; FONT-FAMILY: Verdana\"><b>Select the Mailing List:</b></td>
<td>$mailing_block</td>
</tr>
<tr>
<td height=20 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Topic:</span></td>
<td><INPUT style=\"FONT-FAMILY: Arial\" tabIndex=2 type=\"text\" maxLength=30 size=30 name=\"topic\" value=\"$t\"></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td align=\"left\" valign=\"middle\"><span class=\"champst\">Content:</span></td>
<td><textarea name=\"content\" rows=8 cols=40 wrap=virtual>$c</textarea></td>
</tr>
<tr>
<td height=10 nowrap>
</td>
<td></td>
</tr>
<tr>
<td>
</td>
<td noWrap height=20><input type=\"hidden\" name=\"submitted\" value=\"TRUE\"><input type=\"hidden\" name=\"nsent\" value=\"1\"></td>
</tr>
<tr>
<td><div id=\"cancel\" align=\"right\">
  <a href=\"http://www.ioneec.com/settings/ioneec_entries\">cancel</a> 
  <input type=\"submit\" name=\"submit\" value=\"Confirm\">
</div></td>
</tr>
</table>
</form>	
</div>";
}


mysql_close(); // Close the database connection.

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
#box {
	width: 600px;
	padding:5px;
	margin: 0;
}
table#myioneec {
	padding: 0;
	margin: 0px 0px 20px 90px;
}
table#mypass {
	margin: 15px 0px 0px 50px;
	padding: 0;
}
-->
</style>
</head>

<body>

<?php include ($_SERVER['DOCUMENT_ROOT'] . '/includes/html/header.html'); ?>
<table width="940" border="0" cellspacing="0" cellpadding="0" class="lateent">
	<tr>
	<td width="100%" height="35" bgcolor="#F0F0F0"><span class="boxtopic">GROUP EMAIL</span></td>
	</tr>
</table>
<table width="940" border="0" cellspacing="0" cellpadding="0">
 				 <tr>
				   <td align="left" valign="top">
<?php echo $groupemail_block; ?>
				   </td>
 				 </tr>
</table>
<?php include ($_SERVER['DOCUMENT_ROOT'] . '/includes/html/footer.html'); ?>
</body>

</html>
<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/includes/php/session_end.php');
?>
