<?PHP
	require 'includes/master.inc.php';
	$Auth->requireAdmin('login.php');

	if(isset($_POST['btnCreateAccount']))
	{
		$Error->blank($_POST['username'], 'Username');
		$Error->blank($_POST['password'], 'Password');
		$Error->blank($_POST['level'], 'Level');
        $Error->email($_POST['email']);
		
		if($Error->ok())
		{
			$u = new User();
			$u->username   = $_POST['username'];
			$u->email      = $_POST['email'];
			$u->level      = $_POST['level'];
			$u->setPassword($_POST['password']);
			$u->insert();

            redirect('users.php');
		}
		else
		{
			$username = $_POST['username'];
			$email    = $_POST['email'];
			$level    = $_POST['level'];
		}
	}
	else
	{
		$username  = '';
		$email     = '';
		$level     = 'user';
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
</head>
<body class="rounded">
    <div id="doc3" class="yui-t6">

        <div id="hd">
            <?PHP include('inc/header.inc.php'); ?>
        </div>

        <div id="bd">
            <div id="yui-main">
                <div class="yui-b"><div class="yui-g">
					<?PHP echo $Error; ?>
                    <div class="block">
                        <div class="hd">
                            <h2>Create new user</h2>
                        </div>
                        <div class="bd">
							<form action="user-new.php" method="post">
								<p><label for="username">Username</label> <input type="text" name="username" id="username" value="<?PHP echo $username; ?>" class="text"></p>
								<p><label for="password">Password</label> <input type="password" name="password" id="password" value="" class="text"></p>
								<p><label for="email">Email</label> <input type="text" name="email" id="email" value="<?PHP echo $email; ?>" class="text"></p>
								<p><label for="level">Level</label>
								    <select name="level" id="level">
                                        <option <?PHP if($level == 'user') echo 'selected="selected"'; ?> value="user">User</option>
                                        <option <?PHP if($level == 'admin') echo 'selected="selected"'; ?> value="admin">Admin</option>
                                    </select>
                                </p>
								<p><input type="submit" name="btnCreateAccount" value="Create Account" id="btnCreateAccount"></p>
							</form>
						</div>
					</div>
              
                </div></div>
            </div>
            <div id="sidebar" class="yui-b">

            </div>
        </div>

        <div id="ft"></div>
    </div>
</body>
</html>
