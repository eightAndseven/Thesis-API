<?php
class Hasher{
    //salt key for HASHING using MD5
    private $asalt_key = '$1$P0w3rbo@$';
    private $bhash_key;
    private $nhash_key;
    
    /**
     * constructor for hasher
     */
    // public function __construct($pass){
    //     $this->bhash_key = $pass;
    // }

    /**
     * Setters
     */
    public function setPassword($password){
        $this->bhash_key = $password;
        return $this->bhash_key;
    }
    public function setNewPassword($passworda){
        $this->nhash_key = $passworda;
        return $this->nhash_key;
    }

    /**
     * function to hash password
     */
    function HashPassword(){
        return crypt($this->bhash_key, $this->asalt_key);
    }

    /**
     * function to has both password
     */
    function HashtwoPassword(){
        return array(
            "old_password"=>crypt($this->bhash_key, $this->asalt_key),
            "new_password"=>crypt($this->nhash_key, $this->asalt_key)
        );
    }
}