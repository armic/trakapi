<?php
/**
 * Created by PhpStorm.
 * User: artolentino
 * Date: 2/12/18
 * Time: 10:31 AM
 */
class clstrak{

    public function isUserExist($custid,$userid) {

        $sql = "SELECT\n".
            "users.id,\n".
            "users.custid,\n".
            "users.active,\n".
            "users.kabtrak,\n".
            "users.portatrak,\n".
            "users.cribtrak,\n".
            "users.auditrak,\n".
            "users.role,\n".
            "users.userid\n".
            "FROM\n".
            "users\n".
            "WHERE\n".
            "users.auditrak = 1 AND\n".
            "users.custid = $custid AND\n".
            "users.userid = '$userid'";

        $user = null;

        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmtuser = $db->query($sql);
            $user = $stmtuser->fetchAll(PDO::FETCH_OBJ);

            if($user) {

                return true;

            } else {
                return false;
            }


        }catch(PDOException $e){
            return false;

        }
    }

    public function isUserGranted($custid,$userid) {

        $sql = "SELECT\n".
            "users.id,\n".
            "users.custid,\n".
            "users.active,\n".
            "users.kabtrak,\n".
            "users.portatrak,\n".
            "users.cribtrak,\n".
            "users.auditrak,\n".
            "users.role,\n".
            "users.userid\n".
            "FROM\n".
            "users\n".
            "WHERE\n".
            "users.auditrak = 1 AND\n".
            "users.custid = $custid AND\n".
            "users.userid = '$userid'";

        $user = null;

        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();

            foreach($db->query($sql, PDO::FETCH_ASSOC) as $row){

                if($row['active'] == 0 or $row['active'] == null) {


                    return false;
                } else {

                    return true;
                }

            }


        }catch(PDOException $e){
            return false;

        }
    }

    public function isEmployeeEmailExist($email) {

        $sql = "SELECT email\n".
               "FROM\n".
               "employees\n".
               "WHERE\n".
               "employees.email = '$email'";

        $employees = null;

        try{
            // Get DB object
            $db= new db();
            $db = $db->connect();
            $stmtuser = $db->query($sql);
            $employees = $stmtuser->fetchAll(PDO::FETCH_OBJ);

            if($employees) {

                return true;

            } else {
                return false;
            }


        }catch(PDOException $e){
            return false;

        }
    }




}