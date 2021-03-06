<?php
/**
 * Henchman Products PTY.  Standard Copyright and Disclaimer Notice:
 *
 * Copyright ©2018. Henchman Products PTY.  All Rights Reserved. Permission to use, copy, modify, and distribute this
 * software and its documentation for educational, research, and not-for-profit purposes, without fee and without a signed
 * licensing agreement, is hereby granted, provided that the above copyright notice, this paragraph and the following two
 * paragraphs appear in all copies, modifications, and distributions.
 *
 * IN NO EVENT SHALL HENCHMAN  BE LIABLE TO ANY PARTY FOR DIRECT, INDIRECT, SPECIAL, INCIDENTAL, OR
 * CONSEQUENTIAL DAMAGES, INCLUDING LOST PROFITS, ARISING OUT OF THE USE OF THIS SOFTWARE AND ITS
 * DOCUMENTATION, EVEN IF REGENTS HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * HENCHMAN SPECIFICALLY DISCLAIMS ANY WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE SOFTWARE AND
 * ACCOMPANYING DOCUMENTATION, IF ANY, PROVIDED HEREUNDER IS PROVIDED "AS IS".HENCHMAN
 *  HAS NO OBLIGATION TO PROVIDE MAINTENANCE, SUPPORT, UPDATES, ENHANCEMENTS, OR MODIFICATIONS.
 */

/**
 * Created by PhpStorm.
 * User: artolentino
 * Date: 2/2/18
 * Time: 10:27 AM
 */


/**
 * Contents
 * $app->get('/api/user/login', function(Request $request, Response $response)
 * $app->get('/api/users/{custid}', function(Request $request, Response $response)
 * $app->post('/api/user/grant/{userid}/{custid}', function(Request $request, Response $response)
 * $app->post('/api/user/revoke/{userid}/{custid}', function(Request $request, Response $response)
 * $app->post('/api/user/register', function(Request $request, Response $response)
 */

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

// Register
//

$app->post('/api/user/register/{custid}/{firstname}/{lastname}/{email}/{username}/{password}', function(Request $request, Response $response) {
    // Parameters
    $custid = $request->getAttribute('custid');
    $firstname = $request->getAttribute('firstname');
    $lastname = $request->getAttribute('lastname');
    $email = $request->getAttribute('email');
    $username = $request->getAttribute('username');
    $password = md5($request->getAttribute('password'));
    $createddate = date("Y-m-d h:i:sa");
    $verified = 0;
    $hash = md5( rand(0,1000) ); // Generate random 32 character hash and assign it to a local variable.


    if(isset($custid) && !empty($custid) AND isset($firstname) && !empty($firstname) AND isset($lastname) && !empty($lastname) AND isset($username) && !empty($username) AND isset($password) && !empty($password) AND isset($email) && !empty($email)){

        // Form Submited
        if(preg_match("/^[^@]+@[^@]+\.[a-z]{2,6}$/i",$email)) {
            //
            // Check for duplicate email or username
            // isUsernameExist($username)
            // isEmployeeEmailExist($email)
            $trk = new clstrak();

            // Check username

            if($trk->isUsernameExist($username)) {

                echo '{ "error" : "username is in use" }';
                exit(0);
            }

            // Check email
            if($trk->isEmployeeEmailExist($email)) {

                echo '{ "error" : "email is in use" }';
                exit(0);
            }

            // Passed all

            $sql = "INSERT INTO employees (firstname, lastname, email, custid,createddate,username,password, verified, hash) VALUES
            (:firstname, :lastname,:email, :custid, :createddate, :username, :password, :verified, :hash)";
            try{
                // Get DB object
                $db= new db();
                $db = $db->connect();
                $stmt = $db->prepare($sql);

                $stmt->bindParam(':firstname', $firstname);
                $stmt->bindParam(':lastname', $lastname);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':createddate', $createddate);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':custid', $custid);
                $stmt->bindParam(':verified', $verified);
                $stmt->bindParam(':hash', $hash);



                $stmt->execute();
                $db = null;

                //
                //
                // Send an email

                $url = "http://localhost/auditrak/verify.php?email=" .$email."&hash=".$hash;
                echo $url;

                $to      = $email; // Send email to our user
                $subject = "Henchmantrak Signup | Verification"; // Give the email a subject
                $message = "Thanks for signing up! Your account has been created \n".
                           "with the credentials below. Please follow the url below to  \n".
                           "activated your account.\n \n \n" .
                           "------------------------\n".
                           "Username: " .$username. "\n".
                           "Username: " .$password. "\n".
                           "------------------------\n \n \n".
                           "Please click this link to activate your account: \n".
                           "http://localhost/auditrak/verify.php?email=" .$email."&hash=".$hash;



                $headers = 'From:support@henchmantrak.com' . "\r\n"; // Set from headers
                mail($to, $subject, $message, $headers); // Send our email


                echo '{"notice": {"Message": "Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below."}';

            }catch(PDOException $e){
                echo '{ "error" : "Registration failed. Please try again" }';
               // echo '{"error": {"text": '.$e->getMessage().'}';

            }


        }else{

            echo '{ "error" : "Invalid email Address" }';
        }

    } else {
        echo '{ "error" : "All fields are required!" }';
    }


});



