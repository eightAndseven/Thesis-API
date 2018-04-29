<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * route for test mail
 */
$app->get('/api/powerboard/test_mail', function($request, $response){
    $mailer = new Mailer();
    echo $mailer->testMail();
}); 

/**
 * route to authenticate email for change password
 */
$app->post('/api/powerboard/forgot_password', function($request, $response){
    $email = $request->getParam("email");
    $date_time = date('Y-m-d H:i:s');

    $db = new Database();
    $db = $db->connectDB();

    $user = new User($db);
    $user->email = $email;
    $stmt = $user->getEmailUserInfo();
    $count = $stmt->rowCount();
    if($count == 1){
        $row = $stmt->fetch();
        $mail = new Mailer($db);
        $mail->user_id = $row['id'];
        $mail->user_username = $row['username'];
        $mail->user_name = $row['name'];
        $mail->user_email = $row['email'];
        $mail->first_token = $mail->randomToken(6);
        $mail->second_token = $mail->randomToken(16);
        $mail->time_expire = date('Y-m-d H:i:s', time() + 600);
        if($mail->sendForgotToken()){
            if($mail->saveForgot()){
                $forgot_array['forgot_password'] = array(
                    "success" => true,
                    "username" => $mail->user_username,
                    "time_expire" => $mail->time_expire
                );
                $message_array['response'] = array(
                    "success" => true,
                    "date_time" => $date_time,
                    "message" => "Email sent!"
                );
                return $response->withHeader('Content-Type', 'application/json')
                ->write(json_encode(array_merge($message_array, $forgot_array)));
            }else{
                $message_array['response'] = array(
                    "success" => false,
                    "date_time" => $date_time,
                    "message" => "Something occurred, Please continue"
                );
                return $response->withHeader('Content-Type', 'application/json')
                ->write(json_encode($message_array));
            }
        }else{
            $message_array['response'] = array(
                "success" => false,
                "date_time" => $date_time,
                "message" => "Something occurred, please try again!"
            );
            return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($message_array));
        }
        // echo $mail->sendForgotToken();

    }else{
        $message_array['response'] = array(
            "success" => false,
            "date_time" => $date_time,
            "message" => "Incorrect Email"
        );
        return $response->withHeader('Content-Type', 'application/json')
        ->write(json_encode($message_array));
    }
});

/**
 * route to confirm the token sent to the email of the user
 */
$app->post('/api/powerboard/confirm_token', function($request, $response){
    $token = $request->getParam('token');
    $date_time = date('Y-m-d H:i:s');

    $db = new Database();
    $db = $db->connectDB();

    $mail = new Mailer($db);
    $mail->first_token = $token;
    $mail_stmt = $mail->getFirstToken();
    $count = $mail_stmt->rowCount();
    if($count == 1){
        $row = $mail_stmt->fetch();
        $user_id = $row['user_id'];
        $first_token = $row['first_token'];
        $second_token = $row['second_token'];
        $time_expire = $row['time_expire'];
        $forgot_arr['forgot'] = array(
            "first_token" => $first_token,
            "second_token"=>$second_token,
            "time_expire" => $time_expire
        );
        $message_array['response'] = array(
            "success" => true,
            "date_time" => $date_time,
            "message" => "Token is correct!"
        );
        return $response->withHeader('Content-Type', 'application/json')
        ->write(json_encode(array_merge($message_array, $forgot_arr)));
    }else{
        $message_array['response'] = array(
            "success" => false,
            "date_time" => $date_time,
            "message" => "Token is incorrect!"
        );
        return $response->withHeader('Content-Type', 'application/json')
        ->write(json_encode($message_array));
    }
});

/**
 * route to change password through forgot password
 */
$app->post('/api/powerboard/forgot_change', function($request, $response){
    $second_token = $request->getParam('second_token');
    $new_password = $request->getParam('new_password');
    $con_password = $request->getParam('con_password');
    $date_time = date('Y-m-d H:i:s');

    $db = new Database();
    $db = $db->connectDB();

    $mail = new Mailer($db);
    $mail->second_token = $second_token;
    $stmt_mail = $mail->getSecondToken();
    // echo $stmt_mail;
    $count_mail = $stmt_mail->rowCount();
    echo $count_mail;
    if($count_mail == 1){
        $row_mail = $stmt_mail->fetch();
        $mail->user_id = $row_mail['user_id'];
        $mail->id = $row_mail['id'];
        if($new_password === $con_password && (strlen($new_password) >= 4 && strlen($new_password) < 50 && strlen($con_password) >= 4 && strlen($con_password) < 50)){
            
            $hash = new Hasher();
            $new_password = $hash->setPassword($new_password);
            $new_password = $hash->HashPassword();

            $user = new User($db);
            $user->new_password = $new_password;
            $user->id = $mail->user_id;
            if($user->changePasswordForgot()){
                if($mail->deleteToken()){
                    $forgot_arr['forgot'] = array(
                        "change_password" => true,
                        "message" => "Password Changed"
                    );
                    $message_array['response'] = array(
                        "success" => true,
                        "date_time" => $date_time,
                        "message" => "Password now changed"
                    );
                    return $response->withHeader('Content-Type', 'application/json')
                    ->write(json_encode(array_merge($message_array, $forgot_arr)));
                }else{
                    $forgot_arr['forgot'] = array(
                        "change_password" => true
                    );
                    $message_array['response'] = array(
                        "success" => false,
                        "date_time" => $date_time,
                        "message" => "Something occurred, Please continue now"
                    );
                    return $response->withHeader('Content-Type', 'application/json')
                    ->write(json_encode(array_merge($message_array, $forgot_arr)));
                }
            }else{
                $message_array['response'] = array(
                    "success" => false,
                    "date_time" => $date_time,
                    "message" => "An error occurred, Try again later!c"
                );
                return $response->withHeader('Content-Type', 'application/json')
                ->write(json_encode($message_array));
            }
        }else{
            $message_array['response'] = array(
                "success" => false,
                "date_time" => $date_time,
                "message" => "An error occurred, Try again later!b"
            );
            return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($message_array));
        }
    }else{
        $message_array['response'] = array(
            "success" => false,
            "date_time" => $date_time,
            "message" => "An error occurred, Try again later!a"
        );
        return $response->withHeader('Content-Type', 'application/json')
        ->write(json_encode($message_array));
    }
});