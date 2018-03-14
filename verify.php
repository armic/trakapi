<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>HenchhmanTRAK > Sign up</title>
</head>
<body>
<!-- start header div -->
<div id="header">
    <h3>HenchmanTRAK > Sign up</h3>
</div>
<!-- end header div -->

<!-- start wrap div -->
<div id="wrap">
    <!-- start PHP code -->
    <?php

           //

          $db = mysqli_connect("localhost", "root", "", "AuditTRAK") or die(mysqli_error($db)); // Connect to database server(localhost) with username and password.

             if (mysqli_connect_errno())
                {
                    echo "Failed to connect to MySQL: " . mysqli_connect_error($db);
                }


            if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash']))
                {
                    // Verify data
                    $email = mysqli_escape_string($db,$_GET['email']); // Set email variable
                    $hash =  mysqli_escape_string($db, $_GET['hash']); // Set hash variable


                    $search = mysqli_query($db, "SELECT email, hash, verified FROM employees WHERE email = '".$email."' AND hash='".$hash."' AND verified=0") or die(mysqli_error($db));
                    $match  = mysqli_num_rows($search);

                    // Check for match

                    if($match > 0 ) {


                        // We have a match, activate the account
                            mysqli_query($db,"UPDATE employees SET verified= 1 WHERE email='".$email."' AND hash= '".$hash."' AND verified= 0") or die(mysqli_error($db));
                            echo '<div">Your account has been activated, you can login after you are GRANTED access to AuditTRAK.</div>';
                        }else{
                         // No match -> invalid url or account has already been activated.
                            echo '<div">The url is either invalid or you already have activated your account.</div>';
                            }


                } else {

                echo "<div>Invalid approach, please use the link that has been send to your email.</div>";
            }

?>


</div>
<!-- end wrap div -->
</body>
</html>