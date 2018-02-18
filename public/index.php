<?php
/**
 * Henchman Products PTY.  Standard Copyright and Disclaimer Notice:
 *
 * Copyright ©2018. Henchman Products PTY.  All Rights Reserved. Permission to use, copy, modify, and distribute this
 * software and its documentation for educational, research, and not-for-profit purposes, without fee and without a signed
 * licensing agreement, is hereby granted, provided that the above copyright notice, this paragraph and the following two
 * paragraphs appear in all copies, modifications, and distributions.
 *
 * IN NO EVENT SHALL HENCHMAN  BE LIABLE TO ANY PARTY FOR DIRECT, INDIRECT, SPECIAL, INCIDENTAL, OR
 * CONSEQUENTIAL DAMAGES, INCLUDING LOST PROFITS, ARISING OUT OF THE USE OF THIS SOFTWARE AND ITS
 * DOCUMENTATION, EVEN IF REGENTS HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * HENCHMAN SPECIFICALLY DISCLAIMS ANY WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE SOFTWARE AND
 * ACCOMPANYING DOCUMENTATION, IF ANY, PROVIDED HEREUNDER IS PROVIDED "AS IS".HENCHMAN
 *  HAS NO OBLIGATION TO PROVIDE MAINTENANCE, SUPPORT, UPDATES, ENHANCEMENTS, OR MODIFICATIONS.
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

$config = [
    'settings' => [
        'displayErrorDetails' => TRUE,
        'addContentLengthHeader' => FALSE,
    ],
];

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "secure" => false,
    "users" => [
        "root" => " ",
        "somebody" => "passw0rd",
        "gannet" => "k43tr4k",
        "techugo" => "namrata"
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