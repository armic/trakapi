<?php
/**
 * Copyright (c) 2018. Gabriel A. Tolentino
 * Henchman Products PTY.
 */


class clstrak
{

    public function isUserExist($custid, $userid)
    {

        $sql = "SELECT\n" .
            "users.id,\n" .
            "users.custid,\n" .
            "users.active,\n" .
            "users.kabtrak,\n" .
            "users.portatrak,\n" .
            "users.cribtrak,\n" .
            "users.auditrak,\n" .
            "users.role,\n" .
            "users.userid\n" .
            "FROM\n" .
            "users\n" .
            "WHERE\n" .
            "users.custid = $custid AND\n" .
            "users.userid = '$userid'";

        $user = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmtuser = $db->query($sql);
            $user = $stmtuser->fetchAll(PDO::FETCH_OBJ);

            if ($user) {

                return true;

            } else {
                return false;
            }


        } catch (PDOException $e) {
            return false;

        }
    }

    public function isUserGranted($custid, $userid)
    {

        $sql = "SELECT\n" .
            "users.id,\n" .
            "users.custid,\n" .
            "users.active,\n" .
            "users.auditrak,\n" .
            "users.role,\n" .
            "users.userid\n" .
            "FROM\n" .
            "users\n" .
            "WHERE\n" .
            "users.custid = $custid AND\n" .
            "users.auditrak = 1 AND\n".
            "users.userid = '$userid'";

        $user = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();

            foreach ($db->query($sql, PDO::FETCH_ASSOC) as $row) {

                if ($row['active'] == REVOKED or $row['active'] == null) {


                    return false;
                } else {

                    return true;
                }

            }


        } catch (PDOException $e) {
            return false;

        }
    }

    public function isEmployeeEmailExist($email)
    {

        $sql = "SELECT email\n" .
            "FROM\n" .
            "employees\n" .
            "WHERE\n" .
            "employees.email = '$email'";

        $employees = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $employees = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($employees) {

                return true;

            } else {
                return false;
            }


        } catch (PDOException $e) {
            return false;

        }
    }

    // Check if the username being register is unique
    //
    public function isUsernameExist($username)
    {

        $sql = "SELECT username\n" .
            "FROM\n" .
            "employees\n" .
            "WHERE\n" .
            "employees.username = '$username'";

        $employees = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmtuser = $db->query($sql);
            $employees = $stmtuser->fetchAll(PDO::FETCH_OBJ);

            if ($employees) {

                return true;

            } else {
                return false;
            }


        } catch (PDOException $e) {
            return false;

        }
    }

   // Check if Tail number being added is existing. No tail number should be identical

    public function isTailNumberExist($tailnumber, $custid)
    {

        $sql = "SELECT number\n" .
            "FROM\n" .
            "tails\n" .
            "WHERE\n" .
            "tails.custid = $custid\n" .
            "AND tails.number = '$tailnumber'";

        $tails = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $tails = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($tails) {

                return true;

            } else {
                return false;
            }


        } catch (PDOException $e) {
            return false;

        }
    }



public function isTransactionExist($custid,$userid,$tailid,$kitid,$kittoolid,$flag) {

    $sql = "SELECT\n".
        "audittraktransactions.custid,\n".
        "audittraktransactions.type,\n".
        "audittraktransactions.datetimeissued,\n".
        "audittraktransactions.userid,\n".
        "audittraktransactions.tailid,\n".
        "audittraktransactions.lockerid,\n".
        "audittraktransactions.kitid,\n".
        "audittraktransactions.datereturned,\n".
        "audittraktransactions.kittoolid,\n".
        "audittraktransactions.workorder\n".
        "FROM\n".
        "audittraktransactions\n".
        "WHERE\n".
        "audittraktransactions.custid = $custid AND\n".
        "audittraktransactions.userid = $userid AND\n".
        "audittraktransactions.tailid = $tailid";

   if($flag == KIT) {
       $sql = $sql. " AND audittraktransactions.kitid = $kitid";
   }else{
       $sql = $sql. " AND audittraktransactions.kittoolid = $kittoolid";
   }


    $transactions = null;

    try {
        // Get DB object
        $db = new db();
        $db = $db->connect();

        foreach ($db->query($sql, PDO::FETCH_ASSOC) as $transactions) {

            if ($transactions['type'] == ISSUE ) {

                // Transaction exist

                return true;
            } else {
               // No , go on
                return false;
            }

        }


    } catch (PDOException $e) {
        return false;

    }


}


public  function updateToolStatus($custid, $toolid,$status){

         $sql =  "UPDATE kittools SET  status = $status  WHERE id = $toolid AND custid = $custid";

         //$status 0 - IN 1 - OUT

        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);


            $stmt->execute();
            $db = null;
           // echo '{"notice": {"text": "Tool status updated"}';

        }catch(PDOException $e){
            echo '{"error": {"text": '.$e->getMessage().'}';

        }
    }

public  function updateKitStatus($custid, $kitid, $status ){

    //$status 0 - IN 1 - OUT

        $sql =  "UPDATE kits SET  status = $status  WHERE id = $kitid AND custid = $custid";

        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);


            $stmt->execute();
            $db = null;
           // echo '{"notice": {"text": "Kit status updated"}';

        }catch(PDOException $e){
            echo '{"error": {"text": '.$e->getMessage().'}';

        }

    }

}