<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer{
    private $conn;
    private $table_name = 'forgot_table';

    private $email_username = 'powerboard.lightsandsocket@gmail.com';
    private $email_password = '$0ck3Ts@ndL1gh+$';

    public $user_id;
    public $user_name;
    public $user_username;
    public $user_email;
    
    public $id;
    public $first_token;
    public $second_token;
    public $time_expire;


    public function __construct($db){
        $this->conn = $db;
    }

    /**
     * function to delete the row
     */
    function deleteToken(){
        try{
            $sql = "DELETE FROM $this->table_name WHERE id=:id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam('id', $this->id);
            if($stmt->execute()){
                return true;
            }else{
                return false;
            }
        }catch(PDOEXCEPTION $e){
            return false;
        }
    }

    /**
     * function to check if there is a second token
     */
    function getSecondToken(){
        try{
            $sql = "SELECT id, user_id FROM $this->table_name WHERE second_token=:second_token";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':second_token', $this->second_token);
            $stmt->execute();
            return $stmt;
        }catch(PDOEXCEPTION $e){
            return "ERROR";
        }
    }

    /**
     * function to get if first token is in the database
     */
    function getFirstToken(){
        try{
            $sql = "SELECT id, first_token, second_token, time_expire, user_id FROM $this->table_name WHERE first_token=:first_token";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':first_token', $this->first_token);
            $stmt->execute();
            return $stmt;
        }catch(PDOEXCEPTION $e){
            return "ERROR";
        }
    }

    /**
     * function to save new token to database
     */
    function saveForgot(){
        try{
            $sql = "INSERT INTO $this->table_name SET first_token=:first_token, second_token=:second_token, time_expire=:time_expire, user_id=:user_id";
            $stmt = $this->conn->prepare($sql);
                    //sanitize
            $this->first_token=htmlspecialchars(strip_tags($this->first_token));
            $this->second_token=htmlspecialchars(strip_tags($this->second_token));
            $this->time_expire=htmlspecialchars(strip_tags($this->time_expire));
            $this->user_id=htmlspecialchars(strip_tags($this->user_id));

            //bind to prepare stmt
            $stmt->bindParam(":first_token", $this->first_token);
            $stmt->bindParam(":second_token", $this->second_token);
            $stmt->bindParam(":time_expire", $this->time_expire);
            $stmt->bindParam(":user_id", $this->user_id);

            if($stmt->execute()){
                return true;
            }else{
                return false;
            }

        }catch(PDOEXCEPTION $e){
            return false;
        }
    }

    /**
     * function to send an email to the user for forgot password
     */
    function sendForgotToken(){
        $message =  "Hi <b>$this->user_name ($this->user_username)</b><br><br>".
                    "It seems that you forgot your password, below is the code to confirm your identity.<br>".
                    "Code: ".$this->first_token."<br>".
                    "Please copy the code to proceed and change your password.<br><br>".
                    "If you received this email without the want to change your password, please disregard this email.<br><br>".
                    "Thank you!<br><br>".
                    "The code will expire in $this->time_expire";

        $mail = new PHPMailer(true);
        try{
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Mailer = 'smtp';
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->SMTPAuth = true;
            $mail->Username = $this->email_username;
            $mail->Password = $this->email_password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = '587';
            $mail->setFrom('no-reply@powerboard.tk', 'Lights and Sockets');
            $mail->addAddress($this->user_email);
            $mail->isHTML(true);
            $mail->Subject = 'Change Password';
            $mail->Body = $message;
            $mail->send();
            return true;
        }catch(Exception $e){
            return false;
        }
    }

    /**
     * function to test if email is working.
     */
    function testMail(){
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Mailer = 'smtp';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->SMTPAuth = true;
        $mail->Username = $this->email_username;
        $mail->Password = $this->email_password;
        $mail->SMTPSecure = 'tls';
        $mail->Port = '587';
        
        $mail->setFrom('no-reply@powerboard.tk', 'Lights and Sockets');
        $mail->addAddress('reinyear@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'Subject of the email';
        $mail->Body = 'This is the HTML message body in <b>in bold!</b>.';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        return 'Message has been sent';
    }

    // https://stackoverflow.com/questions/48124/generating-pseudorandom-alpha-numeric-strings
    function randomToken($len){
        $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));
        $key = '';
        for($i=0; $i < $len; $i++) {
            $key .= $pool[mt_rand(0, count($pool) - 1)];
        }
        return $key;
    }
}