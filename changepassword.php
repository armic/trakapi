<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>HenchhmanTRAK > Sign up</title>
    <link href="css/style.css" type="text/css" rel="stylesheet" />
</head>
<body>
<h2>Password Recovery</h2>
<p>Welcome back, </p>
<p>In the fields below, enter your new password.</p>
<form action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="fieldGroup"><label for="pw0">New Password</label><div class="field"><input type="password" class="input" name="pw0" id="pw0" value="" maxlength="20"></div></div>
    <div class="fieldGroup"><label for="pw1">Confirm Password</label><div class="field"><input type="password" class="input" name="pw1" id="pw1" value="" maxlength="20"></div></div>
    <input type="hidden" name="subStep" value="3" />
    <input type="hidden" name="key" value="<?= $_GET['email']=='' ? $_POST['key'] : $_GET['email']; ?>" />
    <div class="fieldGroup"><input type="submit" value="Submit" style="margin-left: 150px;" /></div>
    <div class="clear"></div>
</form>
</body>
</html>