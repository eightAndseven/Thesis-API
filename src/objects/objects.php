<?php

/**
 * User object has following functions:
 * 1. loginValidate()
 * 2. loginUser()
 * 3. checkPassword()
 * 4. changePassword()
 */
class User{

    //database local variable
    private $conn;
    private $table_name = "user_table";

    public $id;
    public $username;
    public $name;
    public $password;
    public $new_password;
    public $hashed_password;
    /**
     * User Constructor
     */
    public function __construct($db){
        $this->conn = $db;
    }
    /**
     * function to validate username and password
     */
    function loginValidate(){
        if(strlen($this->username) > 4 && strlen($this->username) < 50 && strlen($this->password) > 4 && strlen($this->password) < 50 && ctype_alnum($this->username)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * function to check the user in the db using username and password
     */
    function loginUser(){
        try{
            //sql to get id and username with object username and password
            $sql = "SELECT id, username FROM ".$this->table_name." WHERE username=:username AND password=:password";

            //prepare statement
            $stmt = $this->conn->prepare($sql);
            
            //bind parameters to prepared statement
            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":password", $this->hashed_password);

            //execute prepared statement and return stmt
            $stmt->execute();
            return $stmt;

        }catch(PDOEXCEPTION $e){
            echo $e->getMessage();
        }
    }
    /**
     * function to get user information
     */
    function getUserInfo(){
        try{
            //sql string to get id, username, name with object id
            $sql = "SELECT id, username, name FROM ".$this->table_name." WHERE id=".$this->id;

            //execute statement
            $stmt = $this->conn->query($sql);
            return $stmt;
        }catch(PDOEXCEPTION $e){
            
        }
    }
    /**
     * function to check if password is currently the password
     */
    function checkPassword(){
        try{
            //sql string to get id and username
            $sql = "SELECT id, username, name FROM ".$this->table_name." WHERE id=".$this->id." AND password='".$this->hashed_password."'";
            echo $sql;
            //execute statement
            $stmt = $this->conn->query($sql);
            return $stmt;
        }catch(PDOEXCEPTION $e){

        }
    }

    
    /**
     * function to change password
     */   
    function changePassword(){
        try{
            $sql = "UPDATE ".$this->table_name." SET password=:password WHERE id=:id AND username=:username AND password=:old_pass";

            $stmt = $this->conn->prepare($sql);
            //bind to prepare stmt'
            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":password", $this->new_password);
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":old_pass", $this->hashed_password);

            if($stmt->execute()){
                return true;
            }else{
                return false;
            }
        }catch(PDOEXCEPTION $e){
            return false;
        }
    }
}

/**
 * Activity object has the following functions:
 * 1. getUserActivity()
 */
class Activity{
    //database local variable
    private $conn;
    private $table_name = "activity";
    
    //object properties
    public $id;
    public $user_activity;
    public $user_id;
    public $user_username;
    public $date_time;

    /**
     * Activity Constructor
     */
    public function __construct($db){
        $this->conn = $db;
    }

    /**
     * function to get user activity without pagination
     */
    function getUserActivity(){
        try{
            //sql to get id, user_activity, user_id, user_username, date_time FROM ACTIVITY
            $sql = "SELECT id, user_activity, user_id, user_username, date_time FROM ".$this->table_name." ORDER BY date_time DESC";
            
            //query sql and return statement
            $stmt = $this->conn->query($sql);
            return $stmt;
        }catch(Exception $e){
            
        }
    }

    /**
     * function to save user activity
     */
    function saveActivity(){
        //query insert record
        $query = "INSERT INTO ".$this->table_name." SET user_activity=:user_activity, user_id=:user_id, user_username=:user_username, date_time=:date_time";

        //prepare query
        $stmt = $this->conn->prepare($query);

        //sanitize
        $this->user_activity=htmlspecialchars(strip_tags($this->user_activity));
        $this->user_id=htmlspecialchars(strip_tags($this->user_id));
        $this->user_username=htmlspecialchars(strip_tags($this->user_username));
        $this->date_time=htmlspecialchars(strip_tags($this->date_time));

        //bind to prepare stmt
        $stmt->bindParam(":user_activity", $this->user_activity);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":user_username", $this->user_username);
        $stmt->bindParam(":date_time", $this->date_time);

        try{
            //execute stmt
            if($stmt->execute()){
                return true;
            }else{
                return false;
            }
        }catch(Exception $e){
            return false;
        }
    }
}

/**
 * DailyConsumed object has the following functions: 
 * 1. getDailyConsumed()
 */
class DailyConsumed{
    //database local variable
    private $conn;
    private $table_name = "power_daily";

    //object properties
    public $id;
    public $socket_id;
    public $watt_cons;
    public $date;

    /**
     * DailyConsumed Constructor
     */
    public function __construct($db){
        $this->conn = $db;
    }

