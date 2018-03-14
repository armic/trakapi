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
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

// Login
$app->post('/api/user/login', function(Request $request, Response $response) {

    $username = $request->getParam('username');
    $password = $request->getParam('password');
    // Select statement\

    $sql = "SELECT\n" .
            "users.active,\n" .
            "users.auditrak,\n" .
            "users.role,\n" .
            "users.`level`,\n" .
            "users.custid,\n" .
            "users.userid,\n" .
            "employees.custid,\n" .
            "employees.firstname,\n" .
            "employees.lastname,\n" .
            "employees.username,\n" .
            "employees.password,\n" .
            "employees.email,\n" .
            "employees.photo,\n" .
            "employees.mobilenumber,\n" .
            "customers.`name`,\n" .
            "customers.contactperson,\n" .
            "customers.email,\n" .
            "customers.address\n" .
            "FROM\n" .
            "users\n" .
            "LEFT JOIN employees ON employees.id = users.userid\n" .
            "LEFT JOIN customers ON customers.id = employees.custid\n" .
            "WHERE\n" .
            "users.auditrak = 1 AND\n" .
            "users.active = 1 AND\n" .
            "employees.password = '" . md5($password) . "'";


    // Check username
    if (strpos($username, '@')) {
        $sql = $sql . " AND employees.email = '$username'";
    } else {
        $sql = $sql . " AND employees.username = '$username'";
    }


    $user = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if ($user) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($user) . ', "code": 200 }';
            $user = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "User doesn\'t exist" , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"' . $e->getMessage() . '": "abc" , "code": 202 }';
    }
});

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
    $hash = md5(rand(0, 1000)); // Generate random 32 character hash and assign it to a local variable.


    if (isset($custid) && !empty($custid) AND isset($firstname) && !empty($firstname) AND isset($lastname) && !empty($lastname) AND isset($username) && !empty($username) AND isset($password) && !empty($password) AND isset($email) && !empty($email)) {

        // Form Submited
        if (preg_match("/^[^@]+@[^@]+\.[a-z]{2,6}$/i", $email)) {
            //
            // Check for duplicate email or username
            // isUsernameExist($username)
            // isEmployeeEmailExist($email)
            $trk = new clstrak();

            // Check username

            if ($trk->isUsernameExist($username)) {
                echo '{"success": false,"error_message": "username is in use" , "code": 202 }';
                exit(0);
            }

            // Check email
            if ($trk->isEmployeeEmailExist($email)) {
                echo '{"success": false,"error_message": "email is in use" , "code": 202 }';
                exit(0);
            }

            // Passed all

            $sql = "INSERT INTO employees (firstname, lastname, email, custid,createddate,username,password, verified, hash) VALUES
            (:firstname, :lastname,:email, :custid, :createddate, :username, :password, :verified, :hash)";
            try {
                // Get DB object
                $db = new db();
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

                $to = $email; // Send email to our user
                $subject = "Henchmantrak Signup | Verification"; // Give the email a subject
                $message = "Thanks for signing up! Your account has been created \n" .
                        "with the credentials below. Please follow the url below to  \n" .
                        "activated your account.\n \n \n" .
                        "------------------------\n" .
                        "Username: " . $username . "\n" .
                        "Username: " . $password . "\n" .
                        "------------------------\n \n \n" .
                        "Please click this link to activate your account: \n" .
                        "<a href='http://52.27.53.102/Henchman_webapp/admin/Admin/registerVerify/".$email."/".$hash."'>http://localhost/verify.php?email='" . $email . "'&hash='" . $hash."</a>";



                $headers = 'From:support@henchmantrak.com' . "\r\n"; // Set from headers
                mail($to, $subject, $message, $headers); // Send our email


                echo '{"success": true,"error_message": null, "result":"Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below.", "code": 200 }';
            } catch (PDOException $e) {
                echo '{"success": false,"error_message": "Registration failed. Please try again" , "code": 202 }';
                //echo '{"error": {"text": '.$e->getMessage().'}';
            }
        } else {
            echo '{"success": false,"error_message": "Invalid email Address" , "code": 202 }';
        }
    } else {
        echo '{"success": false,"error_message": "All fields are required!" , "code": 202 }';
    }
});

