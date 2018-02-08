<?php

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