// Forgot password
//

$app->post('/api/user/forgotpassword/{email}', function(Request $request, Response $response) {

    // Parameters
    $email = $request->getAttribute('email');


    if(isset($email) && !empty($email)) {

        // Form Submitted
        if (preg_match("/^[^@]+@[^@]+\.[a-z]{2,6}$/i", $email)) {
            //
            // isEmployeeEmailExist($email)
            //
            $trk = new clstrak();


            // Check email
            if ($trk->isEmployeeEmailExist($email)) {

                // Send an email

                $to = $email; // Send email to our user
                $subject = "Henchmantrak  | Change password"; // Give the email a subject
                $message = "To reset your password , pleae follow the url below \n" .

                    "http://localhost/changepassword.php?email=" . $email ;


                $headers = 'From:support@henchmantrak.com' . "\r\n"; // Set from headers
                mail($to, $subject, $message, $headers); // Send our email


                echo '{"notice": {"Message": "Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below."}';

            } else {
                echo '{ "error" : "Email does not exist." }';

            }


        } else {
            echo '{ "error" : "Invalid email address" }';
        }
    } else {

        echo '{ "error" : "Email is required!" }';
    }


});

// Login
$app->get('/api/user/login', function(Request $request, Response $response) {

    $username = $request->getParam('username');
    $password = $request->getParam('password');
    // Select statement\


    $sql =  "SELECT\n".
            "users.active,\n".
            "users.auditrak,\n".
            "users.role,\n".
            "users.`level`,\n".
            "users.custid,\n".
            "users.userid,\n".
            "employees.custid,\n".
            "employees.firstname,\n".
            "employees.lastname,\n".
            "employees.username,\n".
            "employees.password,\n".
            "employees.email,\n".
            "employees.photo,\n".
            "employees.mobilenumber,\n".
            "customers.`name`,\n".
            "customers.contactperson,\n".
            "customers.email,\n".
            "customers.address\n".
            "FROM\n".
            "users\n".
            "LEFT JOIN employees ON employees.id = users.userid\n".
            "LEFT JOIN customers ON customers.id = employees.custid\n".
            "WHERE\n".
            "users.auditrak = 1 AND\n".
            "users.active = 1 AND\n".
            "employees.password = '". md5($password). "'";


      // Check username
      if (strpos($username,'@')) {
         $sql = $sql." AND employees.email = '$username'";
      } else {
         $sql = $sql." AND employees.username = '$username'";

      }


    $user = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if($user) {
            echo  '{"success":' . json_encode($user).'}';
            $user = null;
            $db = null;
        } else {
            echo '{ "error" : "User doesn\'t exist" }';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});


// Login Admin
$app->get('/api/admin/login', function(Request $request, Response $response) {

    $username = $request->getParam('username');
    $password = $request->getParam('password');
    // Select statement\


    $sql =  "SELECT\n".
        "users.active,\n".
        "users.auditrak,\n".
        "users.role,\n".
        "users.`level`,\n".
        "users.custid,\n".
        "users.userid,\n".
        "employees.custid,\n".
        "employees.firstname,\n".
        "employees.lastname,\n".
        "employees.username,\n".
        "employees.password,\n".
        "employees.email,\n".
        "employees.photo,\n".
        "employees.mobilenumber,\n".
        "customers.`name`,\n".
        "customers.contactperson,\n".
        "customers.email,\n".
        "customers.address\n".
        "FROM\n".
        "users\n".
        "LEFT JOIN employees ON employees.id = users.userid\n".
        "LEFT JOIN customers ON customers.id = employees.custid\n".
        "WHERE\n".
        "users.auditrak = 1 AND\n".
        "users.role = 1 AND\n".
        "employees.password = '". md5($password). "'";


    // Check username
    if (strpos($username,'@')) {
        $sql = $sql." AND employees.email = '$username'";
    } else {
        $sql = $sql." AND employees.username = '$username'";

    }


    $user = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if($user) {
            echo  '{"success":' . json_encode($user).'}';
            $user = null;
            $db = null;
        } else {
            echo '{ "error" : "User doesn\'t exist" }';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Get user list


$app->get('/api/users/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT DISTINCT\n".
            "employees.id,\n".
            "employees.firstname,\n".
            "employees.lastname,\n".
            "employees.username,\n".
            "employees.`password`,\n".
            "employees.email,\n".
            "employees.photo,\n".
            "users.custid,\n".
            "users.active,\n".
            "users.auditrak,\n".
            "users.role,\n".
            "users.userid\n".
            "FROM\n".
            "users\n".
            "LEFT JOIN employees ON employees.id = users.userid\n".
            "WHERE\n".
            "users.auditrak = 1 AND\n".
            "users.active = 1 AND\n".
            "users.custid = $custid";

    $users = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($users) {
            echo  '{"users": '. json_encode($users). '}';
            $users = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});




// Grant user access to AuditTRAK

$app->post('/api/user/grant/{userid}/{custid}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');

    $auditrak      = $request->getParam('auditrak');
    $active = $request->getParam('active');
    $role = $request->getParam('role');

    $trk = new clstrak();


    if ($trk->isUserExist($custid,$userid)) {
       // User exist, check if auditrak is = 1

        if ($trk->isUserGranted($custid, $userid)) {

            echo '{"Warning: {"Message": "User is already granted access to auditTRAK"}';
        } else {

         // Need to set auditrak value to 1

            $sql = "UPDATE users  SET active = 1 WHERE custid = $custid AND userid = '$userid' AND auditrak = 1";
            try{
                // Get DB object
                $db= new db();
                $db = $db->connect();
                $stmt = $db->prepare($sql);

                $stmt->bindParam(':custid', $custid);
                $stmt->bindParam(':userid', $userid);


                $stmt->execute();
                $db = null;
                echo '{"notice": {"Message": "User Granted"}';

            }catch(PDOException $e){
                echo '{"error": {"Message": '.$e->getMessage().'}';

            }

        }


    }else {
        $sql = "INSERT INTO users (custid,active,auditrak,role,userid,level) VALUES (:custid,:active, :auditrak, :role, :userid, :level)";
        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $role = 0;
            $active = 1;
            $auditrak = 1;
            $level = 1;

            $stmt->bindParam(':custid', $custid);
            $stmt->bindParam(':active', $active);
            $stmt->bindParam(':auditrak', $auditrak);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':userid', $userid);
            $stmt->bindParam(':level', $level);


            $stmt->execute();
            $db = null;
            echo '{"notice": {"Message": "User Granted"}';

        }catch(PDOException $e){
            echo '{"error": {"Message": '.$e->getMessage().'}';

        }


    }

});


// Revoke user access to AuditTRAK

$app->post('/api/user/revoke/{userid}/{custid}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');

    $auditrak      = $request->getParam('auditrak');
    $active = $request->getParam('active');
    $role = $request->getParam('role');

    $trk = new clstrak();

    if (!$trk->isUserExist($custid,$userid)) {
        echo '{"Warning: {"Message": "User does not exist"}';

    } else {

        if ($trk->isUserGranted($custid, $userid)) {

            // Do an update here
            $sql = "UPDATE users  SET active = 0 WHERE custid = $custid AND userid = '$userid' AND auditrak = 1";
            try{
                // Get DB object
                $db= new db();
                $db = $db->connect();
                $stmt = $db->prepare($sql);

                $stmt->bindParam(':custid', $custid);
                $stmt->bindParam(':userid', $userid);


                $stmt->execute();
                $db = null;
                echo '{"notice": {"Message": "User access revoked"}';

            }catch(PDOException $e){
                echo '{"error": {"Message": '.$e->getMessage().'}';

            }


        }else {
            echo '{"Warning: {"Message": "User access already revoked!"}';

        }
    }



});




