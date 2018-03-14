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
 * HAS NO OBLIGATION TO PROVIDE MAINTENANCE, SUPPORT, UPDATES, ENHANCEMENTS, OR MODIFICATIONS.
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
            "users.auditrak = 1 AND\n" .
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
            "users.auditrak = 1 AND\n" .
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


    public function isTransactionExist($custid, $userid, $tailid, $kitid, $kittoolid, $flag)
    {

        $sql = "SELECT\n" .
            "audittraktransactions.custid,\n" .
            "audittraktransactions.type,\n" .
            "audittraktransactions.datetimeissued,\n" .
            "audittraktransactions.userid,\n" .
            "audittraktransactions.tailid,\n" .
            "audittraktransactions.lockerid,\n" .
            "audittraktransactions.kitid,\n" .
            "audittraktransactions.datereturned,\n" .
            "audittraktransactions.kittoolid,\n" .
            "audittraktransactions.workorder\n" .
            "FROM\n" .
            "audittraktransactions\n" .
            "WHERE\n" .
            "audittraktransactions.custid = $custid AND\n" .
            "audittraktransactions.userid = $userid AND\n" .
            "audittraktransactions.tailid = $tailid";

        if ($flag == KIT) {
            $sql = $sql . " AND audittraktransactions.kitid = $kitid";
        } else {
            $sql = $sql . " AND audittraktransactions.kittoolid = $kittoolid";
        }


        $transactions = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();

            foreach ($db->query($sql, PDO::FETCH_ASSOC) as $transactions) {

                if ($transactions['type'] == ISSUE) {

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

    public function isKiToolExist($custid, $code)
    {

        $sql = "SELECT\n" .
            "kittools.id,\n" .
            "kittools.kitid,\n" .
            "kittools.toolid,\n" .
            "kittools.custid,\n" .
            "kittools.reserved,\n" .
            "kittools.qrcode,\n" .
            "kittools.`status`\n" .
            "FROM\n" .
            "kittools\n" .
            "WHERE\n" .
            "kittools.custid = $custid AND\n" .
            "kittools.qrcode = $code";


        $kittool = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $kittool = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($kittool) {

                return true;

            } else {
                return false;
            }


        } catch (PDOException $e) {
            return false;

        }
    }


    public function updateToolStatus($custid, $toolid, $status)
    {

        $sql = "UPDATE kittools SET  status = $status  WHERE id = $toolid AND custid = $custid";

        //$status 0 - IN 1 - OUT

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);


            $stmt->execute();
            $db = null;
            // echo '{"notice": {"text": "Tool status updated"}';

        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}';

        }
    }


    public function updateToolReservedStatus($custid, $toolid, $status)
    {

        $sql = "UPDATE kittools SET  reserved = $status  WHERE id = $toolid AND custid = $custid";

        //$status 0 - IN 1 - OUT

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);


            $stmt->execute();
            $db = null;
            // echo '{"notice": {"text": "Tool reservation status updated"}';

        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}';

        }
    }


    public function updateKitReservedStatus($custid, $kitid, $status)
    {

        $sql = "UPDATE kits SET  reserved = $status  WHERE id = $kitid AND custid = $custid";

        //$status 0 - Available 1 - Reserved

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);


            $stmt->execute();
            $db = null;
            // echo '{"notice": {"text": "Kit reservation  status updated"}';

        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}';

        }
    }

    public function updateKitStatus($custid, $kitid, $status)
    {

        //$status 0 - IN 1 - OUT

        $sql = "UPDATE kits SET  status = $status  WHERE id = $kitid AND custid = $custid";

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);


            $stmt->execute();
            $db = null;
            // echo '{"notice": {"text": "Kit status updated"}';

        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}';

        }

    }


    public function isKitReserved($custid, $kitid)
    {

        $sql = "SELECT *\n" .
            "FROM\n" .
            "reservations\n" .
            "WHERE\n" .
            "reservations.custid = $custid\n" .
            "AND reservations.kitid = $kitid";

        $reservations = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $reservations = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($reservations) {
                return true;
                $reservations = null;
                $db = null;
            } else {

                return false;
            }


        } catch (PDOException $e) {
            echo '{"error": {"Message": ' . $e->getMessage() . '}';

        }

    }

    public function isToolReserved($custid, $toolid)
    {

        $sql = "SELECT *\n" .
            "FROM\n" .
            "reservations\n" .
            "WHERE\n" .
            "reservations.custid = $custid\n" .
            "AND reservations.toolid = $toolid";

        $reservations = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $reservations = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($reservations) {
                return true;
                $reservations = null;
                $db = null;
            } else {

                return false;
            }


        } catch (PDOException $e) {
            echo '{"error": {"Message": ' . $e->getMessage() . '}';

        }

    }


    public function isTailTransaction($custid, $number)
    {

        $sql = "SELECT *\n" .
            "FROM\n" .
            "auditraktransactions\n" .
            "WHERE\n" .
            "tailid = $number\n" .
            "AND custid = $custid";

        $tailtransactions = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $tailtransactions = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($tailtransactions) {
                return true;
                $tailtransactions = null;
                $db = null;
            } else {

                return false;
            }


        } catch (PDOException $e) {
            echo '{"error": {"Message": ' . $e->getMessage() . '}';

        }

    }

    public function DisableTail($custid, $number)
    {

        //$status 0 - IN 1 - OUT

        $sql = "UPDATE tails SET  active = 0  WHERE custid = $custid AND number = $number";

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);


            $stmt->execute();
            $db = null;
            // echo '{"notice": {"text": "Tail disabled"}';

        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}';

        }

    }

    public function EnableTail($custid, $number)
    {

        //$status 0 - IN 1 - OUT

        $sql = "UPDATE tails SET  active = 1  WHERE custid = $custid AND number = $number";

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->prepare($sql);


            $stmt->execute();
            $db = null;
            // echo '{"notice": {"text": "Tail enabled"}';

        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}';

        }

    }


    public function isLocationExist($custid, $description)
    {

        $sql = "SELECT\n" .
            "locations.id,\n" .
            "loctions.description,\n" .
            "locations.custid\n" .
            "FROM\n" .
            "locations\n" .
            "WHERE\n" .
            "locations.custid = $custid AND\n" .
            "locations.description = $description";


        $locations = null;

        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();
            $stmt = $db->query($sql);
            $locations = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($locations) {

                return true;

            } else {
                return false;
            }


        } catch (PDOException $e) {
            return false;

        }
    }


    public function isKitComplete($custid, $kitid)
    {

        $sql = "SELECT\n" .
            "kits.id,\n" .
            "kits.count,\n" .
            "FROM\n" .
            "kits\n" .
            "WHERE\n" .
            "kits.custid = $custid AND\n" .
            "kits.id = $kitid";

        $sql_tool = "SELECT\n" .
            "kittools.id,\n" .
            "kittoolss.status,\n" .
            "kittoolss.custid,\n" .
            "kittoolss.kitid,\n" .
            "FROM\n" .
            "kittools\n" .
            "WHERE\n" .
            "kittools.custid = $custid AND\n" .
            "kittools.kitid = $kitid AND\n" .
            "kittools.status = 1";

        $kits = null;
        $kittools = null;
        $kittoolcount = null;
        $tool_available_count = null;


        try {
            // Get DB object
            $db = new db();
            $db = $db->connect();

            foreach ($db->query($sql, PDO::FETCH_ASSOC) as $row) {

                $kittoolcount = $row[count];
            }


            $stmt = $db->query($sql_tool);
            $kittools = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($kittools) {
                $tool_available_count = $stmt->rowCount();
                $kittools = null;
                $db = null;
            } else {

                $tool_available_count = 0;
            }


            if ($kittoolcount == $tool_available_count) {

                return true;
            } else {

                return false;
            }


        } catch (PDOException $e) {
            return false;

        }
    }

}