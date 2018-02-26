#CHANGELOG 2/27/2018


Start building documentation using swagger


![swagger](https://user-images.githubusercontent.com/1977383/36696544-75821f64-1b7f-11e8-9ef5-cedde333c101.jpg)


clstrak.php

1. Added public function isLocationExist($custid, $description)
   This validate added location record for duplicate

admin.php

1. update path /api/admin/location/add/{custid} - add validation



#CHANGELOG 2/26/2018

clstrak.php

1. added public function isKiToolExist($custid,$code)
   This validate added kit tool on kittools table for duplicate record using unique qrcode field

users.php

1. update  path /api/user/login. Change Method from GET to POST and assigned variables (username and password) to QueryParams as shown

    $username = $request->getQueryParam('username');
    $password = $request->getQueryParam('password');





CHANGELOG 2/21/2018


users.php

1. update $app->get('/api/users/{custid}, added filter on active field (active=1). This to load on granted users
2. removed $app->get('/api/users/{custid} path
3. remove // Update user record $app->put('/api/user/update/{userid}/{custid}', function(Request $request, Response $response) {


admin.php

1. added $app->get('/api/admin/user/{userid}', function(Request $request, Response $response)
2. added $app->get('/api/admin/users/{custid}', function(Request $request, Response $response), this loads both revoked/granted users
3. added Update tail $app->put('/api/admin/tail/update/{number}/{custid}', function(Request $request, Response $response)
4. added // Deactivate specific Tail $app->put('/api/admin/tail/disable/{number}/{custid}', function(Request $request, Response $response)
5. added // Activate specific Tail $app->put('/api/admin/tail/enable/{number}/{custid}', function(Request $request, Response $response)
6. added // Add new tail $app->post('/api/admin/tail/add/{custid}', function(Request $request, Response $response)
7. added // Update user record $app->put('/api/admin/user/update/{userid}/{custid}', function(Request $request, Response $response)
8. added // Reservation List app->get('/api/admin/reservation/list/{custid}', function(Request $request, Response $response)
9. added // Log List $app->get('/api/admin/log/list/{custid}', function(Request $request, Response $response)
10. added // View locker list $app->get('/api/admin/locker/list/{custid}', function(Request $request, Response $response)
11. added // Update locker $app->put('/api/admin/locker/update/{custid}/{lockerid}', function(Request $request, Response $response)
12. added // Add locker $app->post('/api/admin/locker/add/{custid}', function(Request $request, Response $response)
13. added // Disable specific  locker $app->put('/api/admin/locker/disable/{custid}/{lockerid}', function(Request $request, Response $response)
14. added // enable specific  locker $app->put('/api/admin/locker/enable/{custid}/{lockerid}', function(Request $request, Response $response)
15. added // View kit List $app->get('/api/admin/kits/list/{custid}', function(Request $request, Response $response)
16. added //Update kit $app->put('/api/admin/kit/update/{custid}/{kitid}', function(Request $request, Response $response)
17. added // Add KIT $app->post('/api/admin/kit/add/{custid}', function(Request $request, Response $response)
18. added // View kit Tool List $app->get('/api/admin/kittools/list/{custid}', function(Request $request, Response $response)
19. added //Update kit tool $app->put('/api/admin/kittool/update/{custid}/{toolkitid}', function(Request $request, Response $response)
20. added // Add KIT Tool $app->post('/api/admin/kittool/add/{custid}', function(Request $request, Response $response)
21. added // View Tool List $app->get('/api/admin/tools/list/{custid}', function(Request $request, Response $response)
22. added //Update tool $app->put('/api/admin/tool/update/{custid}/{toolid}', function(Request $request, Response $response)
23. added // Add Tool $app->post('/api/admin/tool/add/{custid}', function(Request $request, Response $response)
24. added // View Tool Category List $app->get('/api/admin/toolcategory/list/{custid}', function(Request $request, Response $response)
25. added //Update tool categoy $app->put('/api/admin/toolcategory/update/{custid}/{id}', function(Request $request, Response $response)
26. added // Add Tool category $app->post('/api/admin/toolcategory/add/{custid}', function(Request $request, Response $response)
27. added // View location List $app->get('/api/admin/locations/list/{custid}', function(Request $request, Response $response)
28. added //Update Location $app->put('/api/admin/location/update/{custid}/{id}', function(Request $request, Response $response)
29. added // Add location $app->post('/api/admin/location/add/{custid}', function(Request $request, Response $response)


transaction.php

1. added // Add log $app->post('/api/log/add/{custid}', function(Request $request, Response $response)

clstrak.php

1. update public function isUserExist($custid, $userid) - added filter users.auditrak =1

Database

1. added log table - to log system wide operations of Auditrak
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `logdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `custid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

2. Update Database Model
3. Update Database Dump

2. Added active field in locker table
   CREATE TABLE `lockers` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `custid` int(11) DEFAULT NULL,
     `description` varchar(255) DEFAULT NULL,
     `code` varchar(200) DEFAULT NULL COMMENT 'Code to unlock locker via wifi or bluetooth',
     `locationid` int(11) DEFAULT NULL COMMENT 'Location ID',
     `active` int(1) DEFAULT NULL COMMENT '0 - inactive 1- active',
     PRIMARY KEY (`id`)
   ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Locker Table';

3. Addes custid field in toolcategories table

CREATE TABLE `toolcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL COMMENT 'Tool Categoy Description  kabtrak,portatrak,cribtrak,audittrak',
  `custid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Tool category table';



