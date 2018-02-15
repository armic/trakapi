<?php
/**
 * Copyright (c) 2018. Gabriel A. Tolentino
 * Henchman Products PTY.
 */

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;
// use \Psr\Http\Message\ServerRequestInterface as Request;
//use \Psr\Http\Message\ResponseInterface as Response;
require '../vendor/autoload.php';
require '../src/config/db.php';
require '../src/config/clstrak.php';

define("ISSUE", 1);
define("IRETURN",0);
define("ADMIN", 1);
define("USER",0);
define("OUT",1);
define("IN",0);
define("GRANTED",1);
define("REVOKED",0);
define("AUDITTRAK",1);
define("KIT",1);
define("TOOL",0);


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
require '../src/routes/transactions.php';
$app->run();