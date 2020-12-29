<?php

# Test get the last connected IP information from SIMS1 Logs
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/php/common.php');

// Autoload definition for Email and SMS
require('/root/vendor/autoload.php');
// Mail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// SMS
use Twilio\Rest\Client;
$account_sid = 'ACXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$auth_token = 'My Twilio Token';

// test Notification
$user_id = 174 ;

// Get the User's info
$query9 = "SELECT user.id AS user_id, email, tel, name
      FROM user
      WHERE user.id = " . $user_id ; // Can also be done
$result9 = mysqli_query($dbc, $query9) or trigger_error("Query: $query9\n<br>MySQL Error: " . mysqli_error($dbc));
$row9 = mysqli_fetch_array($result9, MYSQLI_NUM) ;
//$user_id = $row9[0] ;
$e = $row9[1] ;
$phone = $row9[2] ;
$full_name = $row9[3] ;

// Select sensors with enabled notification for this User
$query = "SELECT alarmcheckpoint_sensor.id AS alarmcheckpoint_id, sensor_id, formula, data_unit
      , low_point, low_notification_dt, high_point, high_notification_dt
      FROM alarmcheckpoint_sensor
      WHERE user_id = " . $user_id
      . " AND enabled = 'y' LIMIT 1" ;
$result = mysqli_query($dbc_local, $query) or trigger_error("Query: $query\n<br>MySQL Error: " . mysqli_error($dbc_local));
if (@mysqli_num_rows($result) != 0)
{
  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
  {
    $alarmcheckpoint_id = $row['alarmcheckpoint_id'] ;
    $sensor_id = $row['sensor_id'] ;
    $data_unit = $row['data_unit'] ;
    $sensor_name = $row['formula'] ;
    
    // From the date of the last check to now (Go through the values and compare to max and min)
    $low_notif_value = $row['low_point'] ;
    $low_notification_dt = $row['low_notification_dt'] ;
    $high_notif_value = $row['high_point'] ;
    $high_notification_dt = $row['high_notification_dt'] ;
    // Verify that low and high notification values are not empty!
    if (strlen($high_notif_value) > 0 && strlen($low_notif_value) > 0)
    {
      // Get the equipment and company IDs from sensor_id
      $query7 = "SELECT sensor.id AS sensor_id, sensor.equipement, equipement.name, equipement.company
            FROM sensor
            INNER JOIN equipement ON equipement.id = sensor.equipement
            WHERE sensor.id = " . $sensor_id ;
      $result7 = mysqli_query($dbc, $query7) or trigger_error("Query: $query7\n<br>MySQL Error: " . mysqli_error($dbc));
      $row7 = mysqli_fetch_array($result7, MYSQLI_NUM) ;
      $equipment_id = $row7[1] ;
      $equipment_name = $row7[2] ;
      $company_id = $row7[3] ;
      
      // From settings notification dt to now, check sample values for this sensor
      $update_high_tmstp = strtotime($high_notification_dt) ;
      $update_low_tmpstp = strtotime($low_notification_dt) ;
      
      // Select sample value where is above threshold
      $query2 = "SELECT sample.id AS sample_id, sample.value, sample.sampledat
            FROM sample
            WHERE sensor = " . $sensor_id
            . " AND sampledat > '" . $update_high_tmstp . "'"
            . " AND CAST(value AS UNSIGNED) >= " . $high_notif_value
            . " ORDER BY sampledat ASC LIMIT 1" ;
      $result2 = mysqli_query($dbc, $query2) or trigger_error("Query: $query2\n<br>MySQL Error: " . mysqli_error($dbc));
      if (@mysqli_num_rows($result2) != 0)
      {
        $row2 = mysqli_fetch_array($result2, MYSQLI_NUM) ;
        
        $value = $row2[1] ;
        $sampledat = $row2[2] ;
        $value_dt = date("l jS F Y", $sampledat) ;
        $value_sql_dt = date("Y-m-d H:i:s", $sampledat) ;
        
        // Write the message under condition
        $high_notif_msg = 'The value of ' . $value . ' (' . $data_unit . ') for sensor ' . $sensor_name 
        . ' in ' . $equipment_name . ' crossed the maximum threshold of ' . $high_notif_value 
        . ' on ' . $value_dt . '.' ;
        
        // INSERT data in alarm_sensor
        $query4 = "INSERT INTO alarm_sensor (message, user_id, sensor_id, equipment_id, company_id, created_dt) VALUES (" 
                  . "'" . $high_notif_msg . "'," . $user_id . ", " . $sensor_id 
                  . ", " . $equipment_id . ", " . $company_id . ",NOW()); " ;
        $result4 = mysqli_query($dbc_local, $query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
        
        // Update Notification date
        $query8 = "UPDATE settings SET notification_dt='$value_sql_dt' WHERE user_id = " . $user_id ;
        $result8 = mysqli_query($dbc_local, $query8) or trigger_error("Query: $query8\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
        
        // Send Email + SMS
        // If alarm email has been sent use it. if not use the user's email
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        //Server settings
        //$mail->SMTPDebug = 2;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'mail.*******.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'alarm@********.com';                 // SMTP username
        $mail->Password = '********';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to
        //Recipients
        $mail->setFrom('info@*******.com', 'Notification');
        $mail->addAddress($e,$full_name);     // Add a recipient
        $mail->addReplyTo('info@******.com', 'Notification');
        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Notification' ;
        $mail->Body    = $high_notif_msg ;
        $mail->AltBody = $high_notif_msg ;
        $mail->send() ;
        
        // Send SMS
        // A Twilio number you own with SMS capabilities
        $twilio_number = "***********";
        $client = new Client($account_sid, $auth_token) ;
        $client->messages->create(
          // Where to send a text message (your cell phone?)
          $phone,
          array(
            'from' => $twilio_number,
            'body' => $high_notif_msg
          )
        );
        
      }
      else
      {
        // Update Notification date to today if no alarm has been found
        $query8 = "UPDATE alarmcheckpoint_sensor SET high_notification_dt=NOW() 
                   WHERE alarmcheckpoint_sensor.id = " . $alarmcheckpoint_id ;
        $result8 = mysqli_query($dbc_local, $query8) or trigger_error("Query: $query8\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
      }
      
      // Select sample value where is above threshold
      $query3 = "SELECT sample.id AS sample_id, sample.value, sample.sampledat
            FROM sample
            WHERE sensor = " . $sensor_id
            . " AND sampledat > '" . $update_low_tmpstp . "'"
            . " AND CAST(value AS UNSIGNED) <= " . $low_notif_value
            . " ORDER BY sampledat ASC LIMIT 1" ;
      $result3 = mysqli_query($dbc, $query3) or trigger_error("Query: $query3\n<br>MySQL Error: " . mysqli_error($dbc));
      if (@mysqli_num_rows($result3) != 0) 
      {
        $row3 = mysqli_fetch_array($result3, MYSQLI_NUM) ;
        
        $value = $row3[1] ;
        $sampledat = $row3[2] ;
        $value_dt = date("l jS F Y", $sampledat) ;
        $value_sql_dt = date("Y-m-d H:i:s", $sampledat) ;
        
        // Write the message under condition
        $low_notif_msg = 'The value of ' . $value. ' (' . $data_unit .  ') for sensor ' . $sensor_name 
        . ' in ' . $equipment_name . ' crossed the minimum threshold of ' . $low_notif_value 
        . ' on ' . $value_dt . '.' ;
        
        // INSERT data in alarm_sensor
        $query4 = "INSERT INTO alarm_sensor (message, user_id, sensor_id, equipment_id, company_id, created_dt) VALUES (" 
                  . "'" . $low_notif_msg . "'," . $user_id . ", " . $sensor_id 
                  . ", " . $equipment_id . ", " . $company_id . ",NOW()); " ;
        $result4 = mysqli_query($dbc_local, $query4) or trigger_error("Query: $query4\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
        
        // Update Notification date
        $query8 = "UPDATE settings SET notification_dt='$value_sql_dt' WHERE user_id = " . $user_id ;
        $result8 = mysqli_query($dbc_local, $query8) or trigger_error("Query: $query8\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
        
        // Send Email
        // If alarm email has been sent use it. if not use the user's email
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        //Server settings
        //$mail->SMTPDebug = 2;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'mail.********.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'alarm@********.com';                 // SMTP username
        $mail->Password = '********';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to
        //Recipients
        $mail->setFrom('info@scentroid.com', 'Notification');
        $mail->addAddress($e,$full_name);     // Add a recipient
        $mail->addReplyTo('info@********.com', 'Notification');
        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Notification' ;
        $mail->Body    = $low_notif_msg ;
        $mail->AltBody = $low_notif_msg ;
        $mail->send() ;
        
        // Send SMS
        // A Twilio number you own with SMS capabilities
        $twilio_number = "***********";
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
          // Where to send a text message (your cell phone?)
          $phone,
          array(
            'from' => $twilio_number,
            'body' => $low_notif_msg
          )
        ) ;
        
      }
      else
      {
        // Update Notification date to today if no alarm has been found
        $query8 = "UPDATE alarmcheckpoint_sensor SET low_notification_dt=NOW() 
                   WHERE alarmcheckpoint_sensor.id = " . $alarmcheckpoint_id ;
        $result8 = mysqli_query($dbc_local, $query8) or trigger_error("Query: $query8\n<br>MySQL Error: " . mysqli_error($dbc_local)) ;
      }
    }
  }
}

echo "OK" ;

// Close db
db_close($dbc) ;
db_close($dbc_local) ;

?>
