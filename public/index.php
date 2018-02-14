<?php
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;
// use \Psr\Http\Message\ServerRequestInterface as Request;
//use \Psr\Http\Message\ResponseInterface as Response;
require '../vendor/autoload.php';
require '../src/config/db.php';
require '../src/config/clstrak.php';

$app = new \Slim\App;
//Enable debugging (on by default)

ini_set('memory_limit','-1'); // enabled the full memory available.

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "secure" => false,
    "users" => [
        "root" => " ",
        "somebody" => "passw0rd",
        "gannet" => "k43tr4k"
    ]
]));


$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});

// User
require '../src/routes/users.php';
require '../src/routes/employees.php';
require '../src/routes/tails.php';
$app->run();