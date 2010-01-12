<?PHP
/**
* License generation script for cocoaFob based implementations using FastSpring
* We'll generate the license here (protects us from embedding our private key @ FastSpring)
* We'll configure FastSpring to send it back to us with the completed order @ which time we'll add 
* it to the order
*/
    require 'includes/master.inc.php';

    $app = new Application();
    $app->select($_POST['item_number']); // custom
    if(!$app->ok())
    {
        error_log("Application {$_POST['item_name']} {$_POST['item_number']} not found!");
        exit;
    }
    
    // FastSpring security check...
    if(md5($_REQUEST['security_data'] . $app->fs_security_key) != $_REQUEST['security_hash'])
        die('Security check failed.');
    

    $name                 = $_POST['name'];
	$quantity			  = $_POST['quantity'];
	
	$product_code = $app->cf_product_code; 
	$compositeLicenseCode = make_license_source($product_code,$name);
	error_log("[$product_code] - [$name] -- [$compositeLicenseCode]");
	
	$priv = openssl_pkey_get_private($app->ap_pkey);
	$license = make_phpFob_license($compositeLicenseCode, $priv);
	echo $license;

    // These are the fields and values you'll need to setup in FastSpring's
    // license generation system.
    // by default, 'name' comes through as a composite name
    // quantity is also set.
    // The following is a custom parameter setup on the parameter overrides page
    // item_number          3 <-- this is the Shine ID number of your product - 
?>