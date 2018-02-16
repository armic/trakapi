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
 * Date: 2/12/18
 * Time: 9:39 AM
 */

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

// Get employee list

$app->get('/api/employees/{custid}', function(Request $request, Response $response) {

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

$app->get('/api/employee/{id}', function(Request $request, Response $response) {

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

$app->get('/api/employees/count/{id}', function(Request $request, Response $response) {

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

$app->put('/api/employee/update/{id}', function(Request $request, Response $response) {

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

$app->post('/api/employee/add/{custid}', function(Request $request, Response $response) {

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






?>