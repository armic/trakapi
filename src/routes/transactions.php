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
//$app->post('/api/transaction/issue/{custid}', function(Request $request, Response $response) {

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

       if($flag == KIT) {
           if($trk->isKitReserved($custid,$kitid)) {
               echo '{"warning": {"Message": "Kit is reserved"}';
               exit(0);
           };

       }else{
           if($trk->isToolReserved($custid,$kittoolid)) {
               echo '{"warning": {"Message": "Tool is reserved"}';
               exit(0);
           };
       };

    if($trk->isTransactionExist($custid,$userid,$tailid,$kitid,$kittoolid,$flag))
    {
        echo '{"warning": {"Message": "Transaction already exist"}';
    }else {

        $sql = "INSERT INTO audittraktransactions (custid, type, datetimeissued,userid, tailid,lockerid,kitid,datereturned, kittoolid,workorder,flag) VALUES 
            (:custid, :type, :datetimeissued,:userid, :tailid,:lockerid,:kitid, :datereturned, :kittoolid,:workorder, :flag)";
        try{
            // Get DB object
            $db= new db();
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

            if($flag == KIT) {
                $trk->updateKitStatus($custid,$kitid, OUT);
            }else{
                $trk->updateToolStatus($custid,$kittoolid, OUT);
            };

            // Inform

            echo '{"notice": {"text": "Issue successful"}';

        }catch(PDOException $e){
            echo '{"error": {"text": '.$e->getMessage().'}';

        }

    }


});


//Return

// {flag can be 1 for KIT or 0 for TOOL}
$app->post('/api/transaction/return/{custid}/{userid}/{flag}', function(Request $request, Response $response) {
//$app->post('/api/transaction/issue/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $flag = $request->getAttribute('flag');
    $type = IRETURN;
    $datetimereturned = date("Y-m-d h:i:sa");
    $tailid = $request->getParam('tailid');
    $lockerid = $request->getParam('lockerid');
    $kitid = $request->getParam('kitid');
    $kittoolid = $request->getParam('kittoolid');
    $workorder = $request->getParam('workorder');

    $trk = new clstrak();

    if($trk->isTransactionExist($custid,$userid,$tailid,$kitid,$kittoolid,$flag)) {

        $sql = "UPDATE audittraktransactions SET type = $type, datereturned = '$datetimereturned' WHERE custid = $custid AND userid = $userid AND type = 1 AND tailid = $tailid AND lockerid = $lockerid AND  workorder = $workorder";

        if($flag == KIT) {
            $sql = $sql. " AND kitid = $kitid";
        }else{
            $sql = $sql. " AND kittoolid = $kittoolid";
        }
echo $sql;

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

            echo '{"notice": {"text": "Return successful"}';

        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}';

        }
    } else {

        echo '{"warning": {"Message": "Return failed"}';

    }


});


// Count user kit issued transactions

$app->get('/api/transaction/kit/issued/user/count/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 1 AND flag = 1";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();
        echo '{"Kit issued": {"Total": '.json_encode($numrows).'}';

    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Count user kit returned transactions

$app->get('/api/transaction/kit/returned/user/count/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 0 AND flag = 1";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();
        echo '{"Kit returned": {"Total": '.json_encode($numrows).'}';

    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Count user tool issued transactions

$app->get('/api/transaction/tool/issued/user/count/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 1 AND flag = 0";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();
        echo '{"Tool issued": {"Total": '.json_encode($numrows).'}';

    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Count user tools returned transactions

$app->get('/api/transaction/tool/returned/user/count/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 0 AND flag = 0";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $numrows = $stmt->rowCount();
        echo '{"Tool returned": {"Total": '.json_encode($numrows).'}';

    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Issued kit/tools list per customer

$app->get('/api/transaction/list/issued/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND type = 1";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($kittransactions) {
            echo  '{"Out kit/tools": '. json_encode($kittransactions). '}';
            $employees = null;
            $db = null;
        } else {
            echo '{"error":"No issuance transactions found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});



// Returned kit/tools list per customer

$app->get('/api/transaction/list/returned/{custid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND type = 0";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($kittransactions) {
            echo  '{"Returned kit/tools": '. json_encode($kittransactions). '}';
            $employees = null;
            $db = null;
        } else {
            echo '{"error":"No return transactions found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Issued kit/tools list per user

$app->get('/api/transaction/list/user/issued/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 1";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($kittransactions) {
            echo  '{"Out kit/tools": '. json_encode($kittransactions). '}';
            $employees = null;
            $db = null;
        } else {
            echo '{"error":"No issuance transactions found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// returned  kit/tools list per user

$app->get('/api/transaction/list/user/returned/{custid}/{userid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');

    $kittransactions = null;

    // Select statement
    $sql = "SELECT DISTINCT* FROM audittraktransactions WHERE  custid = $custid AND userid = $userid AND type = 0";

    try{
        // Get DB object
        $db= new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $kittransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

        if($kittransactions) {
            echo  '{"Returned kit/tools": '. json_encode($kittransactions). '}';
            $employees = null;
            $db = null;
        } else {
            echo '{"error":"No returned transactions found.")';
        }


    }catch(PDOException $e){
        echo '{"error": {"Message": '.$e->getMessage().'}';

    }
});

// Add KIt reservation

$app->post('/api/transaction/reservation/kit/add/{custid}/{userid}/{kitid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $kitid = $request->getAttribute('kitid');
    $reservationdate  = date("Y-m-d");
    $reservationtime = date("h:i:sa");
    $flag = 1;

    $reservation = null;

    // Select statement
    // $sql = "INSERT INTO users (custid,active,auditrak,role,userid,level) VALUES (:custid,:active, :auditrak, :role, :userid, :
    $sql = "INSERT INTO reservations (reservationdate, reservationtime, custid, userid,kitid,flag) VALUES (:reservationdate, :reservationtime, :custid, :userid,:kitid,:flag)";

    try{
        // Get DB object
        $db= new db();
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
        echo '{"notice": {"Message": "KIT Reservation Added"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }
});


// Add Tool  reservation

$app->post('/api/transaction/reservation/tool/add/{custid}/{userid}/{toolid}', function(Request $request, Response $response) {

    $custid = $request->getAttribute('custid');
    $userid = $request->getAttribute('userid');
    $toolid = $request->getAttribute('toolid');
    $reservationdate  = date("Y-m-d");
    $reservationtime = date("h:i:sa");
    $flag = 0;

    $reservation = null;

    // Select statement
    // $sql = "INSERT INTO users (custid,active,auditrak,role,userid,level) VALUES (:custid,:active, :auditrak, :role, :userid, :
    $sql = "INSERT INTO reservations (reservationdate, reservationtime, custid, userid,toolid,flag) VALUES (:reservationdate, :reservationtime, :custid, :userid,:toolid,:flag)";

    try{
        // Get DB object
        $db= new db();
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
        echo '{"notice": {"Message": "TOOL Reservation Added"}';

    }catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';

    }
});





