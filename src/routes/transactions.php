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
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

//Issuance
// {flag can be 1 for KIT or 0 for TOOL}

$app->post('/api/transaction/issue/{custid}/{userid}/{flag}', function(Request $request, Response $response) {
    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $flag = $request->getAttribute('flag');
    $type = ISSUE;
    $datetimeissued = date("Y-m-d h:i:sa");
    $tailid = $request->getParam('tailid');
    $lockerid = $request->getParam('lockerid');
    $kitid = $request->getParam('kitid');
    $datereturned = null;
    $kittoolid = $request->getParam('kittoolid');
    $workorder = $request->getParam('workorder');

    $trk = new clstrak();

    if ($flag == KIT) {
        if ($trk->isKitReserved($custid, $kitid)) {

            echo '{"success": false,"error_message": "Kit is reserved" , "code": 202 }';
            exit(0);
        }
    } else {
        if ($trk->isToolReserved($custid, $kittoolid)) {
            echo '{"success": false,"error_message": "Tool is reserved" , "code": 202 }';
            exit(0);
        };
    }

    if ($trk->isTransactionExist($custid, $userid, $tailid, $kitid, $kittoolid, $flag)) {

        echo '{"success": false,"error_message": "Transaction already exist" , "code": 202 }';
    } else {

        $sql = "INSERT INTO audittraktransactions (custid, type, datetimeissued,userid, tailid,lockerid,kitid,datereturned, kittoolid,workorder,flag) VALUES 
            (:custid, :type, :datetimeissued,:userid, :tailid,:lockerid,:kitid, :datereturned, :kittoolid,:workorder, :flag)";

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->bindParam(':custid', $custid);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':datetimeissued', $datetimeissued);
            $stmt->bindParam(':userid', $userid);
            $stmt->bindParam(':tailid', $tailid);
            $stmt->bindParam(':lockerid', $lockerid);
            $stmt->bindParam(':kitid', $kitid);
            $stmt->bindParam(':datereturned', $datereturned);
            $stmt->bindParam(':kittoolid', $kittoolid);
            $stmt->bindParam(':workorder', $workorder);
            $stmt->bindParam(':flag', $flag);


            $stmt->execute();

            $db = null;

            if ($flag == KIT) {
                $trk->updateKitStatus($custid, $kitid, OUT);
            } else {
                $trk->updateToolStatus($custid, $kittoolid, OUT);
            }

            // Inform
            echo '{"success": true,"error_message": null, "result":"Issue successful.", "code": 200 }';
        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}';
        }
    }
});


// {flag can be 1 for KIT or 0 for TOOL}

$app->post('/api/transaction/return/{custid}/{userid}/{flag}', function(Request $request, Response $response) {


    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $flag = $request->getAttribute('flag');
    $type = BACK;
    $datetimereturned = date("Y-m-d h:i:sa");
    $tailid = $request->getParam('tailid');
    $lockerid = $request->getParam('lockerid');
    $kitid = $request->getParam('kitid');
    $kittoolid = $request->getParam('kittoolid');
    $workorder = $request->getParam('workorder');

    $trk = new clstrak();

    if ($trk->isTransactionExist($custid, $userid, $tailid, $kitid, $kittoolid, $flag)) {

        $sql = "UPDATE audittraktransactions SET type = $type, datereturned = '$datetimereturned' WHERE custid = $custid AND userid = $userid AND type = 1 AND tailid = $tailid AND lockerid = $lockerid AND  workorder = '$workorder'";


        if ($flag == KIT) {
            $sql = $sql . " AND kitid = $kitid";
        } else {
            $sql = $sql . " AND kittoolid = $kittoolid";
        }

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);

            $stmt->execute();
            $db = null;

            if ($flag == KIT) {
                $trk->updateKitStatus($custid, $kitid, IN);
            } else {
                $trk->updateToolStatus($custid, $kittoolid, IN);
            }


            echo '{"success": true,"error_message": null, "result":"Success", "code": 200 }';
        } catch (PDOException $e) {
            echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
        }
    } else {


        echo '{"success": false,"error_message": "UnSuccessful" , "code": 202 }';
    }
});


// Count user kit issued transactions

$app->get('/api/transaction/kit/issued/user/count/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT * FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 1 AND flag = 1";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();

        echo '{"success": true,"error_message": null, "result":' . json_encode($numrows) . ', "code": 200 }';
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "No Kits/Tools issued." , "code": 202 }';
    }
});

