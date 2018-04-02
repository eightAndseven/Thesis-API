<?php
class Hasher{
    //salt key for HASHING using MD5
    private $asalt_key = '$1$P0w3rbo@$';
    private $bhash_key;
    
    /**
     * constructor for hasher
     */
    public function __construct($pass){
        $this->bhash_key = $pass;
    }
    /**
     * function to hash password
     */
    function HashPassword(){
        return crypt($this->bhash_key, $this->asalt_key);
    }
}