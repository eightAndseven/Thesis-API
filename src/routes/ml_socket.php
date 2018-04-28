<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->get('/api/ml_socket/get_category',function($request, $response){
    $db = new Database();
    $db = $db->connectDB();

    $category = new ApplianceCategory($db);
    $stmt = $category->getCategory();

    $count = $stmt->rowCount();
    if($count >= 1){
        $arr_category["category"] = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $arr_items = array(
                "id"=>$id,
                "name"=>$name
            );
            array_push($arr_category["category"], $arr_items);
        }
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($arr_category));
    }else{
        return $response->write("No data");
    }
});

$app->post('/api/ml_socket/add_category', function($request, $response){
    $name = $request->getParam("name");
    if(!empty($name)){
        $db = new Database();
        $db = $db->connectDB();

        $category = new ApplianceCategory($db);
        $category->name = $name;
        $isAdded = $category->addCategory();

        $add_category["category"] = array(
            "name"=>$name,
            "success"=>true
        );

        if($isAdded){
            return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($add_category));
        }else{
            return $response->write($isAdded);
        }
    }else{
        return $response->write("ERROR1");
    }
});

$app->get('/api/ml_socket/get_specific/{id}', function($request, $response){
    $specific_id = $request->getAttribute("id");

    $db = new Database();
    $db = $db->connectDB();

    $specific = new ApplianceSpecific($db);
    $specific->category_id = $specific_id;
    $stmt = $specific->getAppSpecific();
    $count = $stmt->rowCount();

    if($count > 0){
        $arr_specific["specific"] = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $arr_items = array(
                "id"=> $id,
                "name"=> $name,
                "category_id"=>$category_id
            );
            array_push($arr_specific["specific"], $arr_items);
        }
        $status_arr["status"] = array(
            "row_count"=>$count,
            "success"=> true
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode(array_merge($status_arr, $arr_specific)));
    }else{
        $status_arr["status"] = array(
            "row_count"=> $count,
            "success"=> false
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($status_arr));
    }
});

$app->post('/api/ml_socket/add_specific', function($request, $response){
    $name = $request->getParam("name");
    $category_id = $request->getParam("id_category");

    $db = new Database();
    $db = $db->connectDB();

    $specific = new ApplianceSpecific($db);
    $specific->name = $name;
    $specific->category_id = (int)$category_id;
    if($specific->addAppSpecific()){
        $add_specific["specific"] = array(
            "name"=>$name,
            "category_id"=>$category_id,
            "success"=>true
        );
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode($add_specific));
    }else{
        return $response->write("ERROR");
    }
});