//Register verify
$app->post('/api/user/registerVerify/{email}/{hash}', function(Request $request, Response $response) {
    $email = $request->getAttribute('email');
    $hash = $request->getAttribute('hash');

    if (isset($email) && !empty($email) AND isset($hash) && !empty($hash)) {
        $email = mysqli_escape_string($email);
        $hash = mysqli_escape_string($hash);

        $sql = "SELECT email, hash, verified "
                . "FROM employees "
                . "WHERE email = '" . $email . "' "
                . "AND hash='" . $hash . "' "
                . "AND verified=0";
        $users = NULL;
        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            $match = $stmt->rowCount();

            if ($match > 0) {
                $sql = "UPDATE employees "
                        . "SET verified= 1 "
                        . "WHERE email='" . $email . "' "
                        . "AND hash= '" . $hash . "' "
                        . "AND verified= 0";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $db = null;
                echo '{"success": true,"error_message": null, "result":"Your account has been activated, you can login after you are GRANTED access to AuditTRAK.", "code": 200 }';
            } else {
                echo '{"success": false,"error_message": "The url is either invalid or you already have activated your account." , "code": 202 }';
            }
        } catch (PDOException $e) {
            echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
        }
    } else {
        echo '{"success": false,"error_message": "Invalid approach, please use the link that has been send to your email." , "code": 202 }';
    }
});