// Count user kit returned transactions

$app->get('/api/transaction/kit/returned/user/count/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 0 AND flag = 1";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();

        echo '{"success": true,"error_message": null, "result":' . json_encode($numrows) . ', "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Count user tool issued transactions

$app->get('/api/transaction/tool/issued/user/count/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 1 AND flag = 0";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();

        echo '{"success": true,"error_message": null, "result":' . json_encode($numrows) . ', "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Count user tools returned transactions

$app->get('/api/transaction/tool/returned/user/count/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 0 AND flag = 0";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();

        echo '{"success": true,"error_message": null, "result":' . json_encode($numrows) . ', "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Issued kit/tools list per customer

$app->get('/api/transaction/list/issued/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $kittransactions = null;

    // Select statement
//    $sql = "SELECT DISTINCT * FROM audittraktransactions WHERE  custid = $custid AND type = 1";

    
     $sql = "SELECT\n" .
            "audittraktransactions.id,\n" .
            "audittraktransactions.custid,\n" .
            "audittraktransactions.type,\n" .
             "audittraktransactions.datetimeissued,\n" .
             
             "audittraktransactions.tailid,\n" .
             "audittraktransactions.lockerid,\n" .
            "audittraktransactions.kitid,\n" .
            "audittraktransactions.datereturned,\n" .
             "audittraktransactions.kittoolid,\n" .
             "audittraktransactions.workorder,\n" .
             "audittraktransactions.flag,\n" .
            
       	    "users.id as userid,\n".
            "users.active,\n" .
            "users.kabtrak,\n" .
            "users.portatrak,\n" .
            "users.cribtrak,\n" .
            "users.auditrak,\n" .
            "users.role,\n" .
            "users.level,\n" . 


	    "employees.id,\n".
            "employees.	firstname,\n" .
            "employees.lastname,\n" .
            "employees.username,\n" . 
            "employees.email,\n" .
            "employees.photo,\n" .
            "employees.mobilenumber,\n" . 
            "employees.createddate,\n" . 

		
             
            "tails.number,\n" .
            "tails.description as tailName, \n" .
             "tails.active,\n" .
             
             "lockers.description as lockerName,\n" .
            "lockers.code, \n" .
             "lockers.active, \n" .
             
             
            "kits.description as kitName, \n" .
            "kits.reserved,\n" .
            "kits.qrcode, \n" .
            "kits.status, \n" .

	    "locations.id, \n" .
	     "locations.description as locationName\n" .  
             
            "FROM\n" .
            "audittraktransactions\n" .
            "LEFT JOIN users ON users.id = audittraktransactions.userid \n" .
            "LEFT JOIN tails ON tails.id = audittraktransactions.tailid \n" .
            "LEFT JOIN lockers ON lockers.id = audittraktransactions.lockerid \n" .
            "LEFT JOIN kits ON kits.id = audittraktransactions.kitid \n" .
	    "LEFT JOIN employees ON employees.id = audittraktransactions.userid \n" .
	    "LEFT JOIN locations ON audittraktransactions.kitid = kits.id AND  kits.kitlocation =  locations.id \n" .
             
            "WHERE\n" .
            "audittraktransactions.custid = $custid AND TYPE=1";
    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($kittransactions) {

            echo '{"success": true,"error_message": null, "result":' . json_encode($kittransactions) . ', "code": 200 }';
            $employees = null;
            $db = null;
        } else {

            echo '{"success": false,"error_message": "NO kits/tools found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// count Issued kit/tools list per customer

$app->get('/api/transaction/list/issuedcount/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT\n" .
            "audittraktransactions.id,\n" .
            "audittraktransactions.custid,\n" .
            "audittraktransactions.type,\n" .
             "audittraktransactions.datetimeissued,\n" .
             
             "audittraktransactions.tailid,\n" .
             "audittraktransactions.lockerid,\n" .
            "audittraktransactions.kitid,\n" .
            "audittraktransactions.datereturned,\n" .
             "audittraktransactions.kittoolid,\n" .
             "audittraktransactions.workorder,\n" .
             "audittraktransactions.flag,\n" .
            
       	    "users.id as userid,\n".
            "users.active,\n" .
            "users.kabtrak,\n" .
            "users.portatrak,\n" .
            "users.cribtrak,\n" .
            "users.auditrak,\n" .
            "users.role,\n" .
            "users.level,\n" . 


	    "employees.id,\n".
            "employees.	firstname,\n" .
            "employees.lastname,\n" .
            "employees.username,\n" . 
            "employees.email,\n" .
            "employees.photo,\n" .
            "employees.mobilenumber,\n" . 
            "employees.createddate,\n" . 

		
             
            "tails.number,\n" .
            "tails.description as tailName, \n" .
             "tails.active,\n" .
             
             "lockers.description as lockerName,\n" .
            "lockers.code, \n" .
             "lockers.active, \n" .
             
             
            "kits.description as kitName, \n" .
            "kits.reserved,\n" .
            "kits.qrcode, \n" .
            "kits.status, \n" .

	    "locations.id, \n" .
	     "locations.description as locationName\n" .  
             
            "FROM\n" .
            "audittraktransactions\n" .
            "LEFT JOIN users ON users.id = audittraktransactions.userid \n" .
            "LEFT JOIN tails ON tails.id = audittraktransactions.tailid \n" .
            "LEFT JOIN lockers ON lockers.id = audittraktransactions.lockerid \n" .
            "LEFT JOIN kits ON kits.id = audittraktransactions.kitid \n" .
	    "LEFT JOIN employees ON audittraktransactions.userid  = users.userid = employees.id \n" .
	    "LEFT JOIN locations ON audittraktransactions.kitid = kits.id AND  kits.kitlocation =  locations.id \n" .
             
            "WHERE\n" .
            "audittraktransactions.custid = $custid AND TYPE=1";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);
        $kittransactionsno = $stmt->rowCount();
        if ($kittransactionsno) {
             
            echo '{"success": true,"error_message": null, "result":' .$kittransactionsno. ', "code": 200 }';
            $employees = null;
            $db = null;
        } else {

            echo '{"success": false,"error_message": "NO kits/tools found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Returned kit/tools list per customer

$app->get('/api/transaction/list/returned/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND type = 0";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($kittransactions) {

            echo '{"success": true,"error_message": null, "result":' . json_encode($kittransactions) . ', "code": 200 }';
            $employees = null;
            $db = null;
        } else {

            echo '{"success": false,"error_message": "No return transactions found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Issued kit/tools list per user

$app->get('/api/transaction/list/user/issued/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 1";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($kittransactions) {

            echo '{"success": true,"error_message": null, "result":' . json_encode($kittransactions) . ', "code": 200 }';

            $employees = null;
            $db = null;
        } else {

            echo '{"success": false,"error_message": "No issuance transactions found.." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// returned  kit/tools list per user

$app->get('/api/transaction/list/user/returned/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 0";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($kittransactions) {

            echo '{"success": true,"error_message": null, "result":' . json_encode($kittransactions) . ', "code": 200 }';

            $employees = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "No returned transactions found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


// Add Kit  reservation

$app->post('/api/transaction/reservation/kit/add/{custid}/{userid}/{kitid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $kitid = $request->getAttribute('kitid');
    $reservationdate = date("Y-m-d");
    $reservationtime = date("h:i:sa");

    $flag = 0;

    $reservation = null;

    // Select statement
    // $sql = "INSERT INTO users (custid,active,auditrak,role,userid,level) VALUES (:custid,:active, :auditrak, :role, :userid, :
    $sql = "INSERT INTO reservations (reservationdate, reservationtime, custid, userid,kitid,flag) VALUES (:reservationdate, :reservationtime, :custid, :userid,:kitid,:flag)";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':kitid', $kitid);
        $stmt->bindParam(':reservationdate', $reservationdate);
        $stmt->bindParam(':reservationtime', $reservationtime);
        $stmt->bindParam(':flag', $flag);


        $stmt->execute();
        $db = null;

        //update tool reserved tah here
        $trk = new clstrak();

        $trk->updateKitReservedStatus($custid, $kitid, 1);


        echo '{"success": true,"error_message": null, "result":"KIT Reservation Added.", "code": 200 }';
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "Kit Not reserved." , "code": 202 }';
    }
});


// Remove Kit  reservation

$app->delete('/api/transaction/reservation/kit/delete/{custid}/{userid}/{kitid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $kitid = $request->getAttribute('kitid');

    $flag = 0;

    $reservation = null;

    // Select statement
    // $sql = "INSERT INTO users (custid,active,auditrak,role,userid,level) VALUES (:custid,:active, :auditrak, :role, :userid, :
    $sql = "DELETE FROM reservations WHERE custid = $custid AND userid= $userid AND kitid = $kitid AND flag = 0";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);


        $stmt->execute();
        $db = null;

        //update tool reserved tah here
        $trk = new clstrak();

        $trk->updateKitReservedStatus($custid, $toolid, 0);


        echo '{"success": true,"error_message": null, "result":"KIT Reservation removed.", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});


// Add Tool  reservation

$app->post('/api/transaction/reservation/tool/add/{custid}/{userid}/{toolid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $toolid = $request->getAttribute('toolid');
    $reservationdate = date("Y-m-d");
    $reservationtime = date("h:i:sa");
    $flag = 0;

    $reservation = null;

    // Select statement
    // $sql = "INSERT INTO users (custid,active,auditrak,role,userid,level) VALUES (:custid,:active, :auditrak, :role, :userid, :
    $sql = "INSERT INTO reservations (reservationdate, reservationtime, custid, userid,toolid,flag) VALUES (:reservationdate, :reservationtime, :custid, :userid,:toolid,:flag)";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':toolid', $toolid);
        $stmt->bindParam(':reservationdate', $reservationdate);
        $stmt->bindParam(':reservationtime', $reservationtime);
        $stmt->bindParam(':flag', $flag);


        $stmt->execute();
        $db = null;

        //update tool reserved tah here
        $trk = new clstrak();

        $trk->updateToolReservedStatus($custid, $toolid, 1);


        echo '{"success": true,"error_message": null, "result":"TOOL Reservation Added.", "code": 200 }';
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "Tool Not reserved." , "code": 202 }';
    }
});


// Remove Tool  reservation

$app->delete('/api/transaction/reservation/tool/delete/{custid}/{userid}/{toolid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $toolid = $request->getAttribute('toolid');

    $flag = 0;

    $reservation = null;

    // Select statement
    // $sql = "INSERT INTO users (custid,active,auditrak,role,userid,level) VALUES (:custid,:active, :auditrak, :role, :userid, :
    $sql = "DELETE FROM reservations WHERE custid = $custid AND userid= $userid AND toolid = $toolid AND flag = 0";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);


        $stmt->execute();
        $db = null;

        //update tool reserved tah here
        $trk = new clstrak();

        $trk->updateToolReservedStatus($custid, $toolid, 0);


        echo '{"success": true,"error_message": null, "result":"TOOL Reservation removed."", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Load tool list for user selection. Tools that are issued or reserved will not be shown from the list

$app->get('/api/transaction/tools/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $tools = null;

    // Select statement

    $sql = "SELECT\n" .
            "kittools.toolid,\n" .
            "tools.description AS ToolName,\n" .
            "tools.serialno,\n" .
            "tools.categoryid,\n" .
            "tools.toolimage,\n" .
            "kittools.`status`,\n" .
            "kittools.qrcode,\n" .
            "kittools.reserved,\n" .
            "kittools.custid,\n" .
            "kittools.kitid,\n" .
            "kits.description AS kitName,\n" .
            "kits.lockerid,\n" .
            "toolcategories.description AS ToolCategoryName \n" .
            "FROM\n" .
            "kittools\n" .
            "LEFT JOIN tools ON tools.id = kittools.toolid\n" .
            "LEFT JOIN kits ON kits.id = kittools.kitid\n" .
            "LEFT JOIN toolcategories ON toolcategories.id = tools.categoryid\n" .
            "WHERE\n" .
            "kittools.`status` IS NOT NULL AND\n" .
            "kittools.`status` <> 1 AND\n" .
            "kittools.reserved <> 1 AND\n" .
            "kittools.custid = $custid";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $tools = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($tools) {

            echo '{"success": true,"error_message": null, "result":' . json_encode($tools) . ', "code": 200 }';

            $tools = null;
            $db = null;
        } else {

            echo '{"success": false,"error_message": "No tools found." , "code": 202 }';
        }
    } catch (PDOException $e) {

        echo '{"success": false,"error_message": "No tools found."" , "code": 202 }';
    }
});

