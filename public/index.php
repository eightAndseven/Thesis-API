<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';
require '../src/config/hasher.php';
require '../src/objects/objects.php';
require '../src/objects/mailer_object.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});

//customers routes
// require '../src/routes/customers.php';

//powerboard routes
require '../src/routes/powerboard.php';

//mailer route
require '../src/routes/mailer_powerboard.php';
$app->run();