<?PHP
	require 'includes/master.inc.php';
	
	error_log(print_r($_POST, true));
error_log($_SERVER['REQUEST_URI']);
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
	

	$app_id = DBObject::glob('Application', "SELECT id FROM applications WHERE name = '{$_POST['appname']}' ");
	$app = new Application($app_id);
	// if (!is_null($app->of_email_notify)) {
		// Format email to external system
		$full_url = full_url_for_page('feedback-view.php');
		$message  = "{$_POST['type']} case: " . "$full_url?id=$feedback_id \n";
		$message .= "Message: {$_POST['message']}\n";

		error_log($message);
	// }
	

	echo "ok";