// Load kit list for user selection. Kits that are issued or reserved will not be shown from the list

$app->get('/api/transaction/kits/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $kits = null;

    // Select statement


    $sql=
    "select k.custid, k.id,k.lockerid,k.description as kitname,k.count,k.reserved, k.status,k.kitlocation,k.qrcode,\n".
    "(select count(*) from kittools t where t.status = 0 and t.custid = k.custid and t.kitid =  k.id) as tool_count,\n".
    "l.id as lockerid,\n".
    "l.description as lockerdescription,\n".
    "l.code,\n".
    "loc.description as location\n".
    "from kits k\n".
    "LEFT JOIN lockers l ON k.lockerid = l.id\n".
    "LEFT JOIN locations loc ON k.kitlocation = loc.id\n".
    "WHERE (k.custid = $custid AND k.reserved = 0 AND k.status = 0)\n".
    "HAVING k.count = tool_count";



    try{

        // Get DB object
        $db= new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $kits = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($kits) { 
            echo '{"success": true,"error_message": null, "result":'. json_encode($kits). ', "code": 200 }';
            $tools = null;
            $kittools = null;
            $db = null;
        } else { 
            echo '{"success": false,"error_message": "No kits found." , "code": 202 }';
        }


    }catch(PDOException $e){
        echo '{"success": false,"error_message": "'.$e->getMessage().'" , "code": 202 }';

    }
});