// Get specific user
$app->get('/api/user/details/{empid}', function(Request $request, Response $response) {

    $empid = $request->getAttribute('empid');
    // Select statement

    $sql = "SELECT DISTINCT\n" .
            "employees.id as empid,\n" .
            "employees.firstname,\n" .
            "employees.lastname,\n" .
            "employees.username,\n" .
            "employees.`password`,\n" .
            "employees.email,\n" .
            "employees.photo,\n" .
            "users.id as userid,\n" .
            "users.custid,\n" .
            "users.active,\n" .
            "users.auditrak,\n" .
            "users.role,\n" .
            "users.userid,\n" .
            "users.level\n" .
            "FROM\n" .
            "users\n" .
            "LEFT JOIN employees ON employees.id = users.userid\n" .
            "WHERE\n" .
            "employees.id = $empid AND\n" .
            "users.auditrak =1";


    $user = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($user) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($user) . ', "code": 200 }';
            $user = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "User not found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});
// Reset password request 

$app->get('/api/users/passwordupdaterequest/{email}', function(Request $request, Response $response) {

    $email = $request->getAttribute('email');
    // Select statement


    $sql = "SELECT *\n" .
            "FROM\n" .
            "employees\n" .
            "WHERE\n";
    // Check username
    if (strpos($email, '@')) {
        $sql = $sql . "  employees.email = '$email'";
    } else {
        $sql = $sql . " employees.username = '$email'";
    }

    $users = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $users = $stmt->fetch(PDO::FETCH_OBJ);

        if ($users) {
            $to = $users->email;
            $subject = "Henchmann Track password request for" . $users->firstname . " " . $users->lastname;
            $message = "Please, click on the link below for your password reset: <br/>";
            $message .= "<a href='52.27.53.102/Henchman/public/api/users/passwordupdateresetting/" . $users->custid . "'>Reset password</a><br/>";
            $message .= "You can alternatively copy-paste this url if the link is not working";

            mail($to, $subject, $message);

            echo '{"success": true,"error_message": null, "result":"Please click on the link on the mail you received at ' . $users->email . ' mail", "code": 200 }';

            $users = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


// Reset password reset 

$app->put('/api/users/passwordupdateresetting/{empid}', function(Request $request, Response $response) {

    $password = $request->getParam('password');
    $empid = $request->getAttribute('empid');
    // Select statement


    $sql = "UPDATE employees "
            . "SET employees.password = '" . md5($password) . "' "
            . "WHERE id =" . $empid;




    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);


        $stmt->execute();


        echo '{"success": true,"error_message": null, "result":"Password updated", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Get user list

$app->get('/api/users/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT DISTINCT\n" .
            "employees.id,\n" .
            "employees.firstname,\n" .
            "employees.lastname,\n" .
            "employees.username,\n" .
            "employees.`password`,\n" .
            "employees.email,\n" .
            "employees.photo,\n" .
            "users.custid,\n" .
            "users.active,\n" .
            "users.auditrak,\n" .
            "users.role,\n" .
            "users.userid\n" .
            "FROM\n" .
            "users\n" .
            "LEFT JOIN employees ON employees.id = users.userid\n" .
            "WHERE\n" .
            "users.auditrak = 1 AND\n" .
            "users.active = 1 AND\n" .
            "users.custid = $custid";

    $users = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($users) {


            echo '{"success": true,"error_message": null, "result":' . json_encode($users) . ', "code": 200 }';
            $users = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});





// Grant user access to AuditTRAK

$app->post('/api/user/grant/{userid}/{custid}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');

    $auditrak = $request->getParam('auditrak');
    $active = $request->getParam('active');
    $role = $request->getParam('role');

    $trk = new clstrak();


    if ($trk->isUserExist($custid, $userid)) {
        // User exist, check if auditrak is = 1

        if ($trk->isUserGranted($custid, $userid)) {





            echo '{"success": false,"error_message": "User is already granted access to auditTRAK" , "code": 202 }';
        } else {

            // Need to set auditrak value to 1

            $sql = "UPDATE users  SET active = 1 WHERE custid = $custid AND userid = '$userid' AND auditrak = 1";
            try {
                // Get DB object
                $db = new db();
                $db = $db->connect();
                $stmt = $db->prepare($sql);

                $stmt->bindParam(':custid', $custid);
                $stmt->bindParam(':userid', $userid);


                $stmt->execute();
                $db = null;

                echo '{"success": true,"error_message": null, "result":"User Granted.", "code": 200 }';
            } catch (PDOException $e) {

                echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
            }
        }
    } else {
        $sql = "INSERT INTO users (custid,active,auditrak,role,userid,level) VALUES (:custid,:active, :auditrak, :role, :userid, :level)";
        try {
            // Get DB object
            $db = new db();
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
            echo '{"success": true,"error_message": null, "result":"User Granted.", "code": 200 }';
        } catch (PDOException $e) {
            echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
        }
    }
});


// Revoke user access to AuditTRAK

$app->post('/api/user/revoke/{userid}/{custid}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');

    $auditrak = $request->getParam('auditrak');
    $active = $request->getParam('active');
    $role = $request->getParam('role');

    $trk = new clstrak();

    if (!$trk->isUserExist($custid, $userid)) {


        echo '{"success": false,"error_message": "User does not exist" , "code": 202 }';
    } else {

        if ($trk->isUserGranted($custid, $userid)) {

            // Do an update here
            $sql = "UPDATE users  SET active = 0 WHERE custid = $custid AND userid = '$userid' AND auditrak = 1";
            try {
                // Get DB object
                $db = new db();
                $db = $db->connect();
                $stmt = $db->prepare($sql);

                $stmt->bindParam(':custid', $custid);
                $stmt->bindParam(':userid', $userid);


                $stmt->execute();
                $db = null;

                echo '{"success": true,"error_message": null, "result":"User access revoked.", "code": 200 }';
            } catch (PDOException $e) {
                echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
            }
        } else {

            echo '{"success": false,"error_message": "User access already revoked!" , "code": 202 }';
        }
    }
});




