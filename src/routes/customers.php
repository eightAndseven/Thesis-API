<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// $configuration = [
//     'settings' => [
//         'displayErrorDetails' => true,
//     ],
// ];
// $c = new \Slim\Container($configuration);
// $app = new \Slim\App($c);


//get all customers
$app->get('/api/customers', function(Request $request, Response $response){
    $sql = "SELECT * FROM customers";
    try{
        //get db
        $db = new TestDb();

        //connect
        $db = $db->connectDB();

        $stmt = $db->query($sql);
        $customers = $stmt->fetchall(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($customers);
    }catch(PDOException $e){
        echo '{"error":{"text" : '.$e->getMessage().'}}';
    }
});

//get single customer
$app->get('/api/customer/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sql = "SELECT * FROM customers WHERE id=$id";
    try{
        //get db
        $db = new TestDb();

        //connect
        $db = $db->connectDB();

        $stmt = $db->query($sql);
        $customers = $stmt->fetchall(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($customers);
    }catch(PDOException $e){
        echo '{"error":{"text" : '.$e->getMessage().'}}';
    }
});

//add customers
$app->post('/api/customer/add', function(Request $request, Response $response){
    $first_name = $request->getParam('first_name');
    $last_name = $request->getParam('last_name');
    $phone = $request->getParam('phone');
    $email = $request->getParam('email');
    $address = $request->getParam('address');   
    $city = $request->getParam('city');
    $state = $request->getParam('state');

    $sql = "INSERT INTO customers (first_name, last_name, phone, email, address, city, state) VALUES 
    (:first_name, :last_name, :phone, :email, :address, :city, :state)";

    try{
        //get db
        $db = new TestDb();

        //connect
        $db = $db->connectDB();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name",  $last_name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":city", $city);
        $stmt->bindParam(":state", $state);

        $stmt->execute();

        echo '{"notice": {"text": "Customer Added"}}';
    }catch(PDOException $e){
        echo '{"error":{"text" : '.$e->getMessage().'}}';
    }
});

//udpate customer
$app->put('/api/customer/update/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $first_name = $request->getParam('first_name');
    $last_name = $request->getParam('last_name');
    $phone = $request->getParam('phone');
    $email = $request->getParam('email');
    $address = $request->getParam('address');   
    $city = $request->getParam('city');
    $state = $request->getParam('state');

    // $sql = "INSERT INTO customers (first_name, last_name, phone, email, address, city, state) VALUES 
    // (:first_name, :last_name, :phone, :email, :address, :city, :state)";

    $sql = "UPDATE customers SET 
        first_name = :first_name, 
        last_name  = :last_name, 
        phone      = :phone, 
        email      = :email, 
        address    = :address, 
        city       = :city,
        state      = :state 
    WHERE id = $id";

    try{
        //get db
        $db = new TestDb();

        //connect
        $db = $db->connectDB();

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name",  $last_name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":city", $city);
        $stmt->bindParam(":state", $state);

        $stmt->execute();

        echo '{"notice": {"text": "Customer Updated"}}';
    }catch(PDOException $e){
        echo '{"error":{"text" : '.$e->getMessage().'}}';
    }
});

//get delete customer
$app->delete('/api/customer/delete/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sql = "DELETE FROM customers WHERE id=$id";
    try{
        //get db
        $db = new TestDb();

        //connect
        $db = $db->connectDB();

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "User is deleted"}}';
    }catch(PDOException $e){
        echo '{"error":{"text" : '.$e->getMessage().'}}';
    }
});