    /**
     * function to get power consumed of $socket_id
     */
    function getDailyConsumed(){
        try{
            
            //sql to get id, user_activity, user_id, user_username, date_time FROM ACTIVITY
            //https://stackoverflow.com/questions/12125904/select-last-n-rows-from-mysql
            $sql = "SELECT id, socket_id, watt_cons, date FROM ".$this->table_name." WHERE socket_id=".$this->socket_id." ORDER BY date DESC";
            
            //query sql and return statement
            $stmt = $this->conn->query($sql);
            return $stmt;
        }catch(Exception $e){
            return null;
        }
    }

    /**
     * function to get 7 days for daily graph
     */
    function getDailyGraph(){
        try{

            ///sql to get last 7 days from power_daily asc
            $sql = "SELECT id, socket_id, watt_cons, date FROM (SELECT * FROM ".$this->table_name." WHERE socket_id=".$this->socket_id." ORDER BY date DESC limit 7) sub ORDER BY date ASC";
            
            //query sql and return statement
            $stmt = $this->conn->query($sql);
            return $stmt;
        }catch(Exception $e){
            return null;
        }
    }
}

/**
 * WeeklyConsumed object has the following functions: 
 * 1. getWeeklyConsumed()
 */
class WeeklyConsumed{
    //database local variable
    private $conn;
    private $table_name = "power_weekly";

    //object properties
    public $id;
    public $socket_id;
    public $watt_cons;
    public $date_from;
    public $date_to;
    public $week_number;

    /**
     * WeeklyConsumed Constructor
     */
    public function __construct($db){
        $this->conn = $db;
    }

    /**
     * function to get power consumed of $socket_id
     */
    function getWeeklyConsumed(){
        try{
            
            //sql to get id, user_activity, user_id, user_username, date_time FROM ACTIVITY
            $sql = "SELECT id, socket_id, watt_cons, date_from, date_to, week_number FROM ".$this->table_name." WHERE socket_id=".$this->socket_id." ORDER BY week_number DESC";
            
            //query sql and return statement
            $stmt = $this->conn->query($sql);

            return $stmt;
        }catch(Exception $e){
            return null;
        }
    }

    /**
     * function to get 8 days for daily graph
     */
    function getWeeklyGraph(){
        try{

            ///sql to get last 7 days from power_daily asc
            $sql = "SELECT id, socket_id, watt_cons, date_from, date_to FROM (SELECT * FROM ".$this->table_name." WHERE socket_id=".$this->socket_id." ORDER BY week_number DESC limit 7) sub ORDER BY week_number ASC";
            
            //query sql and return statement
            $stmt = $this->conn->query($sql);
            return $stmt;
        }catch(Exception $e){
            return null;
        }
    }
}


/**
 * Socket object has the following functions: 
 * 1. readSocketInfo(),
 * 2. stateOnOffSocket(),
 * 3. socketOnOff()
 */
class Socket{
    //database local variable
    private $conn;
    private $table_name = "socket";

    //object properties
    public $id;
    public $socket_id;
    public $socket_name;
    public $socket_description;
    public $socket_pin;
    public $socket_read;
    public $socket_switch;
    public $brightness;

    /**
     * Socket Constructor
     */
    public function __construct($db){
        $this->conn = $db;
    }

    /**
     * private variables for socket
     */
    private $socket = array(1, 4, 5, 7 ,0 , 2);

    /**
     * function to read a single socket info
     */
    function readSocketInfo(){
        $pin = $this->socket_id;
        $status;
        switch($pin){
            case "1":
                exec("gpio read ".$this->socket[0], $gpiostatus);
                // $gpiostatus = '0';
                $status = $gpiostatus;
                break;
            case "2":
                exec("gpio read ".$this->socket[1], $gpiostatus);
                // $gpiostatus = '1';
                $status = $gpiostatus;
                break;
            case "3":
                exec("gpio read ".$this->socket[2], $gpiostatus);
                // $gpiostatus = '1';
                $status = $gpiostatus;
                break;
            case "4":
                exec("gpio read ".$this->socket[3], $gpiostatus);
                // $gpiostatus = '0';
                $status = $gpiostatus;
                break;
            case "5":
                exec("gpio read ".$this->socket[4], $gpiostatus);
                // $gpiostatus = '0';
                $status = $gpiostatus;
                break;
            case "6":
                exec("gpio read ".$this->socket[5], $gpiostatus);
                // $gpiostatus = '0';
                $status = $gpiostatus;
                break;
            default:
                $status = -1;
        }
        if($status[0] == "1"){
            return "off";
        }elseif($status[0] == "0"){
            return "on";
        }else{
            return "ERROR";
        }
    }

