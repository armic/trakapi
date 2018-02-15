<?php
/**
 * Copyright (c) 2018. Gabriel A. Tolentino
 * Henchman Products PTY.
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

// isTransactionExist($custid,$userid,$tailid,$kitid,$kittoolid)
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






