<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->add(function ($request, $response, $next) {
    $response = $next($request, $response);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*');
});

// Get All Customers

$app->get('/api/customers', function (Request $request, Response $response) {
    $sql = 'SELECT * FROM customers';

    try {
        $db = new db();

        $db = $db->connect();

        $stmt = $db->query($sql);

        $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
        $response->withStatus(200) ? $response->getBody()->write(json_encode($customers)) : null;
    } catch (PDOException $e) {
        echo 'error: ' . $e->getMessage();
    }
});


// Get Single Customer

$app->get('/api/customer/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "SELECT * FROM customers WHERE id = $id";

    try {
        $db = new db();

        $db = $db->connect();

        $stmt = $db->query($sql);

        $customer = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($customer);
    } catch (PDOException $e) {
        echo 'error: ' . $e->getMessage();
    }
});

// Add Customer

$app->post('/api/customer/add', function (Request $request, Response $response) {
    $first_name = $request->getParsedBody()['first_name'];
    $last_name = $request->getParsedBody()['last_name'];
    $phone = $request->getParsedBody()['phone'];
    $email = $request->getParsedBody()['email'];
    $address = $request->getParsedBody()['address'];
    $city = $request->getParsedBody()['city'];
    $state = $request->getParsedBody()['state'];

    $data = [
        'msg' => "customer added",
        'info' => [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'state' => $state
        ]
    ];
    $jsonData = json_encode($data);

    $sql = "INSERT INTO customers (first_name, last_name, phone, email, address, city, state) VALUES (
            :first_name, :last_name, :phone, :email, :address, :city, :state
    )";
    $sql2 = "SELECT first_name FROM customers WHERE `first_name` = :first_name";

    try {
        $db = new db();
        $db = $db->connect();
        $stmt2 = $db->prepare($sql2);
        $stmt2->bindParam(':first_name', $first_name);
        $stmt2->execute();

        // check if customer exist
        if ($stmt2->rowCount() < 1) {
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':state', $state);
            $stmt->execute();
            $response->getBody()->write($jsonData);
        } else {
            $feedback = [['msg' => 'User already exist']];
            $response->getBody()->write(json_encode($feedback));
        }
    } catch (PDOException $e) {
        echo 'error: ' . $e->getMessage();
    }
});

// update Customer

$app->put('/api/customer/update/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $first_name = $request->getParsedBody()['first_name'];
    $last_name = $request->getParsedBody()['last_name'];
    $phone = $request->getParsedBody()['phone'];
    $email = $request->getParsedBody()['email'];
    $address = $request->getParsedBody()['address'];
    $city = $request->getParsedBody()['city'];
    $state = $request->getParsedBody()['state'];
    $response->getBody()->write("customer updated");

    // sql query
    $sql = "UPDATE customers SET
            first_name = :first_name, 
            last_name = :last_name, 
            phone = :phone, 
            email = :email, 
            address = :address, 
            city = :city, 
            state = :state
        WHERE id = $id";

        // database logic
    try {
        $db = new db();

        $db = $db->connect();

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':state', $state);

        $stmt->execute();
    } catch (PDOException $e) {
        echo 'error: ' . $e->getMessage();
    }
});

// Delete Customer

$app->delete('/api/customer/delete/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "DELETE FROM customers WHERE id = $id";

    try {
        $db = new db();

        $db = $db->connect();

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $stmt = null;
        echo '{"notice": {"text":"customer deleted"}}';
    } catch (PDOException $e) {
        echo 'error: ' . $e->getMessage();
    }
});

// Customer File Upload
$app->post('/api/customer/upload', function (Request $request, Response $response) {
    $uploadedFiles = $request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['fileName'];

    $scaned = scandir('../uploads');
    $scanedFileName = end($scaned);
    json_encode($scanedFileName);
    $scanedName = explode(".", $scanedFileName)[0];
    $getUploadedFileName = explode(".", $uploadedFile->getClientFilename())[0];
    json_encode($getUploadedFileName);
    
    // validation check
    if (!in_array($getUploadedFileName, $scaned) && $getUploadedFileName !== $scanedName) {
        $uploadedFile->moveTo("../uploads/" . $uploadedFile->getClientFilename());
        $response->getBody()->write('File uploaded successfully');
    } elseif ($getUploadedFileName == $scanedName) {
        $response->getBody()->write("File Name Already Exist");
    } elseif (empty($uploadedFile)){
        $response->getBody()->write('No file uploaded');
    }
});
