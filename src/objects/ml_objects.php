<?php

class ApplianceSpecific{
    private $conn;
    private $table_name = "appliance_specific";

    public $id;
    public $name;
    public $category_id;

    public function __construct($db){
        $this->conn = $db;
    }

    function getAppSpecific(){
        try{
            $sql = "SELECT id, name, category_id FROM ".$this->table_name." WHERE category_id=".$this->category_id."";
            $stmt = $this->conn->query($sql);
            return $stmt;
        }catch(PDOEXCEPTION $e){
            return $e->getMessage();
        }
    }

    function addAppSpecific(){
        try{
            $sql = "INSERT INTO $this->table_name SET name=:name, category_id=:category_id";

            $stmt = $this->conn->prepare($sql);
            
            $this->name = htmlspecialchars(strip_tags($this->name));
            // $this->category_id = htmlspecialchars(strip_tags($this->category_id));

            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":category_id", $this->category_id);

            if($stmt->execute()){
                return true;
            }else{
                return false;
            }
        }catch(PDOEXCEPTION $e){
            echo $e;
        }
    }

}

class ApplianceCategory{
    private $conn;
    private $table_name = "appliance_category";

    public $id;
    public $name;

    public function __construct($db){
        $this->conn = $db;
    }

    function getCategory(){
        try{
            $sql = "SELECT id, name FROM $this->table_name";

            $stmt = $this->conn->query($sql);
            return $stmt;

        }catch(PDOEXCEPTION $e){
            return $e->getMessage();
        }
    }
    function addCategory(){
        try{
            $sql = "INSERT INTO ".$this->table_name." SET name=:name";
            
            // return $sql;
            $stmt = $this->conn->prepare($sql);

            $this->name = htmlspecialchars(strip_tags($this->name));

            $stmt->bindParam(":name", $this->name);

            if($stmt->execute()){
                return true;
            }else{
                return false;
            }
        }catch(PDOEXCEPTION $e){
            return $e->getMessage();
        }
    }
}