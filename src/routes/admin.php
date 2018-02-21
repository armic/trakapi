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


/**
 * User  Paths
 */


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

// Update user record

$app->put('/api/admin/user/update/{userid}/{custid}', function(Request $request, Response $response) {

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

/**
 * User  Paths
 */


/**
 * reservation  Paths
 */

// Reservation List


$app->get('/api/admin/reservation/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement


    $sql =  "SELECT\n".
            "reservations.custid,\n".
            "reservations.reservationdate,\n".
            "reservations.reservationtime,\n".
            "reservations.userid,\n".
            "reservations.kitid,\n".
            "reservations.toolid,\n".
            "reservations.flag,\n".
            "customers.`name`,\n".
            "employees.firstname,\n".
            "employees.lastname,\n".
            "kits.description AS kitname,\n".
            "lockers.description AS lockername,\n".
            "tools.stockcode,\n".
            "tools.descriptionvAS toolname\n".
            "FROM\n".
            "reservations\n".
            "LEFT JOIN customers ON customers.id = reservations.custid\n".
            "LEFT JOIN employees ON employees.id = reservations.userid\n".
            "LEFT JOIN kits ON kits.id = reservations.kitid\n".
            "LEFT JOIN lockers ON lockers.id = kits.lockerid\n".
            "LEFT JOIN kittools ON kittools.id = reservations.toolid\n".
            "LEFT JOIN tools ON tools.id = kittools.toolid\n".
            "WHERE\n".
            "reservations.custid = $custid\n".
            "ORDER BY\n".
            "reservations.reservationdate DESC,\n".
            "reservations.reservationtime DESC";

    $reservation = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $reservation = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($reservation) {
            echo  '{"reservations": '. json_encode($reservation). '}';
            $reservation = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});


// Log List


$app->get('/api/admin/log/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT * FROM log WHERE custid = $custid ORDER BY logdate DESC";



    $logs = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($logs) {
            echo  '{"logs": '. json_encode($logs). '}';
            $logs = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});


// Lockers

// View locker list

$app->get('/api/admin/locker/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT\n".
            "lockers.id,\n".
            "lockers.custid,\n".
            "lockers.description AS lockername,\n".
            "lockers.`code`,\n".
            "lockers.locationid,\n".
            "locations.description AS locationname\n".
            "FROM\n".
            "lockers\n".
            "LEFT JOIN locations ON locations.id = lockers.locationid\n".
            "WHERE\n".
            "lockers.custid = $custid";



    $lockers = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($lockers) {
            echo  '{"lockers": '. json_encode($lockers). '}';
            $logs = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Update locker

$app->put('/api/admin/locker/update/{custid}/{lockerid}', function(Request $request, Response $response) {

    $lockerid = $request->getAttribute('lockerid');
    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');
    $code = $request->getParam('code');
    $locationid = $request->getParam('locationid');



    $sql = "UPDATE lockers SET
                   description = :description,
                   code = :code,
                   locationid = :locationid
             WHERE id = $lockerid AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        // $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':locationid', $locationid);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "locker updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// Add locker

$app->post('/api/admin/locker/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $description = $request-> getParam('description');
    $code = $request->getParam('code');
    $locationid = $request->getParam('locationid');
    $active = $request->getParam('active');

    $active = 1;



        $sql = "INSERT INTO lockers (custid, description, code,locationid, active) VALUES 
            (:custid, :description,:code, :locationid, :active)";
        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->bindParam(':custid', $custid);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':locationid', $locationid);
            $stmt->bindParam(':locationid', $active);


            $stmt->execute();
            $db = null;
            echo '{"notice": {"text": "locker Added"}';

        }catch(PDOException $e){
            echo '{"error": {"text": '.$e->getMessage().'}';

        }




});

// Disable specific  locker

$app->put('/api/admin/locker/disable/{custid}/{lockerid}', function(Request $request, Response $response) {

    $lockerid = $request->getAttribute('lockerid');
    $custid = $request->getAttribute('custid');



    $sql = "UPDATE lockers SET
                   active = 0
             WHERE id = $lockerid AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);



        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "locker disabled"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// enable specific  locker

$app->put('/api/admin/locker/enable/{custid}/{lockerid}', function(Request $request, Response $response) {

    $lockerid = $request->getAttribute('lockerid');
    $custid = $request->getAttribute('custid');



    $sql = "UPDATE lockers SET
                   active = 1
             WHERE id = $lockerid AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);



        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "locker enabled"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});


//KITS

// View kit List

$app->get('/api/admin/kits/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT\n".
            "kits.id,\n".
            "kits.lockerid,\n".
            "kits.description,\n".
            "kits.custid,\n".
            "kits.reserved,\n".
            "kits.qrcode,\n".
            "kits.kitlocation,\n".
            "kits.`status`,\n".
            "lockers.description AS lockername,\n".
            "locations.description AS locationname\n".
            "FROM\n".
            "kits\n".
            "LEFT JOIN lockers ON lockers.id = kits.lockerid\n".
            "LEFT JOIN locations ON locations.id = lockers.locationid\n".
            "WHERE\n".
            "kits.custid = $custid";



    $kits = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($kits) {
            echo  '{"lockers": '. json_encode($kits). '}';
            $kits = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

//Update kit

$app->put('/api/admin/kit/update/{custid}/{kitid}', function(Request $request, Response $response) {

    $kitid = $request->getAttribute('kitid');
    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');
    $qrcode = $request->getParam('qrcode');
    $lockerid = $request->getParam('lockerid');



    $sql = "UPDATE kits SET
                   description = :description,
                   qrcode = :qrcode,
                   lockerid = :lockerid
             WHERE id = $kitid AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        // $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':qrcode', $qrcode);
        $stmt->bindParam(':lockerid', $lockerid);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Kit updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// Add KIT

$app->post('/api/admin/kit/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $description = $request-> getParam('description');
    $qrcode = $request->getParam('qrcode');
    $lockerid = $request->getParam('lockerid');




    $sql = "INSERT INTO kits (custid, description, lockerid,qrcode, reserved,status) VALUES 
            (:custid, :description,:lockerid, :qrcode, 0, 0)";
    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':qrcode', $qrcode);
        $stmt->bindParam(':lockerid', $lockerid);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Kit Added"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }




});

//Kit Tools
// View kit Tool List

$app->get('/api/admin/kittools/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT\n".
        "kittools.id,\n".
        "kittools.kitid,\n".
        "kittools.toolid,\n".
        "kittools.custid,\n".
        "kittools.reserved,\n".
        "kittools.qrcode,\n".
        "kittools.`status`,\n".
        "tools.stockcode,\n".
        "tools.description AS toolname,\n".
        "tools.serialno,\n".
        "tools.categoryid,\n".
        "toolcategories.description AS categoryname,\n".
        "kits.description AS kitname,\n".
        "lockers.description AS lockername,\n".
        "kits.lockerid\n".
        "FROM\n".
        "kittools\n".
        "LEFT JOIN tools ON tools.id = kittools.toolid\n".
        "LEFT JOIN toolcategories ON toolcategories.id = tools.categoryid\n".
        "LEFT JOIN kits ON kits.id = kittools.kitid\n".
        "LEFT JOIN lockers ON lockers.id = kits.lockerid\n".
        "WHERE\n".
        "kittools.custid = $custid";



    $kittools = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($kittools) {
            echo  '{"kit tools": '. json_encode($kittools). '}';
            $kits = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

//Update kit tool

$app->put('/api/admin/kittool/update/{custid}/{toolkitid}', function(Request $request, Response $response) {

    $toolkitid = $request->getAttribute('toolkitid');
    $custid = $request->getAttribute('custid');

    $kitid = $request->getParam('kitid');
    $toolid = $request->getParam('toolid');
    $qrcode = $request->getParam('qrcode');



    $sql = "UPDATE kittools SET
                   kitid = :kitid,
                   toolid = :toolid,
                   qrcode = :qrcode
             WHERE id = $toolkitid AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':kitid', $kitid);
        $stmt->bindParam(':toolid', $toolid);
        $stmt->bindParam(':qrcode', $qrcode);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Kit tool updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// Add KIT Tool

$app->post('/api/admin/kittool/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $kitid = $request-> getParam('kitid');
    $toolid = $request->getParam('toolid');
    $qrcode = $request->getParam('qrcode');




    $sql = "INSERT INTO kittools (kitid, toolid,custid,reserved,qrcode,status) VALUES 
            (:kitid, :toolid,:custid, 0, :qrcode, 0)";
    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':kitid', $kitid);
        $stmt->bindParam(':toolid', $toolid);
        $stmt->bindParam(':qrcode', $qrcode);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Kit Tool Added"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }




});

//TOOLS

// View Tool List

$app->get('/api/admin/tools/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT\n".
            "tools.id,\n".
            "tools.custid,\n".
            "tools.stockcode,\n".
            "tools.description AS toolname,\n".
            "tools.serialno,\n".
            "tools.categoryid,\n".
            "tools.toolimage,\n".
            "toolcategories.description AS categoryname\n".
            "FROM\n".
            "tools\n".
            "LEFT JOIN toolcategories ON toolcategories.id = tools.categoryid\n".
            "WHERE\n".
            "tools.custid = $custid";



    $tools = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($tools) {
            echo  '{"tools": '. json_encode($tools). '}';
            $kits = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

//Update tool

$app->put('/api/admin/tool/update/{custid}/{toolid}', function(Request $request, Response $response) {

    $toolkitid = $request->getAttribute('toolid');
    $custid = $request->getAttribute('custid');

    $stockcode = $request->getParam('stockcode');
    $description = $request->getParam('description');
    $categoryid = $request->getParam('categoryid');
    $toolimage = $request->getParam('toolimage');
    $serialno =  $request->getParam('serialno');




    $sql = "UPDATE tools SET
                   stockcode = :stockcode,
                   description = :description,
                   serialno = :serialno,
                   categoryid = :categoryid,
                   toolimage = :toolimage,
             WHERE id = $toolid AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':stockcode', $stockcode);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':serialno', $serialno);
        $stmt->bindParam(':categoryid', $categoryid);
        $stmt->bindParam(':toolimage', $toolimage);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "tool updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// Add Tool

$app->post('/api/admin/tool/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $stockcode = $request->getParam('stockcode');
    $description = $request->getParam('description');
    $categoryid = $request->getParam('categoryid');
    $toolimage = $request->getParam('toolimage');
    $serialno =  $request->getParam('serialno');



    $sql = "INSERT INTO tools (custid,stockcode,description,serialno,categoryid,toolimage) VALUES 
            (:custid,:stockcode,:description,:serialno,:categoryid,:toolimage)";
    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':stockcode', $stockcode);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':serialno', $serialno);
        $stmt->bindParam(':categoryid', $categoryid);
        $stmt->bindParam(':toolimage', $toolimage);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Tool Added"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }




});

// Tool Category

// View Tool Category List

$app->get('/api/admin/toolcategory/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT * FROM toolcategories WHERE custid = $custid";


    $categories = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($categories) {
            echo  '{"tool categories": '. json_encode($categories). '}';
            $categories = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

//Update tool categoy

$app->put('/api/admin/toolcategory/update/{custid}/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');



    $sql = "UPDATE toolcategories SET
                   description = :description
             WHERE id = $id AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "tool category updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// Add Tool category

$app->post('/api/admin/toolcategory/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');



    $sql = "INSERT INTO toolcategories (custid,description) VALUES 
            (:custid,:description)";
    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':description', $description);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Tool Category  Added"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }




});

//LOCATIONS

// View location List

$app->get('/api/admin/locations/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT * FROM locations WHERE custid = $custid";


    $locations = null;

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($locations) {
            echo  '{"locations": '. json_encode($locations). '}';
            $locations = null;
            $db = null;
        } else {
            echo '{"error":"No records found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

//Update Location

$app->put('/api/admin/location/update/{custid}/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');



    $sql = "UPDATE locations SET
                   description = :description
             WHERE id = $id AND custid = $custid";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "location updated"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }


});

// Add location

$app->post('/api/admin/location/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');



    $sql = "INSERT INTO locations (custid,description) VALUES 
            (:custid,:description)";
    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':description', $description);


        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "location  Added"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }




});

























