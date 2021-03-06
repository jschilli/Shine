<?PHP
	require 'includes/master.inc.php';
	$Auth->requireAdmin('login.php');
	$nav = 'applications';
	
	$app = new Application($_GET['id']);
	if(!$app->ok()) redirect('index.php');

	if(isset($_POST['btnSaveApp']))
	{
		$Error->blank($_POST['name'], 'Application Name');

		if($Error->ok())
		{
			$app                   = new Application($_GET['id']);
			$app->name             = $_POST['name'];
			$app->link             = $_POST['link'];
			$app->bundle_name      = $_POST['bundle_name'];
			$app->i_use_this_key   = $_POST['i_use_this_key'];
			$app->s3key            = $_POST['s3key'];
			$app->s3pkey           = $_POST['s3pkey'];
			$app->s3bucket         = $_POST['s3bucket'];
			$app->s3path           = $_POST['s3path'];
			$app->sparkle_key      = $_POST['sparkle_key'];
			$app->sparkle_pkey     = $_POST['sparkle_pkey'];
			$app->ap_key           = $_POST['ap_key'];
			$app->ap_pkey          = $_POST['ap_pkey'];
			$app->custom_salt      = $_POST['custom_salt'];
			$app->license_type     = $_POST['license_type'];
			$app->from_email       = $_POST['from_email'];
			$app->email_subject    = $_POST['email_subject'];
			$app->email_body       = $_POST['email_body'];
			$app->license_filename = $_POST['license_filename'];
			$app->return_url       = $_POST['return_url'];
			$app->fs_security_key  = $_POST['fs_security_key'];
			$app->tweet_terms      = $_POST['tweet_terms'];
			$app->cf_product_code  = $_POST['cf_product_code'];
			$app->cf_license_url   = $_POST['cf_license_url'];
			$app->of_email_notify  = $_POST['of_email_notify'];
			$app->fs_license_key   = $_POST['fs_license_key'];
			
			$app->update();
			redirect('application.php?id=' . $app->id);
		}
		else
		{
			$name             = $_POST['name'];
			$link             = $_POST['link'];
			$bundle_name      = $_POST['bundle_name'];
			$i_use_this_key   = $_POST['i_use_this_key'];
			$s3key            = $_POST['s3key'];
			$s3pkey           = $_POST['s3pkey'];
			$s3bucket         = $_POST['s3bucket'];
			$s3path           = $_POST['s3path'];
			$sparkle_key      = $_POST['sparkle_key'];
			$sparkle_pkey     = $_POST['sparkle_pkey'];
			$ap_key           = $_POST['ap_key'];
			$ap_pkey          = $_POST['ap_pkey'];
			$custom_salt      = $_POST['custom_salt'];
			$license_type     = $_POST['license_type'];
			$from_email       = $_POST['from_email'];
			$email_subject    = $_POST['email_subject'];
			$email_body       = $_POST['email_body'];
			$license_filename = $_POST['license_filename'];
			$return_url       = $_POST['return_url'];
			$fs_security_key  = $_POST['fs_security_key'];
			$tweet_terms      = $_POST['tweet_terms'];
			$cf_product_code  = $_POST['cf_product_code'];
			$cf_license_url   = $_POST['cf_license_url'];
			$of_email_notify  = $_POST['of_email_notify'];
			$fs_license_key   = $_POST['fs_license_key'];
		}
	}
	else
	{
		$name             = $app->name;
		$link             = $app->link;
		$bundle_name      = $app->bundle_name;
		$i_use_this_key   = $app->i_use_this_key;
		$s3key            = $app->s3key;
		$s3pkey           = $app->s3pkey;
		$s3bucket         = $app->s3bucket;
		$s3path           = $app->s3path;
		$sparkle_key      = $app->sparkle_key;
		$sparkle_pkey     = $app->sparkle_pkey;
		$ap_key           = $app->ap_key;
		$ap_pkey          = $app->ap_pkey;
		$custom_salt      = $app->custom_salt;
		$license_type     = $app->license_type;
		$from_email       = $app->from_email;
		$email_subject    = $app->email_subject;
		$email_body       = $app->email_body;
		$license_filename = $app->license_filename;
		$return_url       = $app->return_url;
		$fs_security_key  = $app->fs_security_key;
		$tweet_terms      = $app->tweet_terms;
		$cf_product_code  = $app->cf_product_code;
		$cf_license_url   = $app->cf_license_url;
		$of_email_notify  = $app->of_email_notify;
		$fs_license_key   = $app->fs_license_key;
		
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Shine</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
    <link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
    <link rel="stylesheet" href="css/yuiapp.css" type="text/css">
	<link rel="alternate" type="application/rss+xml" title="Appcast Feed" href="appcast.php?id=<?PHP echo $app->id; ?>" />
</head>
<body class="rounded">
    <div id="doc3" class="yui-t6">

        <div id="hd">
            <?PHP include('inc/header.inc.php'); ?>
        </div>

        <div id="bd">
            <div id="yui-main">
                <div class="yui-b"><div class="yui-g">

                    <div class="block tabs spaces">
                        <div class="hd">
                            <h2>Applications</h2>
							<ul>
								<li class="active"><a href="application.php?id=<?PHP echo $app->id; ?>"><?PHP echo $app->name; ?></a></li>
								<li><a href="versions.php?id=<?PHP echo $app->id; ?>">Versions</a></li>
								<li><a href="pirates.php?id=<?PHP echo $app->id; ?>">Pirates</a></li>
								<li><a href="version-new.php?id=<?PHP echo $app->id; ?>">Release New Version</a></li>
							</ul>
							<div class="clear"></div>
                        </div>
                        <div class="bd">
							<?PHP echo $Error; ?>
							<form action="application.php?id=<?PHP echo $app->id; ?>" method="post">
								<h3>Basic Stuff</h3>
                                <p>
									<label for="name">Application Name</label>
                                    <input type="text" class="text" name="name" id="name" value="<?PHP echo $name; ?>">
                                </p>
                                <p>
									<label for="link">Info URL</label>
                                    <input type="text" class="text" name="link" id="link" value="<?PHP echo $link; ?>">
									<span class="info">Your application's product page</span>
                                </p>
                                <p>
                                    <label for="url">Bundle Name</label>
                                    <input type="text" class="text" name="bundle_name" id="bundle_name" value="<?PHP echo $bundle_name; ?>">
                                    <span class="info">Ex: MyApplication.app</span>
                                </p>
                                <p>
                                    <label for="url">i use this URL Key Slug</label>
                                    <input type="text" class="text" name="i_use_this_key" id="i_use_this_key" value="<?PHP echo $i_use_this_key; ?>">
                                    <span class="info">Ex: http://osx.iusethis.com/app/<strong>virtualhostx</strong></span>
                                </p>
                                <p>
                                    <label for="url">Twitter keywords to search for</label>
                                    <input type="text" class="text" name="tweet_terms" id="tweet_terms" value="<?PHP echo htmlspecialchars($tweet_terms); ?>">
                                    <span class="info">Seperate with commas</span>
                                </p>

                                <p>
                                    <label for="of_email_notify">Open Feedback Notification Address</label>
                                    <input type="text" class="text" name="of_email_notify" id="of_email_notify" value="<?PHP echo $of_email_notify; ?>">
                                    <span class="info">Optional email address to send <a href="http://github.com/tylerhall/OpenFeedback/">OpenFeedback</a> notifications</span>
                                </p>

								<hr>
								
								<h3>Amazon S3</h3>
                                <p>
									<label for="s3key">Amazon S3 Key</label>
                                    <input type="text" class="text" name="s3key" id="s3key" value="<?PHP echo $s3key; ?>">
                                </p>
                                <p>
									<label for="s3key">Amazon S3 Private Key</label>
                                    <input type="text" class="text" name="s3pkey" id="s3pkey" value="<?PHP echo $s3pkey; ?>">
                                </p>
                                <p>
									<label for="s3key">Amazon S3 Bucket Name</label>
                                    <input type="text" class="text" name="s3bucket" id="s3bucket" value="<?PHP echo $s3bucket; ?>">
                                </p>
                                <p>
									<label for="url">Amazon S3 Path</label>
                                    <input type="text" class="text" name="s3path" id="s3path" value="<?PHP echo $s3path; ?>">
									<span class="info">The directory in your bucket where you downloads will be stored</span>
                                </p>

								<hr>

								<h3>Sparkle</h3>
                                <p>
									<label for="sparkle_key">Sparkle Public Key</label>
                                    <textarea name="sparkle_key" id="sparkle_key" class="text"><?PHP echo $sparkle_key ?></textarea>
                                </p>
                                <p>
									<label for="sparkle_pkey">Sparkle Private Key</label>
                                    <textarea name="sparkle_pkey" id="sparkle_pkey" class="text"><?PHP echo $sparkle_pkey ?></textarea>
                                </p>

								<hr>

								<h3>Aquatic Prime</h3>
								<p>
									<label for="license_type">License Type</label><br>
                                    <select name="license_type" id="license_type">
										<option <?PHP if($license_type == 'ap') echo 'selected="selected"'; ?> value="ap">Aquatic Prime</option>
										<option <?PHP if($license_type == 'cf') echo 'selected="selected"'; ?> value="cf">CocoaFob</option>
										<option <?PHP if($license_type == 'custom') echo 'selected="selected"'; ?> value="custom">Custom</option>
									</select>
                                </p>

                                <p>
									<label for="ap_key">Aquatic Prime/CocoaFob Public Key</label>
                                    <textarea name="ap_key" id="ap_key" class="text"><?PHP echo $ap_key ?></textarea>
                                </p>
                                <p>
									<label for="ap_pkey">Aquatic Prime/CocoaFob Private Key</label>
                                    <textarea name="ap_pkey" id="ap_pkey" class="text"><?PHP echo $ap_pkey ?></textarea>
                                </p>

								<p>
									<label for="custom_salt">Custom License Salt (if not using Aquatic Prime)</label>
                                    <textarea name="custom_salt" id="custom_salt" class="text"><?PHP echo $custom_salt ?></textarea>
                                </p>
								<h3>Cocoa Fob</h3>

  							    <p>
									<label for="cf_product_code">CocoaFob Product Code</label>
                                    <input type="text" class="text" name="cf_product_code" id="cf_product_code" value="<?PHP echo $cf_product_code; ?>">
									<span class="info">Product Code used for CocoaFob licenses</span>
                                </p>
  							    <p>
									<label for="cf_license_url">CocoaFob License URL</label>
                                    <input type="text" class="text" name="cf_license_url" id="cf_license_url" value="<?PHP echo $cf_license_url; ?>">
									<span class="info">URL Prefix for CocoaFob licenses e.g. com.domainname.appname.lic</span>
                                </p>
								<hr>
								
                                <h3>PayPal</h3>
                                <p>
                                    <label for="return_url">PayPal Thanks URL</label>
                                    <input type="text" class="text" name="return_url" value="<?PHP echo $return_url; ?>" id="return_url">
                                </p>                                

                                <hr>

                                <h3>FastSpring</h3>
                                <p>
                                    <label for="return_url">Item Notification Security Key</label>
                                    <input type="text" class="text" name="fs_security_key" value="<?PHP echo $fs_security_key; ?>" id="fs_security_key">
									<span class="info">Security key from FastSpring used for order notifications</span>
                                </p>                                

                                <p>
                                    <label for="fs_license_key">License Request Security Key</label>
                                    <input type="text" class="text" name="fs_license_key" value="<?PHP echo $fs_license_key; ?>" id="fs_license_key">
									<span class="info">Security key from FastSpring used for requests (used with CocoaFob)</span>
                                </p>                                

                                <hr>
								
								<h3>Thank-you Email</h3>
								<p>
									<label for="from_email">From Email</label>
									<input type="text" class="text" name="from_email" value="<?PHP echo $from_email; ?>" id="from_email">
								</p>
								<p>
									<label for="email_subject">Email Subject</label>
									<input type="text" class="text" name="email_subject" value="<?PHP echo $email_subject; ?>" id="email_subject">
								</p>
                                <p>
									<label for="email_body">Email Body</label>
                                    <textarea name="email_body" id="email_body" class="text"><?PHP echo $email_body ?></textarea><br>
									<span class="info"><strong>Available Substitutions</strong>: {first_name}, {last_name}, {payer_email}, {license}. Add your own in includes/class.objects.php getBody().</span>
                                </p>

								<p>
									<label for="license_filename">License Filename</label>
									<input type="text" class="text" name="license_filename" value="<?PHP echo $license_filename; ?>" id="license_filename">
								</p>

								<p><input type="submit" name="btnSaveApp" value="Save Application" id="btnSaveApp"></p>
							</form>
						</div>
					</div>
              
                </div></div>
            </div>
            <div id="sidebar" class="yui-b">
                <div class="block">
                    <div class="hd">
                        <h3>i use this</h3>
                    </div>
                    <div class="bd">
                        <?PHP echo $app->iUseThisHTML(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="ft"></div>
    </div>
</body>
</html>
