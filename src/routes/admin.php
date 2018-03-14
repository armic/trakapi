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
 * Customer Paths
 */
// Login
$app->post('/api/admin/login', function(Request $request, Response $response) {

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
            "users.role = 1 AND\n" .
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



// Get customer list

$app->get('/api/admin/customer/', function(Request $request, Response $response) {


    // Select statement

    $sql = "SELECT DISTINCT\n" .
            "id,\n" .
            "name,\n" .
            "contactperson,\n" .
            "email,\n" .
            "datecreated,\n" .
            "address \n" .
            "FROM customers";

    $customers = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $customers = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($customers) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($customers) . ', "code": 200 }';

            $customers = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


/**
 * Employee Paths
 */
// Get employee list

$app->get('/api/admin/employees/{custid}', function(Request $request, Response $response) {

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
            "employees.custid\n" .
            "FROM employees\n" .
            "WHERE custid = $custid";

    $employees = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($employees) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($employees) . ', "code": 200 }';

            $employees = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Get specific employee

$app->get('/api/admin/employee/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    // Select statement

    $sql = "SELECT DISTINCT\n" .
            "employees.id,\n" .
            "employees.firstname,\n" .
            "employees.lastname,\n" .
            "employees.username,\n" .
            "employees.mobilenumber,\n" .
            "employees.`password`,\n" .
            "employees.email,\n" .
            "employees.photo,\n" .
            "employees.custid\n" .
            "FROM employees\n" .
            "WHERE id = $id";

    $employees = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($employees) {

            echo '{"success": true,"error_message": null, "result":' . json_encode($employees) . ', "code": 200 }';

            $employees = null;
            $db = null;
        } else {



            echo '{"success": false,"error_message": "Employee not found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Count employees

$app->get('/api/admin/employees/count/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    $employees = null;
    // Select statement
    $sql = "SELECT DISTINCT* FROM employees WHERE  custId = $id
";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();
        echo '{"success": true,"error_message": null, "result":' . json_encode($numrows) . ', "code": 200 }';
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Update employees using id (id) (PUT VERSION)

$app->put('/api/admin/employee/update/{empId}', function(Request $request, Response $response) {

    $id = $request->getAttribute('empId');

    $firstname = $request->getParam('firstname');
    $lastname = $request->getParam('lastname');
    $email = $request->getParam('email');
    $mobilenumber = $request->getParam('mobilenumber');
    $username = $request->getParam('username');
    $password = md5($request->getParam('password'));
    //$photo = $request->getParam('photo');
    //function for image upload
    $uploadOk = 0;
    if (isset($_FILES['photo'])) {
        $trk = new clstrak();
        $target_dir = "/Henchman/public/uploads/";
        $temp = explode(".", $_FILES["photo"]["name"]);
        $newfilename = round(microtime(true)) . '.' . end($temp);
        $target_file = $target_dir . $newfilename;
        $uploadOk = $trk->photoUpload();
    }
    $sql = "UPDATE employees SET
                   firstname = :firstname,
                   lastName = :lastname,
                   email = :email,
                   mobileNumber = :mobilenumber,
                   username = :username,
                   photo = :photo,
                   password = :password                   
             WHERE id = :id";

    if ($uploadOk == 0) {
        $target_file = "";
        $error_msg = "File not uploaded (please check size, type and name of the file : possibly no file was selected)";
    } else {
        //upload was successfull: remove old file
        //check is any picture exist and unset it
        $sql2 = "SELECT\n" .
                "employees.photo\n" .
                "FROM\n" .
                "employees\n" .
                "WHERE\n" .
                "employees.id = $id  \n";
        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql2);
            $picture = $stmt->fetch(PDO::FETCH_OBJ);
            $picture = $picture->photo;
            if (file_exists($picture)) {
                unlink($picture);
            }
        } catch (PDOException $e) {
            
        } 
 
    }
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mobilenumber', $mobilenumber);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':photo', $target_file);
        $stmt->execute();
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
    //geting the user data to return
    $sql = "SELECT\n" .
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
            "employees\n" .
            "LEFT JOIN customers ON customers.id = employees.custid\n" .
            "WHERE\n" .
            "employees.id = $id  \n";
    $user = null;
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if ($user) {
            echo '{"success": true,"error_message": "' . $error_msg . '", "result":' . json_encode($user, JSON_UNESCAPED_SLASHES) . ', "code": 200 }';
            $user = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "User doesn\'t exist" , "code": 202 }';
        }
        $db = null;
        //echo '{"success": true,"error_message": null, "result":"Employee updated", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Update employees using id (id) (POST VERSION)

$app->post('/api/admin/employee/update/{empId}', function(Request $request, Response $response) {

    $id = $request->getAttribute('empId');

    $firstname = $request->getParam('firstname');
    $lastname = $request->getParam('lastname');
    $email = $request->getParam('email');
    $mobilenumber = $request->getParam('mobilenumber');
    $username = $request->getParam('username');
    $password = md5($request->getParam('password'));
    //$photo = $request->getParam('photo');
    //function for image upload
    $uploadOk = 0;
    if (isset($_FILES['photo'])) {
        $trk = new clstrak();
        $target_dir = "/Henchman/public/uploads/";
        $temp = explode(".", $_FILES["photo"]["name"]);
        $newfilename = round(microtime(true)) . '.' . end($temp);
        $target_file = $target_dir . $newfilename;
        $uploadOk = $trk->photoUpload();
    }
    $sql = "UPDATE employees SET
                   firstname = :firstname,
                   lastName = :lastname,
                   email = :email,
                   mobileNumber = :mobilenumber,
                   username = :username,
                   photo = :photo,
                   password = :password                   
             WHERE id = :id";

    if ($uploadOk == 0) {
        $target_file = "";
        $error_msg = "File not uploaded (please check size, type and name of the file : possibly no file was selected)";
    } else {
        //upload was successfull: remove old file
        //check is any picture exist and unset it
        $sql2 = "SELECT\n" .
                "employees.photo\n" .
                "FROM\n" .
                "employees\n" .
                "WHERE\n" .
                "employees.id = $id  \n";
        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql2);
            $picture = $stmt->fetch(PDO::FETCH_OBJ);
            $picture = $picture->photo;
            if (file_exists($picture)) {
                unlink($picture);
            }
        } catch (PDOException $e) {
            
        } 
 
    }
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mobilenumber', $mobilenumber);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':photo', $target_file);
        $stmt->execute();
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
    //geting the user data to return
    $sql = "SELECT\n" .
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
            "employees\n" .
            "LEFT JOIN customers ON customers.id = employees.custid\n" .
            "WHERE\n" .
            "employees.id = $id  \n";
    $user = null;
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if ($user) {
            echo '{"success": true,"error_message": "' . $error_msg . '", "result":' . json_encode($user, JSON_UNESCAPED_SLASHES) . ', "code": 200 }';
            $user = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "User doesn\'t exist" , "code": 202 }';
        }
        $db = null;
        //echo '{"success": true,"error_message": null, "result":"Employee updated", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});



// Add new employee

$app->post('/api/admin/employee/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $firstname = $request->getParam('firstname');
    $lastname = $request->getParam('lastname');
    $email = $request->getParam('email');
    $mobilenumber = $request->getParam('mobilenumber');
    $createddate = date("Y-m-d h:i:sa");
    $username = $request->getParam('username');
    $password = md5($request->getParam('password'));

    $trk = new clstrak();

    if ($trk->isEmployeeEmailExist($email) or $trk->isUsernameExist($username)) {


        if ($trk->isEmployeeEmailExist($email) or $trk->isUsernameExist($username)) {

            echo '{"success": false,"error_message": "Username/Email  already exist" , "code": 202 }';
        };
    } else {
            //function for image upload
        $uploadOk = 0;
        if ( isset($_FILES['photo']) ) {       
          $trk = new clstrak();
          $target_dir = "/Henchman/public/uploads/";
              $temp = explode(".", $_FILES["photo"]["name"]);
              $newfilename = round(microtime(true)) . '.' . end($temp);
              $target_file = $target_dir . $newfilename;
              $uploadOk = $trk->photoUpload();                 
        }
        
        if ($uploadOk == 0) {
            $target_file = "";
            $error_msg = "File not uploaded (please check size, type and name of the file : possibly no file was selected)";
        } else {
            $error_msg = "NULL";
        }

        $sql = "INSERT INTO employees (firstname, lastname, email,mobilenumber, custid,createddate,username,password,photo) VALUES 
            (:firstname, :lastname,:email, :mobilenumber,  :custid, :createddate, :username, :password, :photo)";
        try {
            // Get DB object
            $db = new db();
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
            $stmt->bindParam(':photo', $target_file);


            $stmt->execute();
            $db = null;



            echo '{"success": true,"error_message": "'.$error_msg.'", "result":"Employee Added.", "code": 200 }';
        } catch (PDOException $e) {


            echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
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

    $sql = "SELECT DISTINCT\n" .
            "tails.id,\n" .
            "tails.custid,\n" .
            "tails.number,\n" .
            "tails.active,\n" .
            "tails.description\n" .
            "FROM tails\n" .
            "WHERE custid = $custid";

    $tails = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $tails = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($tails) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($tails) . ', "code": 200 }';
            $tails = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Count tails 

$app->get('/api/admin/tails/count/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT DISTINCT\n" .
            "tails.id,\n" .
            "tails.custid,\n" .
            "tails.number,\n" .
            "tails.active,\n" .
            "tails.description\n" .
            "FROM tails\n" .
            "WHERE custid = $custid";

    $tails = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $tails = $stmt->fetchAll(PDO::FETCH_OBJ);
        $countTails = $stmt->rowCount();
        if ( !empty($countTails) ) {
            echo '{"success": true,"error_message": null, "result":' . $countTails . ', "code": 200 }';
            $tails = null;
            $db = null;
        } else { 
            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});
// Get specific tail

$app->get('/api/admin/tail/view/{custid}/{number}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $number = $request->getAttribute('number');
    // Select statement

    $sql = "SELECT DISTINCT\n" .
            "tails.id,\n" .
            "tails.custid,\n" .
            "tails.number,\n" .
            "tails.active,\n" .
            "tails.description\n" .
            "FROM tails\n" .
            "WHERE custid = $custid AND\n" .
            "number = $number";
    $tails = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $tails = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($tails) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($tails) . ', "code": 200 }';
            $tails = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


// Add new tail

$app->post('/api/admin/tail/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $number = $request->getParam('number');
    $description = $request->getParam('description');
    //$createddate = date("Y-m-d h:i:sa");

    $trk = new clstrak();

    if ($trk->isTailNumberExist($number, $custid)) {



        echo '{"success": false,"error_message": "Tail already exist" , "code": 202 }';
    } else {

        $sql = "INSERT INTO tails (custid, number, description) VALUES 
            (:custid, :number,:description)";
        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->bindParam(':custid', $custid);
            $stmt->bindParam(':number', $number);
            $stmt->bindParam(':description', $description);


            $stmt->execute();
            $db = null;

            echo '{"success": true,"error_message": null, "result":"Tail Added.", "code": 200 }';
        } catch (PDOException $e) {

            echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
        }
    }
});


//Update tail

$app->put('/api/admin/tail/update/{number}/{custid}', function(Request $request, Response $response) {

    $newNumber = $request->getParam('newNumber');
    $custid = $request->getAttribute('custid');
    $number = $request->getAttribute('number');
    $description = $request->getParam('description');



    $sql = "UPDATE tails SET
                   number = :newNumber,
                   description = :description
                  
             WHERE number = :number AND custid = :custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':number', $number);
        $stmt->bindParam(':newNumber', $newNumber);
        $stmt->bindParam(':description', $description);
        $stmt->execute();
        $db = null;



        echo '{"success": true,"error_message": null, "result":"Tail updated.", "code": 200 }';
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


// Deactivate specific Tail

$app->put('/api/admin/tail/disable/{number}/{custid}', function(Request $request, Response $response) {

    $number = $request->getAttribute('number');
    $custid = $request->getAttribute('custid');



    $sql = "UPDATE tails SET
                    active = 0
             WHERE number = '$number' AND custid = $custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);
        $stmt->execute();
        $db = null;


        echo '{"success": true,"error_message": null, "result":"Tail disabled.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Activate specific Tail

$app->put('/api/admin/tail/enable/{number}/{custid}', function(Request $request, Response $response) {

    $number = $request->getAttribute('number');
    $custid = $request->getAttribute('custid');



    $sql = "UPDATE tails SET
                    active = 1
             WHERE number = '$number' AND custid = $custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);
        $stmt->execute();
        $db = null;


        echo '{"success": true,"error_message": null, "result":"Tail enabled.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"error": {"text": ' . $e->getMessage() . '}';
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
            "users.userid,\n" .
            "users.level\n" .
            "FROM\n" .
            "users\n" .
            "LEFT JOIN employees ON employees.id = users.userid\n" .
            "WHERE\n" .
            "users.auditrak = 1 AND\n" .
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
// Count users
$app->get('/api/admin/users/count/{custid}', function(Request $request, Response $response) {

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
            "users.userid,\n" .
            "users.level\n" .
            "FROM\n" .
            "users\n" .
            "LEFT JOIN employees ON employees.id = users.userid\n" .
            "WHERE\n" .
            "users.auditrak = 1 AND\n" .
            "users.custid = $custid";

    $users = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);
        $users = $stmt->rowCount();
        if ($users) {
            echo '{"success": true,"error_message": null, "result":' . $users . ', "code": 200 }';
            $users = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});
// Get specific user
$app->get('/api/admin/user/{userid}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    // Select statement

    $sql = "SELECT DISTINCT\n" .
            "employees.id,\n" .
            "employees.firstname,\n" .
            "employees.lastname,\n" .
            "employees.username,\n" .
            "employees.`password`,\n" .
            "employees.email,\n" .
            "employees.photo,\n" .
            "users.id,\n" .
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
            "users.userid = $userid AND\n" .
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
//Update User role 1 - Administrator 0 - User

$app->put('/api/admin/user/update/role/{custid}/{userid}/{role}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');
    $role = $request->getAttribute('role');

    if ($role < 0 OR $role > 1) {
        echo '{"error": {"text": User role must be administrator or User }';
    } else {
        $sql = "UPDATE users SET
                   role = :role
             WHERE id = $userid AND custid = $custid";

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->bindParam(':role', $role);


            $stmt->execute();
            $db = null;

            if ($role == 1) {
                echo '{"success": true,"error_message": null, "result":"User role set to administrator.", "code": 200 }';
            } else {


                echo '{"success": true,"error_message": null, "result":"User role set to user", "code": 200 }';
            }
        } catch (PDOException $e) {
            echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
        }
    }
});
//Update User Level

$app->post('/api/admin/user/update/level/{custid}/{userid}/{level}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');
    $level = $request->getAttribute('level');


    $sql = "UPDATE users SET
                   level = :level
             WHERE id = $userid AND custid = $custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':level', $level);


        $stmt->execute();
        $db = null;

        echo '{"success": true,"error_message": null, "result":"User level updated.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});
// Update user record

$app->put('/api/admin/user/update/{userid}/{custid}/{level}/{role}', function(Request $request, Response $response) {

    $userid = $request->getAttribute('userid');
    $custid = $request->getAttribute('custid');

    $role = $request->getAttribute('role');
    $level = $request->getAttribute('level');



    $sql = "UPDATE users SET
                   role = :role,
                   level = :level
             WHERE userid = $userid AND custid = $custid AND auditrak= 1";
    //echo $sql;
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        // $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':level', $level);


        $stmt->execute();
        $db = null;
        echo '{"success": true,"error_message": null, "result":"User updated.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


/**
 * User  Paths
 */
/**
 * reservation  Paths
 */
// Reservation List
//works
$app->get('/api/admin/reservation/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement


    $sql = "SELECT\n" .
            "reservations.custid,\n" .
            "reservations.reservationdate,\n" .
            "reservations.reservationtime,\n" .
            "reservations.userid,\n" .
            "reservations.kitid,\n" .
            "reservations.toolid,\n" .
            "reservations.flag,\n" .
            "customers.`name`,\n" .
            "employees.firstname,\n" .
            "employees.lastname,\n" .
            "kits.description AS kitname,\n" .
            "lockers.description AS lockername,\n" .
            "tools.stockcode,\n" .
            "tools.description toolname\n" .
            "FROM\n" .
            "reservations\n" .
            "LEFT JOIN customers ON customers.id = reservations.custid\n" .
            "LEFT JOIN employees ON employees.id = reservations.userid\n" .
            "LEFT JOIN kits ON kits.id = reservations.kitid\n" .
            "LEFT JOIN lockers ON lockers.id = kits.lockerid\n" .
            "LEFT JOIN kittools ON kittools.id = reservations.toolid\n" .
            "LEFT JOIN tools ON tools.id = kittools.toolid\n" .
            "WHERE\n" .
            "reservations.custid = $custid\n" .
            "ORDER BY\n" .
            "reservations.reservationdate DESC,\n" .
            "reservations.reservationtime DESC";

    $reservation = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $reservation = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($reservation) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($reservation) . ', "code": 200 }';
            $reservation = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


// Log List
//works

$app->get('/api/admin/log/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT * FROM log WHERE custid = $custid ORDER BY logdate DESC";



    $logs = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($logs) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($logs) . ', "code": 200 }';
            $logs = null;
            $db = null;
        } else {

            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


// Lockers
// View locker list
//error

$app->get('/api/admin/locker/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT\n" .
            "lockers.id,\n" .
            "lockers.custid,\n" .
            "lockers.description AS lockername,\n" .
            "lockers.`code`,\n" .
            "lockers.locationid,\n" .
            "locations.description AS locationname\n" .
            "FROM\n" .
            "lockers\n" .
            "LEFT JOIN locations ON locations.id = lockers.locationid\n" .
            "WHERE\n" .
            "lockers.custid = $custid";



    $lockers = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);
        //var_dump($logs);
        die;
        if ($logs) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($lockers) . ', "code": 200 }';
            $logs = null;
            $db = null;
        } else {

            echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
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

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        // $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':locationid', $locationid);


        $stmt->execute();
        $db = null;



        echo '{"success": true,"error_message": null, "result":"locker updated.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Add locker
//works
$app->post('/api/admin/locker/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $description = $request->getParam('description');
    $code = $request->getParam('code');
    $locationid = $request->getParam('locationid');
    $active = $request->getParam('active');

    // $active = 1;



    $sql = "INSERT INTO lockers (custid, description, code,locationid, active) VALUES 
            (:custid, :description,:code, :locationid, :active)";
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':locationid', $locationid);
        $stmt->bindParam(':active', $active);


        $stmt->execute();
        $db = null;


        echo '{"success": true,"error_message": null, "result":"locker Added", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Disable specific  locker
//works
$app->put('/api/admin/locker/disable/1{custid}/{lockerid}', function(Request $request, Response $response) {

    $lockerid = $request->getAttribute('lockerid');
    $custid = $request->getAttribute('custid');



    $sql = "UPDATE lockers SET
                   active = 0
             WHERE id = $lockerid AND custid = $custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);



        $stmt->execute();
        $db = null;

        echo '{"success": true,"error_message": null, "result":"locker disabled.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// enable specific  locker
//works
$app->put('/api/admin/locker/enable/{custid}/{lockerid}', function(Request $request, Response $response) {

    $lockerid = $request->getAttribute('lockerid');
    $custid = $request->getAttribute('custid');



    $sql = "UPDATE lockers SET
                   active = 1
             WHERE id = $lockerid AND custid = $custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);



        $stmt->execute();
        $db = null;
        echo '{"success": true,"error_message": null, "result":"locker enabled", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


//KITS
// View kit List

$app->get('/api/admin/kits/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT\n" .
            "kits.id,\n" .
            "kits.lockerid,\n" .
            "kits.description,\n" .
            "kits.custid,\n" .
            "kits.reserved,\n" .
            "kits.qrcode,\n" .
            "kits.kitlocation,\n" .
            "kits.`status`,\n" .
            "lockers.description AS lockername,\n" .
            "locations.description AS locationname\n" .
            "FROM\n" .
            "kits\n" .
            "LEFT JOIN lockers ON lockers.id = kits.lockerid\n" .
            "LEFT JOIN locations ON locations.id = lockers.locationid\n" .
            "WHERE\n" .
            "kits.custid = $custid";



    $kits = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($kits) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($kits) . ', "code": 200 }';

            $kits = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
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

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        // $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':qrcode', $qrcode);
        $stmt->bindParam(':lockerid', $lockerid);


        $stmt->execute();
        $db = null;



        echo '{"success": true,"error_message": null, "result":"Kit updated.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Add KIT

$app->post('/api/admin/kit/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $description = $request->getParam('description');
    $qrcode = $request->getParam('qrcode');
    $lockerid = $request->getParam('lockerid');




    $sql = "INSERT INTO kits (custid, description, lockerid,qrcode, reserved,status) VALUES 
            (:custid, :description,:lockerid, :qrcode, 0, 0)";
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':qrcode', $qrcode);
        $stmt->bindParam(':lockerid', $lockerid);


        $stmt->execute();
        $db = null;

        echo '{"success": true,"error_message": null, "result":"Kit Added.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

//Kit Tools
// View kit Tool List

$app->get('/api/admin/kittools/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT\n" .
            "kittools.id,\n" .
            "kittools.kitid,\n" .
            "kittools.toolid,\n" .
            "kittools.custid,\n" .
            "kittools.reserved,\n" .
            "kittools.qrcode,\n" .
            "kittools.`status`,\n" .
            "tools.stockcode,\n" .
            "tools.description AS toolname,\n" .
            "tools.serialno,\n" .
            "tools.categoryid,\n" .
            "toolcategories.description AS categoryname,\n" .
            "kits.description AS kitname,\n" .
            "lockers.description AS lockername,\n" .
            "kits.lockerid\n" .
            "FROM\n" .
            "kittools\n" .
            "LEFT JOIN tools ON tools.id = kittools.toolid\n" .
            "LEFT JOIN toolcategories ON toolcategories.id = tools.categoryid\n" .
            "LEFT JOIN kits ON kits.id = kittools.kitid\n" .
            "LEFT JOIN lockers ON lockers.id = kits.lockerid\n" .
            "WHERE\n" .
            "kittools.custid = $custid";



    $kittools = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($kittools) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($kittools) . ', "code": 200 }';

            $kits = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
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

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':kitid', $kitid);
        $stmt->bindParam(':toolid', $toolid);
        $stmt->bindParam(':qrcode', $qrcode);


        $stmt->execute();
        $db = null;


        echo '{"success": true,"error_message": null, "result":"Kit tool updated.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Add KIT Tool

$app->post('/api/admin/kittool/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $kitid = $request->getParam('kitid');
    $toolid = $request->getParam('toolid');
    $qrcode = $request->getParam('qrcode');




    $sql = "INSERT INTO kittools (kitid, toolid,custid,reserved,qrcode,status) VALUES 
            (:kitid, :toolid,:custid, 0, :qrcode, 0)";
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':kitid', $kitid);
        $stmt->bindParam(':toolid', $toolid);
        $stmt->bindParam(':qrcode', $qrcode);


        $stmt->execute();
        $db = null;



        echo '{"success": true,"error_message": null, "result":"Kit Tool Added.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

//TOOLS
// View Tool List

$app->get('/api/admin/tools/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT\n" .
            "tools.id,\n" .
            "tools.custid,\n" .
            "tools.stockcode,\n" .
            "tools.description AS toolname,\n" .
            "tools.serialno,\n" .
            "tools.categoryid,\n" .
            "tools.toolimage,\n" .
            "toolcategories.description AS categoryname\n" .
            "FROM\n" .
            "tools\n" .
            "LEFT JOIN toolcategories ON toolcategories.id = tools.categoryid\n" .
            "WHERE\n" .
            "tools.custid = $custid";



    $tools = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($tools) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($tools) . ', "code": 200 }';

            $kits = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
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
    $serialno = $request->getParam('serialno');




    $sql = "UPDATE tools SET
                   stockcode = :stockcode,
                   description = :description,
                   serialno = :serialno,
                   categoryid = :categoryid,
                   toolimage = :toolimage,
             WHERE id = $toolid AND custid = $custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':stockcode', $stockcode);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':serialno', $serialno);
        $stmt->bindParam(':categoryid', $categoryid);
        $stmt->bindParam(':toolimage', $toolimage);


        $stmt->execute();
        $db = null;



        echo '{"success": true,"error_message": null, "result":"tool updated.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Add Tool

$app->post('/api/admin/tool/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $stockcode = $request->getParam('stockcode');
    $description = $request->getParam('description');
    $categoryid = $request->getParam('categoryid');
    $toolimage = $request->getParam('toolimage');
    $serialno = $request->getParam('serialno');



    $sql = "INSERT INTO tools (custid,stockcode,description,serialno,categoryid,toolimage) VALUES 
            (:custid,:stockcode,:description,:serialno,:categoryid,:toolimage)";
    try {
        // Get DB object
        $db = new db();
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



        echo '{"success": true,"error_message": null, "result":"Tool Added.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Tool Category
// View Tool Category List
//works?
$app->get('/api/admin/toolcategory/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement

    $sql = "SELECT * FROM toolcategories WHERE custid = $custid";


    $categories = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($categories) {


            echo '{"success": true,"error_message": null, "result":' . json_encode($categories) . ', "code": 200 }';
            $categories = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

//Update tool categoy
//works
$app->put('/api/admin/toolcategory/update/{custid}/{id}', function(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');



    $sql = "UPDATE toolcategories SET
                   description = :description
             WHERE id = $id AND custid = $custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);


        $stmt->execute();
        $db = null;


        echo '{"success": true,"error_message": null, "result":"Tool category updated", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Add Tool category
//works
$app->post('/api/admin/toolcategory/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');



    $sql = "INSERT INTO toolcategories (custid,description) VALUES 
            (:custid,:description)";
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':description', $description);


        $stmt->execute();
        $db = null;


        echo '{"success": true,"error_message": null, "result":"Tool Category  Added", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

//LOCATIONS
// View location List
//works?
$app->get('/api/admin/locations/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    // Select statement
    //$sql = "SELECT * FROM locations WHERE custid = $custid";
    $sql = "SELECT\n" .
            "locations.description as locationName,\n" .
            "locations.custid,\n" .
            "kits.lockerid,\n" .
            "kits.`description` as kitName,\n" .
            "kits.reserved,\n" .
            "kits.qrcode,\n" .
            "kits.kitlocation,\n" .
            "kits.status\n" .
            "FROM\n" .
            "kits\n" .
            "LEFT JOIN locations ON kits.kitlocation = locations.id\n" .
            "WHERE\n" .
            "locations.custid = $custid";

    $locations = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $locations = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($locations) {
            echo '{"success": true,"error_message": null, "result":' . json_encode($locations) . ', "code": 200 }';

            $locations = null;
            $db = null;
        } else {


            echo '{"success": false,"error_message": "No records found." , "code": 202 }';
        }
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
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

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':description', $description);


        $stmt->execute();
        $db = null;

        echo '{"success": true,"error_message": null, "result":"Location updated", "code": 200 }';
    } catch (PDOException $e) {


        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Add location
//works
$app->post('/api/admin/location/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $description = $request->getParam('description');



    $sql = "INSERT INTO locations (custid,description) VALUES 
            (:custid,:description)";
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':description', $description);


        $stmt->execute();
        $db = null;


        echo '{"success": true,"error_message": null, "result":"Location  Added", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

