// Load Tail list for user selection. Active = 0 will not be shown

$app->get('/api/transaction/tails/list/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $tails = null;

    // Select statement
    $sql = "SELECT DISTINCT\n" .
            "tails.id,\n" .
            "tails.number,\n" .
            "tails.description AS tailsName\n" .
            "FROM tails\n" .
            "WHERE custid = $custid AND \n" .
            "active = 1";


    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $tails = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($tails) {

            echo '{"success": true,"error_message": null, "result":' . json_encode($tails) . ', "code": 200 }';

            $tools = null;
            $db = null;
        } else {

            echo '{"success": false,"error_message": "No tails found." , "code": 202 }';
        }
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});

// Add log

$app->post('/api/log/add/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $description = $request->getParam('description');
    $logdate = date("Y-m-d h:i:sa");


    $reservation = null;

    // Select statement
    $sql = "INSERT INTO log (description, logdate, custid) VALUES (:description, :logdate, :custid)";

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':custid', $custid);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':logdate', $logdate);


        $stmt->execute();
        $db = null;



        echo '{"success": true,"error_message": null, "result":"Log entry Added", "code": 200 }';
    } catch (PDOException $e) {
        echo '{"success": false,"error_message": "' . $e->getMessage() . '" , "code": 202 }';
    }
});



