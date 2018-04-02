<?php
date_default_timezone_set('Asia/Manila');
class Database{
    private $host = 'localhost';
    private $user = 'root';
    private $psswd = '';
    private $dbname = 'powerboard';

    public function connectDB(){
        $mysql_string = "mysql:host=$this->host; dbname=$this->dbname";
        $dbConnection = new PDO($mysql_string, $this->user, $this->psswd );
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection;
    }
}