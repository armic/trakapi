<?php
/**
 * Copyright (c) 2018. Gabriel A. Tolentino
 * Henchman Products PTY.
 */

/**
 * Created by PhpStorm.
 * User: artolentino
 * Date: 2/14/18
 * Time: 8:35 AM
 */

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

// Get tail list

$app->get('/api/tails/{custid}', function(Request $request, Response $response) {

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


// Add new tail

$app->post('/api/tail/add/{custid}', function(Request $request, Response $response) {

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

$app->put('/api/tail/update/{number}/{custid}', function(Request $request, Response $response) {

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

