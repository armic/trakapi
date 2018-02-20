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
 * Date: 2/20/18
 * Time: 8:13 AM
 */

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

/**
 * Employee Paths
 */

// Get employee list

$app->get('/api/admin/employees/{custid}', function(Request $request, Response $response) {

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
        "employees.custid\n".
        "FROM employees\n".
        "WHERE custid = $custid";

    $employees = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($employees) {
            echo  '{"Employees": '. json_encode($employees). '}';
            $employees = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Get specific employee

$app->get('/api/admin/employee/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    // Select statement

    $sql = "SELECT DISTINCT\n".
        "employees.id,\n".
        "employees.firstname,\n".
        "employees.lastname,\n".
        "employees.username,\n".
        "employees.`password`,\n".
        "employees.email,\n".
        "employees.photo,\n".
        "employees.custid\n".
        "FROM employees\n".
        "WHERE id = $id";

    $employees = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($employees) {
            echo  '{"Employee": '. json_encode($employees). '}';
            $employees = null;
            $db = null;
        } else {
            echo '{"error":"Employee not found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Count employees

$app->get('/api/admin/employees/count/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    $employees = null;
    // Select statement
    $sql = "SELECT DISTINCT* FROM employees WHERE  custId = $id
";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();
        echo  '{"employee_count ": '.  json_encode($numrows) .'}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }
});

// Update employees using customer id (id)

$app->put('/api/admin/employee/update/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');

    $firstname = $request->getParam('firstname');
    $lastname = $request->getParam('lastname');
    $email = $request->getParam('email');
    $mobilenumber = $request->getParam('mobilenumber');
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    $photo = $request->getParam('photo');


    $sql = "UPDATE employees SET
                   firstname = :firstname,
                   lastName = :lastname,
                   email = :email,
                   mobileNumber = :mobileNumber,
                   username = :username,
                   password = :password
             WHERE id = $id";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mobilenumber', $mobilenumber);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Employee updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// Add new employee

$app->post('/api/admin/employee/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $firstname = $request-> getParam('firstname');
    $lastname = $request->getParam('lastname');
    $email = $request->getParam('email');
    $mobilenumber = $request->getParam('mobilenumber');
    $createddate = date("Y-m-d h:i:sa");
    $username = $request->getParam('username');
    $password = md5($request->getParam('password'));

    $trk = new clstrak();

    if($trk->isEmployeeEmailExist($email) or $trk->isUsernameExist($username))
    {
        if($trk->isEmployeeEmailExist($email)) {
            echo '{"warning": {"Message": "Email already exist"}';
        };

        if($trk->isUsernameExist($username)) {
            echo '{"warning": {"Message": "Username already exist"}';
        };

        if($trk->isEmployeeEmailExist($email) or $trk->isUsernameExist($username)) {
            echo '{"warning": {"Message": "Username/Email  already exist"}';
        };

    }else {

        $sql = "INSERT INTO employees (firstname, lastname, email,mobilenumber, custid,createddate,username,password) VALUES 
            (:firstname, :lastname,:email, :mobilenumber,  :custid, :createddate, :username, :password)";
        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mobilenumber', $mobilenumber);
            $stmt->bindParam(':createddate', $createddate);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':custid', $custid);


            $stmt->execute();
            $db = null;
            echo '{"notice": {"text": "Employee Added"}';

        }catch(PDOException $e){
            echo '{"error": {"text": '.$e->getMessage().'}';

        }




    }


});

/**
 * End Employee Paths
 */

/**
 * Tail  Paths
 */

// Get tail list

$app->get('/api/admin/tails/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT DISTINCT\n".
        "tails.id,\n".
        "tails.number,\n".
        "tails.description\n".
        "FROM tails\n".
        "WHERE custid = $custid";

    $tails = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $tails = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($tails) {
            echo  '{"Tails": '. json_encode($tails). '}';
            $tails = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});


// Get specific tail

$app->get('/api/admin/tail/view/{custid}/{number}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $number = $request->getAttribute('number');
    // Select statement

    $sql = "SELECT DISTINCT\n".
        "tails.id,\n".
        "tails.number,\n".
        "tails.description\n".
        "FROM tails\n".
        "WHERE custid = $custid AND\n".
        "number = $number";
    $tails = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $tails = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($tails) {
            echo  '{"Tails": '. json_encode($tails). '}';
            $tails = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});


// Add new tail

$app->post('/api/admin/tail/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $number = $request-> getParam('number');
    $description = $request->getParam('description');
    $createddate = date("Y-m-d h:i:sa");

    $trk = new clstrak();

    if($trk->isTailNumberExist($number,$custid))
    {
        echo '{"warning": {"Message": "Tail already exist"}';

    }else {

        $sql = "INSERT INTO tails (custid, number, description,createddate) VALUES 
            (:custid, :number,:description, :createddate)";
        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->bindParam(':custid', $custid);
            $stmt->bindParam(':number', $number);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':createddate', $createddate);


            $stmt->execute();
            $db = null;
            echo '{"notice": {"text": "Tail Added"}';

        }catch(PDOException $e){
            echo '{"error": {"text": '.$e->getMessage().'}';

        }

    }


});


//Update tail

$app->put('/api/admin/tail/update/{number}/{custid}', function(Request $request, Response $response) {

    $number = $request->getAttribute('number');
    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');


    $sql = "UPDATE tails SET
                   description = :description
                  
             WHERE number = '$number' AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);
        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Tail updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});


// Deactivate specific Tail

$app->put('/api/admin/tail/disable/{number}/{custid}', function(Request $request, Response $response) {

    $number = $request->getAttribute('number');
    $custid = $request->getAttribute('custid');



    $sql = "UPDATE tails SET
                    active = 0
             WHERE number = '$number' AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);
        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Tail disabled"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// Activate specific Tail

$app->put('/api/admin/tail/enable/{number}/{custid}', function(Request $request, Response $response) {

    $number = $request->getAttribute('number');
    $custid = $request->getAttribute('custid');



    $sql = "UPDATE tails SET
                    active = 1
             WHERE number = '$number' AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);
        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Tail enabled"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});


/**
 * End Tail  Paths
 */


//users


// Get user list


$app->get('/api/admin/users/{custid}', function(Request $request, Response $response) {

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


$app->get('/api/admin/user/{userid}', function(Request $request, Response $response) {

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
