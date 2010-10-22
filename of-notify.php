<?PHP
    require 'includes/master.inc.php';
    // error_log(print_r($_POST, true));
    //     error_log($_SERVER['REQUEST_URI']);
    
    
    // an ounce of prevention...
    function cleanerString($input)
    {
        if (empty($input))
            return '';

        $badStuph = array('to:', 'cc:', 'bcc:', 'from:', 'return-path:', 'content-type:', 'mime-version:', 'multipart-mixed:', 'content-transfer-encoding:');

        // if any bad things are found don't use the input at all (as there may be other unknown bad things)
        foreach ($badStuph as $badThing)
            if (stripos($input, $badThing) !== false)
                return 'Found bad things';

        // these aren't technically bad things by themselves, but clean them up for good measure
        //$input = str_replace(array("\r", "\n", "%0d", "%0a"), ' ', $input);
        
        return trim($input);
    }
function str_hex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}

    // Don't put user submitted email addresses in the From or Return-Path headers,
    // if your mail server is down it will bounce back to that address.
    // A malicious person could send spam that way.
    // Better to use an account at a seperate email provider so you won't miss a report.
    function sendReport($from, $subject, $message)
    {
        $from = cleanerString($from);
        $to       = 'support+tickets@manicwave.com';
        $from     = $from;
        $headers  = "From: {$from}\r\n";
        $headers .= "Return-Path: {$from}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        //j$headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        $headers .= "Content-Type: text/plain; charset=\"utf-8\"\n";

        if (empty($message))
            $message = 'There is no message';

        $subject = cleanerString($subject);
        if (empty($subject))
            $subject = 'There is no subject';
    
        //$message = "<html><body>" . nl2br($message) . "</body></html>";

        return mail($to, $subject, $message, $headers);
    }

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
        $message .= "Importance: {$_POST['importance']}\n";
        $message .= "Application Name: {$_POST['appname']}\n";
        $message .= "Version:{$_POST['appversion']}\n";
        $message .= "System Version:{$_POST['systemversion']}\n";
        $message .= "Type:{$_POST['type']}\n";
        $msg = str_replace("\\n", "\n", $_POST['message']);
        $message .= "Message:" . $msg . "\n";
        $message .= "Importance:{$_POST['importance']}\n";
        $message .= "Criticality:{$_POST['critical']}\n";
//  error_log(str_hex($_POST['message']));
//  error_log($msg);

    if (eregi('^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$', $_POST['email'])) {
        $email = $_POST['email'];
    } else {    
           $email = 'support@manicwave.com';
        }
    
    sendReport($email,"Feedback from {$_POST['appname']}",$message);
    // }
    

    echo "ok";
?>
