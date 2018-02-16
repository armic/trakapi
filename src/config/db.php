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

class db{
      //Properties
      /*private  $dbhost = 'localhost';
      private  $dbuser = 'henchman_root';
      private  $dbpass = 'ysaac121406';
      private  $dbname = 'henchman_trak_qt';*/

      private  $dbhost = 'localhost';
      private  $dbuser = 'root';
      private  $dbpass = '';
      private  $dbname = 'AuditTRAK';

      //Connect
      public function connect() {
          $mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname;";
          $dbConnection = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass,array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
          $dbConnection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
          return $dbConnection;
      }

  }

