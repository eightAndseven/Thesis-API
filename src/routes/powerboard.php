<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

/**
 * Login user with route: http://piboard/slimapi/api/powerboard/login
 */
$app->post('/api/powerboard/login', function ($request, $response) {
    //get request parameter
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    $date_time = date('Y-m-d H:i:s');

    //hash password
    $hash = new Hasher($password);
    $hash = $hash->HashPassword();

    //get database
    $db = new Database();
    $dbconn = $db->connectDB();

    //get object user
    $user = new User($dbconn);
    $user->username = $username;
    $user->password= $password;
    $user->hashed_password = $hash;
    $validate = $user->loginValidate();
    if($validate){
        $stmt = $user->loginUser();
        $count = $stmt->rowCount();
        if($count == 1){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_array['user'] = array(
                "id"=>$row['id'],
                "username"=>$row['username']
            );
            $message_array['response'] = array(
                "isLogin" => true,
                "date_time" => $date_time,
                "message" => "Login Successful!"
            );
            return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array_merge($message_array, $user_array)));
        }else{
            $message_array['response'] = array(
                "isLogin" => false,
                "date_time" => $date_time,
                "message" => "Incorrect username or password"
            );
            return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($message_array));
        }
    }else{
        $message_array['response'] = array(
            "isLogin" => false,
            "date_time" => $date_time,
            "message" => "Incorrect username or password"
        );
        return $response->withHeader('Content-Type', 'application/json')
        ->write(json_encode($message_array));
    }
});

/**
 * Get user activities with route: http://piboard/slimapi/api/powerboard/activities
 */
$app->get('/api/powerboard/activities',function($request, $response){
    $date_time = date('Y-m-d H:i:s');

    //get database connection
    $db = new Database();
    $db = $db->connectDB();

    //get object activity
    $activity = new Activity($db);
    $stmt = $activity->getUserActivity();
    $count = $stmt->rowCount();
    
    //if row count is greater than 0
    if($count > 0){
        $activity_arr["user_activity"] = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);

            $activity_items = array(
                "id" => $id,
                "uid" => $user_id,
                "username" => $user_username,
                "user_activity" => $user_activity,
                "date_time" => $date_time
            );
            array_push($activity_arr["user_activity"], $activity_items);
        }

        $message_array["response"] = array(
            "success"=>true,
            "date_time"=>$date_time,
            "message" => "$count user activities"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array_merge($message_array,$activity_arr)));
    }else{
        $message_array['response'] = array(
            "success"=>false,
            "date_time" => $date_time,
            "message" => "No user activities"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($message_array));
    }
});

/**
 * Get daily wattage for socket {id} in route: http://piboard/slimapi/public/api/powerboard/daily_consumed/{socket}
 */
$app->get('/api/powerboard/daily_consumed/{socket}',function($request, $response){
    //get request parameter
    $socket = $request->getAttribute('socket');
    $date_time = date('Y-m-d H:i:s');

    //get database connection
    $db = new Database();
    $db = $db->connectDB();

    //get object dailyconsumed
    $daily = new DailyConsumed($db);
    $daily->socket_id = $socket;
    $stmt = $daily->getDailyConsumed();
    $count;
    if($stmt != null){
        $count = $stmt->rowCount();
    }else{
        $count = -1;
    }
    //if row is greater than 0
    if($count > 0){
        $consumed_arr['daily_consumed'] = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);

            $consumed_item = array(
                "id"=>$id,
                "socket_id"=>$socket_id,
                "watt_consumed"=>$watt_cons,
                "date"=>$date
            );
            array_push($consumed_arr['daily_consumed'], $consumed_item);
        }
        $message_array["response"] = array(
            "socket"=>$socket,
            "success"=>true,
            "date_time"=>$date_time,
            "message" => "$count daily reports for socket $socket"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array_merge($message_array, $consumed_arr)));
    }else{
        $message_array['response'] = array(
            "success"=>false,
            "date_time"=>$date_time,
            "message" => "No daily report"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($message_array));
    }
});

/**
 * Get daily wattage for socket {id} in route: http://piboard/slimapi/public/api/powerboard/weekly_consumed/{socket}
 */
