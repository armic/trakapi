<?php
/**
 * Created by PhpStorm.
 * User: artolentino
 * Date: 2/2/18
 * Time: 10:27 AM
 */


use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;


function isUserExist($custid,$userid) {

    $sql = "SELECT\n".
            "users.id,\n".
            "users.custid,\n".
            "users.active,\n".
            "users.kabtrak,\n".
            "users.portatrak,\n".
            "users.cribtrak,\n".
            "users.auditrak,\n".
            "users.role,\n".
            "users.userid\n".
            "FROM\n".
            "users\n".
            "WHERE\n".
            "users.auditrak = 1 AND\n".
            "users.custid = $custid AND\n".
            "users.userid = '$userid'";

    $user = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmtuser = $db->query($sql);
        $user = $stmtuser->fetchAll(PDO::FETCH_OBJ);

        if($user) {

            return true;

        } else {
            return false;
        }


    }catch(PDOException $e){
        return false;

    }
}

function isUserGranted($custid,$userid) {

    $sql = "SELECT\n".
        "users.id,\n".
        "users.custid,\n".
        "users.active,\n".
        "users.kabtrak,\n".
        "users.portatrak,\n".
        "users.cribtrak,\n".
        "users.auditrak,\n".
        "users.role,\n".
        "users.userid\n".
        "FROM\n".
        "users\n".
        "WHERE\n".
        "users.auditrak = 1 AND\n".
        "users.custid = $custid AND\n".
        "users.userid = '$userid'";

    $user = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();

        foreach($db->query($sql, PDO::FETCH_ASSOC) as $row){

            if($row['active'] == 0 or $row['active'] == null) {


               return false;
            } else {

                return true;
            }

        }


    }catch(PDOException $e){
        return false;

    }
}


// Login
$app->get('/api/user/login/{username}/{password}', function(Request $request, Response $response) {

    $username = $request->getAttribute('username');
    $password = $request->getAttribute('password');
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

    $user = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);

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


// Get specific user


$app->get('/api/user/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    // Select statement

    $sql = "SELECT DISTINCT\n".
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
        "users.id = $id";

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

// Grant user access to system. This assumes that the user did not exist

$app->post('/api/user/grant/{userid}/{custid}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');

    $auditrak      = $request->getParam('auditrak');
    $active = $request->getParam('active');
    $role = $request->getParam('role');

    if (isUserExist($custid,$userid)) {
       // User exist, check if auditrak is = 1
        if (isUserGranted($custid, $userid)) {

            echo '{"Warning: {"Message": "User is already granted access to auditTRAK"}';
        } else {

         // Need to set auditrak value to 1

            $sql = "UPDATE users  SET active = 1 WHERE auditrak =1 AND custid = $custid AND userid = '$userid'";
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
        $sql = "INSERT INTO users (custid,active,auditrak,role,userid) VALUES (:custid,1, :auditrak, :role, :userid)";
        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->bindParam(':custid', $custid);
            $stmt->bindParam(':active', $active);
            $stmt->bindParam(':auditrak', $auditrak);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':userid', $userid);


            $stmt->execute();
            $db = null;
            echo '{"notice": {"Message": "User Granted"}';

        }catch(PDOException $e){
            echo '{"error": {"Message": '.$e->getMessage().'}';

        }


    }




});

?>