    /**
     * function to read state to on/off
     */
    function stateOnOffSocket(){
        $state = $this->socket_switch;
        $pin = $this->socket_id;
        $status;
        switch($pin){
            case "1":
                $msg = $this->socketOnOff($pin, $this->socket[0], $state);
                $status = $msg;
                break;
            case "2":
                $msg = $this->socketOnOff($pin, $this->socket[1], $state);
                $status = $msg;
                break;
            case "3":
                $msg = $this->socketOnOff($pin, $this->socket[2], $state);
                $status = $msg;
                break;
            case "4":
                $msg = $this->socketOnOff($pin, $this->socket[3], $state);
                $status = $msg;
                break;
            case "5":
                $msg = $this->socketOnOff($pin, $this->socket[4], $state);
                $status = $msg;
                break;
            case "6":
                $msg = $this->socketOnOff($pin, $this->socket[5], $state);
                $status = $msg;
                break;
            default:
                $status = "ERROR";
        }
        return $status;
    }
    /**
     * function to turn on/off the socket
     */
    function socketOnOff($socket, $pin, $state){
        $status;
        if($state == "on"){
            system("gpio mode ".$pin." out");
            system("gpio write ". $pin . " 0");
            $status = "Turned on socket " .$socket;
        }else{
            system("gpio mode ".$pin." out");
            system("gpio write ". $pin . " 1");
            $status = "Turned off socket " .$socket;
        }
        return $status;
    }
    /**
     * function to change brightness of dimmer
     */
    function changeBrightness(){
        $python_exec = "python ~/Documents/pythonscripts/test/test_lights.py ". $this->brightness;
        system($python_exec);
        $status = "Change brightness of dimmer at ".$this->brightness."%";
        return $status;
    }
}

/**
 * Schedule object has the following functions: 
 */
class Schedule{
    //database properties
    private $conn;
    private $table_name = "schedule";

    //object properties
    public $id;
    public $socket_id;
    public $user_id;
    public $user_username;
    public $date_time_posted;
    public $date_time_sched;
    public $date_time_now;
    public $action;
    public $state;
    public $description;

    /**
     * Schedule Constructor
     */
    public function __construct($db){
        $this->conn = $db;
    }

    /**
     * function to save schedule
     */
    function scheduleSocket(){
        //initialize vars
        $this->state = "READY";
        $this->description = "Socket ".$this->socket_id." to be turned ".$this->action." at ".$this->date_time_sched;

        //query insert record
        $query = "INSERT INTO ".$this->table_name." SET socket_id=:socket_id, user_id=:user_id, user_username=:user_username, date_time_posted=:post, date_time_sched=:sched, action=:action, state=:state, description=:description";

        //prepare query
        $stmt = $this->conn->prepare($query);

        //sanitize
        $this->socket_id=htmlspecialchars(strip_tags($this->socket_id));
        $this->user_id=htmlspecialchars(strip_tags($this->user_id));
        $this->user_username=htmlspecialchars(strip_tags($this->user_username));
        $this->date_time_posted=htmlspecialchars(strip_tags($this->date_time_posted));
        $this->date_time_sched=htmlspecialchars(strip_tags($this->date_time_sched));
        $this->action=htmlspecialchars(strip_tags($this->action));
        $this->state=htmlspecialchars(strip_tags($this->state));
        $this->description=htmlspecialchars(strip_tags($this->description));

        //bind to prepare stmt
        $stmt->bindParam(":socket_id", $this->socket_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":user_username", $this->user_username);
        $stmt->bindParam(":post", $this->date_time_posted);
        $stmt->bindParam(":sched", $this->date_time_sched);
        $stmt->bindParam(":action", $this->action);
        $stmt->bindParam(":state", $this->state);
        $stmt->bindParam(":description", $this->description);
        try{
            //execute stmt
            if($stmt->execute()){
                $return_arr = array(
                    "success"=>true,
                    "description"=>$this->description
                );
                return $return_arr;
            }else{
                $return_arr = array(
                    "success"=>false,
                    "description"=>"ERROR"
                );
                return $return_arr;
            }
        }catch(PDOException $e){
            $return_arr = array(
                "success"=>false,
                "description"=>"Bad SQL Request"
            );
            return $return_arr;
        }
    }
    /**
     * function to check if socket has schedule
     */
    function getSocketSchedule(){
        try{
            
            //sql to get socket_id, date_time_sched, action, user_username from schedule
            $sql = "SELECT id, socket_id, date_time_sched, action, user_username FROM ".$this->table_name." WHERE socket_id=".$this->socket_id." AND date_time_sched>'".$this->date_time_now."' AND state='READY'";
            
            //query sql and return statement
            $stmt = $this->conn->query($sql);

            return $stmt;
        }catch(Exception $e){
            return -1;
        }
    }
    /**
     * function to delete a scheduled process
     */
    function deleteSocketSchedule(){
        try{

            //sql to delete a schedule job
            $sql = "DELETE FROM ".$this->table_name." WHERE id=:id AND socket_id=:socket_id AND user_id=:user_id AND user_username=:user_username";

            //prepare query
            $stmt = $this->conn->prepare($sql);

            //sanitize
            $this->id=htmlspecialchars(strip_tags($this->id));
            $this->socket_id=htmlspecialchars(strip_tags($this->socket_id));
            $this->user_id=htmlspecialchars(strip_tags($this->user_id));
            $this->user_username=htmlspecialchars(strip_tags($this->user_username));

            //bind to prepare stmt
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":socket_id", $this->socket_id);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":user_username", $this->user_username);

            //execute sql
            if($stmt->execute()){
                return true;
            }else{
                return false;
            }
        }catch(Exception $e){

        }
    }
}