$app->get('/api/powerboard/weekly_consumed/{socket}',function($request, $response){
    //get request parameter
    $socket = $request->getAttribute('socket');
    $date_time = date('Y-m-d H:i:s');

    //get database connection
    $db = new Database();
    $db = $db->connectDB();

    //get object weekly consumed
    $weekly = new WeeklyConsumed($db);
    $weekly->socket_id = $socket;
    $stmt = $weekly->getWeeklyConsumed();
    $count;
    if($stmt != null){
        $count = $stmt->rowCount();
    }else{
        $count = -1;
    }
    //if row is greater than 0
    if($count > 0){
        $consumed_arr['weekly_consumed'] = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);

            $consumed_item = array(
                "id"=>$id,
                "socket_id"=>$socket_id,
                "watt_consumed"=>$watt_cons,
                "date_from"=>$date_from,
                "date_to"=>$date_to,
                "week_no"=>$week_number
            );
            array_push($consumed_arr['weekly_consumed'], $consumed_item);
        }
        $message_array["response"] = array(
            "socket"=>$socket,
            "success"=>true,
            "date_time"=>$date_time,
            "message" => "$count weekly report for socket $socket"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array_merge($message_array, $consumed_arr)));
    }else{
        $message_array['response'] = array(
            "success"=>false,
            "date_time"=>$date_time,
            "message" => "No daily report"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($message_array));
    }
});

/**
 * Get socket info for socket {id} in route: http://piboard/slimapi/public/api/powerboard/socket_status/{socket}
 */
$app->get('/api/powerboard/socket_status/{socket}',function($request, $response){
    //get request parameter
    $socket_num = $request->getAttribute('socket');
    $date_time = date('Y-m-d H:i:s');

    //get database connection
    $db = new Database();
    $db = $db->connectDB();

    $socket = new Socket($db);
    $socket->socket_id = $socket_num;
    $read = $socket->readSocketInfo();
    if($read != "ERROR"){
        $socket_status["socket"] = array(
            "socket"=>$socket_num,
            "socket_status"=>$read
        );
        $message_array["response"] = array(
            "success"=>true,
            "date_time"=>$date_time,
            "message" => "Socket $socket_num is turned $read"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array_merge($message_array, $socket_status)));
    }else{
        $message_array["response"] = array(
            "success"=>false,
            "date_time"=>$date_time,
            "message" => "Bad Request with socket as $socket_num"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($message_array));
    }
});

/**
 * Get socket info for socket {id} in route: http://piboard/slimapi/public/api/powerboard/socket_status/{socket}
 */
$app->post('/api/powerboard/switch_socket',function($request, $response){
    //get request parameter
    // $username = $request->getParam('username');
    $socket_num = $request->getParam('socket');
    $socket_switch = $request->getParam('switch');
    $user_id = $request->getParam('user_id');
    $user_username = $request->getParam('user_username');
    $date_time = date('Y-m-d H:i:s');

    //get database connection
    $db = new Database();
    $db = $db->connectDB();

    //to switch the socket
    $socket = new Socket($db);
    $socket->socket_id = $socket_num;
    $socket->socket_switch = $socket_switch;
    $msg = $socket->stateOnOffSocket();

    if($msg != "ERROR"){
        //to save activity to db
        $activity = new Activity($db);
        $activity->id = $user_id;
        $activity->user_username = $user_username;
        $activity->date_time = $date_time;
        $activity->user_activity = $msg;
        $save_activity = $activity->saveActivity();
        
        if($save_activity){
            $socket_arr["socket"] = array(
                "socket" => $socket->socket_id,
                "socket_state" => $socket->socket_switch
            );

            $activity_arr["activity"] = array(
                "uid"=>$activity->id,
                "username"=>$activity->user_username,
                "date_time"=>$activity->date_time,
                "activity"=>$activity->user_activity
            );
            $message_array["response"] = array(
                "success"=>true,
                "date_time"=>$date_time,
                "message" => $msg . " with user ".$user_username. " and id ".$user_id
            );
            return $response->withHeader('Content-Type', 'application/json')
                ->write(json_encode(array_merge($message_array, $socket_arr, $activity_arr)));    
        }else{

        }
        
    }else{
        $message_array["response"] = array(
            "success"=>false,
            "date_time"=>$date_time,
            "message" => "Bad Request asd"
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($message_array));
    }
    
});





// $app->get('/api/powerboard/activities/{id}',function(){

// });