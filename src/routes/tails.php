<?php
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
        "tails.description,\n".
        "tails.username,\n".
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

