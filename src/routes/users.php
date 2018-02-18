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
$app->get('/api/user/login', function(Request $request, Response $response) {

    $username = $request->getParam('username');
    $password = $request->getParam('password');
    // Select statement

    $sql =  "SELECT DISTINCT\n".
            "employees.id,\n".
            "employees.firstname,\n".
            "employees.lastname,\n".
            "employees.username,\n".
            "employees.password,\n".
            "employees.email,\n".
            "employees.photo,\n".
            "users.custid,\n".
            "users.active,\n".
            "users.auditrak,\n".
            "users.role,\n".
            "users.level,\n".
            "users.userid\n".
            "FROM\n".
            "users\n".
            "LEFT JOIN employees ON employees.id = users.userid\n".
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

      echo $sql;

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


// Get specific user


$app->get('/api/user/{userid}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    // Select statement

    $sql = "SELECT DISTINCT\n".
        "employees.id,\n".
        "employees.firstname,\n".
        "employees.lastname,\n".
        "employees.username,\n".
        "employees.`password`,\n".
        "employees.email,\n".
        "employees.photo,\n".
        "users.id,\n".
        "users.custid,\n".
        "users.active,\n".
        "users.auditrak,\n".
        "users.role,\n".
        "users.userid\n".
        "FROM\n".
        "users\n".
        "LEFT JOIN employees ON employees.id = users.userid\n".
        "WHERE\n".
        "users.userid = $userid AND\n".
        "users.auditrak =1";

      echo $sql;

    $user = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($user) {
            echo  '{"user": '. json_encode($user). '}';
            $users = null;
            $db = null;
        } else {
            echo '{"error":"User not found.")';
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
// Update user record

$app->put('/api/user/update/{userid}/{custid}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');

    $role = $request->getParam('role');
    $level = $request->getParam('level');



    $sql = "UPDATE users SET
                   role = :role,
                   level = :level
             WHERE userid = $userid AND custid = $custid AND auditrak= 1";
   echo $sql;
    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        // $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':level', $level);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "User updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});