// Check kit completeness

$app->get('/api/transaction/kit/complete/{custid}/{kitid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $kitid = $request->getAttribute('kitid');

    $trk = new clstrak();

    if($trk->isKitComplete($custid,$kitid)) {
        echo '{"success": true,"error_message": null, "result":"Kit complete", "code": 200 }';         
    }else{        
        echo '{"success": false,"error_message": "Kit incomplete" , "code": 202 }';
    }


});

// Load tool list per kit will not be shown from the list
$app->get('/api/transaction/kit/tools/list/{custid}/{kitid}', function(Request $request, Response $response) {
    $custid = $request->getAttribute('custid');
    $kitid = $request->getAttribute('kitid');
    $tools = null;
    // Select statement
     $sql=  "SELECT\n".
            "kittools.toolid,\n".
            "tools.description AS ToolName,\n".
            "tools.serialno,\n".
            "tools.categoryid,\n".
            "tools.toolimage,\n".
            "kittools.`status`,\n".
            "kittools.qrcode,\n".
            "kittools.reserved,\n".
            "kittools.custid,\n".
            "kittools.kitid,\n".
            "kits.description AS kitdescription,\n".
            "kits.lockerid,\n".
            "toolcategories.description AS ToolCategory\n".
            "FROM\n".
            "kittools\n".
            "LEFT JOIN tools ON tools.id = kittools.toolid\n".
            "LEFT JOIN kits ON kits.id = kittools.kitid\n".
            "LEFT JOIN toolcategories ON toolcategories.id = tools.categoryid\n".
            "WHERE\n".
            "kittools.kitid = $kitid";
    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $tools = $stmt->fetchAll(PDO::FETCH_OBJ);
        if($tools) { 
            echo '{"success": true,"error_message": null, "result":'. json_encode($tools). ', "code": 200 }';

            $tools = null;
            $db = null;
        } else {
            echo '{"success": false,"error_message": "No tools found." , "code": 202 }';
             
        }
    }catch(PDOException $e){
        echo '{"success": false,"error_message": "'.$e->getMessage().'" , "code": 202 }';
    }
});






