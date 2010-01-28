<?PHP
	require 'includes/master.inc.php';
	require('Mail.php');
	require('Mail/mime.php');
	error_log(print_r($_POST, true));
	
	$db = Database::getDatabase();

	foreach($_POST as $key => $val)
		$_POST[$key] = mysql_real_escape_string($val, $db->db);

	$dt = date('Y-m-d H:i:s');

	$query = "INSERT INTO feedback (appname, appversion, systemversion, email, reply, `type`, message, importance, critical, dt, ip, `new`, reguser, regmail) VALUES
                  ('{$_POST['appname']}',
                   '{$_POST['appversion']}',
                   '{$_POST['systemversion']}',
                   '{$_POST['email']}',
                   '{$_POST['reply']}',
                   '{$_POST['type']}',
                   '{$_POST['message']}',
                   '{$_POST['importance']}',
                   '{$_POST['critical']}',
                   '$dt',
                   '{$_SERVER['REMOTE_ADDR']}',
                   '1',
                   '{$_POST['reguser']}',
				   '{$_POST['regmail']}')";

	mysql_query($query, $db->db) or die('error');
	$feedback_id = $db->insertId();
	
	$app_id = $db->getValue("SELECT id FROM applications WHERE name = '{$_POST['appname']}' LIMIT 1");
	
	$app = new Application($app_id);
	error_log("$app_id name:$app->name");
	if (eregi('^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$', $_POST['email'])) {
	    $email = $_POST['email'];
	} else {    
        $email = $app->of_email_notify;
    }

	// Format email to external system
	$full_url = full_url_for_page('feedback-view.php');
	$message  = "{$_POST['type']} case: " . "<a href=\"$full_url?id=$feedback_id\">Feedback $feedback_id</a> \n";
	$message .= "Message: {$_POST['message']}\n";


		
	$html = "<html><body>" . $message . "</body></html>";
		
	$crlf = "\n";
	$hdrs = array(
	              'From'    => $email,
	              'Subject' => "Feedback for $app->name"
	              );
	$mime = new Mail_mime($crlf);

	$mime->setTXTBody($message);
	$mime->setHTMLBody($html);

	//do not ever try to call these lines in reverse order
	$body = $mime->get();
	$hdrs = $mime->headers($hdrs);
error_log("to: $email from: $app->of_email_notify message: $message");
	$smtp =& Mail::factory('smtp', array('host' => SMTP_HOST, 'port' => SMTP_PORT, 'auth' => true, 'username' => SMTP_USERNAME, 'password' => SMTP_PASSWORD));
	$mail = $smtp->send($app->of_email_notify, $hdrs, $body);

	if (PEAR::isError($mail))
	  error_log($mail->getMessage());
?>
