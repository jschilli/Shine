<?PHP
    // Stick your DBOjbect subclasses in here (to help keep things tidy).

    class User extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('users', array('username', 'password', 'level', 'email'), $id);
        }

        /**
         * Give the user object the ability to change the password with encryption.
         *
         * @param string $password The plain text string value of the password.
         */
        public function setPassword($password)
        {
            $Config = Config::getConfig();

            if($Config->useHashedPasswords === true)
                $this->password = sha1($password . $Config->authSalt);
            else
                $this->password = $password;
        }
    }

    class Application extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('applications', array('name', 'link', 'bundle_name', 's3key', 's3pkey', 's3bucket', 's3path', 'sparkle_key', 'sparkle_pkey', 'ap_key', 'ap_pkey', 'from_email', 'email_subject', 'email_body', 'license_filename', 'custom_salt', 'license_type', 'return_url', 'fs_security_key', 'i_use_this_key', 'tweet_terms', 'cf_product_code', 'cf_license_url'), $id);
        }

		public function versions()
		{
			return DBObject::glob('Version', "SELECT * FROM versions WHERE app_id = '{$this->id}' ORDER BY dt DESC");
		}

		public function strCurrentVersion()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT version_number FROM versions WHERE app_id = '{$this->id}' ORDER BY dt DESC LIMIT 1");
		}
		
		public function strLastReleaseDate()
		{
			$db = Database::getDatabase();
			$dt = $db->getValue("SELECT dt FROM versions WHERE app_id = '{$this->id}' ORDER BY dt DESC LIMIT 1");
			return time2str($dt);
		}
		
        public function totalDownloads()
        {
            $db = Database::getDatabase();
            return $db->getValue("SELECT SUM(downloads) FROM versions WHERE app_id = '{$this->id}'");
        }

        public function totalUpdates()
        {
            $db = Database::getDatabase();
            return $db->getValue("SELECT SUM(updates) FROM versions WHERE app_id = '{$this->id}'");
        }

        public function totalPirates()
        {
            $db = Database::getDatabase();
            return $db->getValue("SELECT SUM(pirate_count) FROM versions WHERE app_id = '{$this->id}'");
        }
		
		public function numSupportQuestions()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM feedback WHERE appname = '{$this->name}' AND `type` = 'support' AND new = 1");
		}
		
		public function numBugReports()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM feedback WHERE appname = '{$this->name}' AND `type` = 'bug' AND new = 1");
		}
		
		public function numFeatureRequests()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM feedback WHERE appname = '{$this->name}' AND `type` = 'feature' AND new = 1");
		}
		
		function getCFLicenseURL($order)
		{
			$composite_name = $order->first_name . ' ' . $order->last_name;
			
			return $this->cf_license_url  . '://' . base64_encode($composite_name) . '/' . $order->license;
		}

		function getBody($order)
		{
			$cf_license_url = $this->getCFLicenseURL($order);
			$mail_body = str_replace('{cf_license_url}', $cf_license_url, $this->email_body);
			return str_replace(array('{first_name}', '{last_name}', '{payer_email}', '{license}'), array($order->first_name, $order->last_name, $order->payer_email, $order->license), $mail_body);
		}
		
		function ordersPerMonth()
		{
			$db = Database::getDatabase();			

			$orders = $db->getRows("SELECT DATE_FORMAT(dt, '%Y-%m') as dtstr, COUNT(*) FROM orders WHERE type = 'PayPal' AND app_id = '{$this->id}' GROUP BY CONCAT(YEAR(dt), '-', MONTH(dt)) ORDER BY YEAR(dt) ASC, MONTH(dt) ASC");
			$keys = gimme($orders, 'dtstr');
			$values = gimme($orders, 'COUNT(*)');
			$orders = array();
			for($i = 0; $i < count($keys); $i++)
				$orders[$keys[$i]] = $values[$i];
				
			$first_order_date = $db->getValue("SELECT dt FROM orders ORDER BY dt ASC LIMIT 1");
			list($year, $month) = explode('-', dater($first_order_date, 'Y-n'));

			do
			{
				$month = str_pad($month, 2, '0', STR_PAD_LEFT);
				if(!isset($orders["$year-$month"]))
					$orders["$year-$month"] = 0;
				
				$month = intval($month) + 1;
				if($month == 13)
				{
					$month = 1;
					$year++;
				}
			}
			while($year <> date('Y') && $month <> date('m'));
			
			ksort($orders);
			return $orders;
		}
		
		public function iUseThisHTML()
		{
		    $html = file_get_contents("http://osx.iusethis.com/app/include/{$this->i_use_this_key}/2");
		    $count = preg_replace('/[^0-9]/', '', strip_tags($html));
		    $result = "<div style=\"width: 160px;background: no-repeat url(http://osx.iusethis.com/static/badges/ucb2.png); height: 43px; cursor: pointer;\"><a href='http://osx.iusethis.com/app/{$this->i_use_this_key}'><div style=\"color: #383838; font: 14px Geneva, Arial, Helvetica, sans-serif; position: relative; top: 14px;    left: 45px; font-weight: bold; text-align: left;\">$count<span style=\"color:#7a7a7a; font:12px;\">usethis</span></div></a></div>";
		    return $result;
	    }
    }

    class Order extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('orders', array('app_id', 'dt', 'txn_type', 'first_name', 'last_name', 'residence_country', 'item_name', 'payment_gross', 'mc_currency', 'business', 'payment_type', 'verify_sign', 'payer_status', 'tax', 'payer_email', 'txn_id', 'quantity', 'receiver_email', 'payer_id', 'receiver_id', 'item_number', 'payment_status', 'payment_fee', 'mc_fee', 'shipping', 'mc_gross', 'custom', 'license', 'type', 'deleted', 'hash', 'claimed'), $id);
        }

		public function applicationName()
		{
			static $cache;
			if(!is_array($cache)) $cache = array();

			if(!isset($cache[$this->app_id]))
			{
				$app = new Application($this->app_id);
				$cache[$this->app_id] = $app->name;
			}
			
			return $cache[$this->app_id];
		}
		
		function generateLicense()
		{
			$app = new Application($this->app_id);
			if($app->license_type == 'ap')
				$this->generateLicenseAP();
			else if($app->license_type=='cf') 
					$this->generateLicenseCF();
			else
				$this->generateLicenseCustom();
		}

		function generateLicenseCustom()
		{
			$app = new Application($this->app_id);
			$arr = array('email' => utf8_encode($this->payer_email));

			$str = '';
			ksort($arr);
			foreach($arr as $k => $v) $str .= $v;

			$this->license = strtoupper(md5($str . $app->custom_salt));
			$this->update();
		}
		
		function generateLicenseAP()
		{
			// Much of the following code is adapted/copied from AquaticPrime's PHP library...

			// Create our license dictionary to be signed
			$dict = array("Product"       => $this->item_name,
						  "Name"          => utf8_encode($this->first_name . ' ' . $this->last_name),
						  "Email"         => utf8_encode($this->payer_email),
						  "Licenses"      => $this->quantity,
						  "Timestamp"     => date('r', strtotime($this->dt)),
						  "TransactionID" => $this->txn_id);

			// $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
			// $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");

			// foreach($dict as $k => $v)
			// 	$dict[$k] = str_replace($search, $replace, $v);

			$app = new Application($this->app_id);
		    $sig = chunk_split(getSignature($dict, $app->ap_key, $app->ap_pkey));

		    $plist = "<?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n";
		    $plist .= "<!DOCTYPE plist PUBLIC \"-//Apple Computer//DTD PLIST 1.0//EN\" \"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">\n";
		    $plist .= "<plist version=\"1.0\">\n<dict>\n";
		    foreach($dict as $key => $value)
			{
				$value = utf8_encode($value);
		        $plist .= "\t<key>" . htmlspecialchars($key, ENT_NOQUOTES) . "</key>\n";
		        $plist .= "\t<string>" . htmlspecialchars($value, ENT_NOQUOTES) . "</string>\n";
		    }
		    $plist .= "\t<key>Signature</key>\n";
		    $plist .= "\t<data>$sig</data>\n";
		    $plist .= "</dict>\n";
		    $plist .= "</plist>\n";

			$this->license = $plist;
			$this->update();
		}
		
			function generateLicenseCF()
			{
			
				$app = new Application($this->app_id);

				$priv = openssl_pkey_get_private($app->ap_pkey);

				$signedData = '';
				$product_code = $app->cf_product_code; 
				$name = $this->first_name . ' ' . $this->last_name;
				$compositeLicenseCode = make_license_source($product_code,$name);
				error_log("[$product_code] - [$name] -- [$compositeLicenseCode]");
				openssl_sign($compositeLicenseCode, $signature, $priv, OPENSSL_ALGO_DSS1);
				openssl_free_key($priv);
				$len = strlen($signature);

				$b32 = encode($signature);
			  	// # Replace Os with 8s and Is with 9s
			  	// # See http://members.shaw.ca/akochoi-old/blog/2004/11-07/index.html
				$b32 =  str_replace('O', '8', $b32);
				$b32 =  str_replace('I', '9', $b32);
				$b32 = join("-",str_split($b32,5));
		
				$this->license = $b32;
				$this->update();
			}
		
		function emailLicense()
		{
			$app = new Application($this->app_id);
			if($app->license_type == 'ap')
				$this->emailLicenseAP();
			else if($app->license_type=='cf') 
				$this->emailLicenseCF();
			else
				$this->emailLicenseCustom();
		}
		
		public function emailLicenseCustom()
		{
			$app = new Application($this->app_id);
			mail($this->payer_email, $app->email_subject, $app->getBody($this), "From: {$app->from_email}");
		}

		public function emailLicenseAP()
		{
			$app = new Application($this->app_id);

			// Create a random boundary
			$boundary = base64_encode(md5(rand()));

			$headers  = "From: {$app->from_email}\n";
			$headers .= "X-Mailer: PHP/" . phpversion() . "\n";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\n";
			$headers .= "Content-Transfer-Encoding: 7bit\n\n";
			$headers .= "This is a MIME encoded message.\n\n";

			$headers .= "--$boundary\n";

			$headers .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
			$headers .= "Content-Transfer-Encoding: 7bit\n\n";
			$headers .= $app->getBody($this) . "\n\n\n";

			$headers .= "--$boundary\n";

			$headers .= "Content-Type: application/octet-stream; name=\"{$app->license_filename}\"\n";
			$headers .= "Content-Transfer-Encoding: base64\n";
			$headers .= "Content-Disposition: attachment\n\n";

		    $headers .= chunk_split(base64_encode($this->license))."\n";

		    $headers .= "--$boundary--";

			mail($this->payer_email, $app->email_subject, '', utf8_encode($headers));
		}
		
		public function emailLicenseCF()
		{
			require('Mail.php');
			require('Mail/mime.php');
			$app = new Application($this->app_id);
error_log($app->getBody($this));
			$text = 'Text version of email';
			$html = '<html><body>HTML version of email</body></html>';
			$file = '/home/richard/example.php';
			$crlf = "\n";
			$hdrs = array(
			              'From'    => 'orders@manicwave.com',
			              'Subject' => 'Test mime message'
			              );

			$mime = new Mail_mime($crlf);

			$mime->setTXTBody($text);
			$mime->setHTMLBody($html);
			$mime->addAttachment($file, 'text/plain');

			//do not ever try to call these lines in reverse order
			$body = $mime->get();
			$hdrs = $mime->headers($hdrs);

			$mail =& Mail::factory('mail');
			$mail->send($this->payer_email, $hdrs, $body);
			
			// $app = new Application($this->app_id);
			// 	
			//  			$hdrs = array('From' => $app->from_email, 'Subject' => $app->email_subject);
			// 	
			// $mime = new Mail_mime("\r\n");
			// $mime->setTXTBody($app->getBody($this));
			// $mime->setHTMLBody('');
			// // $mime->addAttachment($tmp, 'application/octet-stream', $app->license_filename, true, 'base64');
			// 	
			// $body = $mime->get();
			// $hdrs = $mime->headers($hdrs);
			// 	
			// $smtp =& Mail::factory('smtp', array('host' => SMTP_HOST, 'port' => SMTP_PORT, 'auth' => true, 'username' => SMTP_USERNAME, 'password' => SMTP_PASSWORD));
			// $mail = $smtp->send($this->payer_email, $hdrs, $body);
		}

		// This method is an alternative to your box's native sendmail. If you have access, I'd recommend using
		// your company Gmail account to send - as that will help your emails get through spam filters. To setup,
		// add these lines to the production() method in class.config.php
		// define('SMTP_USERNAME', 'your@email.com');
		// define('SMTP_PASSWORD', 'some-password');
		// define('SMTP_HOST', 'ssl://smtp.gmail.com');
		// define('SMTP_PORT', 465);
		// You'll also need to install PEAR's Mail and Mail_Mime extensions.
		//
		// public function emailLicenseSMTP()
		// {
		// 	$app = new Application($this->app_id);
		// 
		// 	$tmp = tempnam('/tmp', 'foo');
		// 	file_put_contents($tmp, $this->license);
		// 
		//  			$hdrs = array('From' => $app->from_email, 'Subject' => $app->email_subject);
		// 
		// 	$mime = new Mail_mime("\r\n");
		// 	$mime->setTXTBody($app->getBody($this));
		// 	$mime->setHTMLBody('');
		// 	// $mime->addAttachment($tmp, 'application/octet-stream', $app->license_filename, true, 'base64');
		// 
		// 	$body = $mime->get();
		// 	$hdrs = $mime->headers($hdrs);
		// 
		// 	$smtp =& Mail::factory('smtp', array('host' => SMTP_HOST, 'port' => SMTP_PORT, 'auth' => true, 'username' => SMTP_USERNAME, 'password' => SMTP_PASSWORD));
		// 	$mail = $smtp->send($this->payer_email, $hdrs, $body);
		// }
		
		public function downloadLicense()
		{
			$app = new Application($this->app_id);
			header("Cache-Control: public");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Content-Type: application/x-download"); // Stupid fix for Safari not honoring content-disposition
			header("Content-Length: " . strlen($this->license));
			header("Content-Disposition: attachment; filename={$app->license_filename}");
			header("Content-Transfer-Encoding: binary");
			echo $this->license;
			exit;
		}
		
		public function intlAmount()
		{
			$currencies = array('USD' => '$', 'GBP' =>'£', 'EUR' => '€', 'CAD' => '$', 'JPY' => '¥');
			
			if($this->mc_currency == '') return '';
			
			return $currencies[$this->mc_currency] . number_format($this->mc_gross, 2);
		}
		
		public static function totalOrders($id = null)
		{
			$db = Database::getDatabase();
			if(is_null($id))
				return $db->getValue("SELECT COUNT(*) FROM orders WHERE type = 'paypal'");
			else
				return $db->getValue("SELECT COUNT(*) FROM orders WHERE type = 'paypal' AND app_id = " . intval($id));
		}
    }

    class Version extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('versions', array('app_id', 'human_version', 'version_number', 'dt', 'release_notes', 'filesize', 'url', 'downloads', 'updates', 'signature', 'pirate_count'), $id);
        }
    }

	class Feedback extends DBObject
	{
		function __construct($id = null)
		{
			parent::__construct('feedback', array('appname', 'appversion', 'systemversion', 'email', 'reply', 'type', 'message', 'importance', 'critical', 'dt', 'ip', 'new', 'starred', 'reguser', 'regmail'), $id);
		}
	}

    class Serial extends DBObject
    {
        function __construct($id = null)
        {
            parent::__construct('pirated_serials', array('app_id', 'dt', 'guid', 'serial'), $id);
        }
    }

    class Tweet extends DBObject
    {
        function __construct($id = null)
        {
            parent::__construct('tweets', array('tweet_id', 'app_id', 'username', 'dt', 'body', 'profile_img', 'new', 'replied_to', 'reply_date', 'deleted'), $id);
        }
    }
