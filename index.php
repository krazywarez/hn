<?php

require_once 'src/Controller/RouteController.php';

$GLOBALS['full_domain'] = 'https://hn.cleberg.net';
$GLOBALS['author_name'] = 'Christian Cleberg';
$GLOBALS['site_title'] = 'hn';

$route = new HN\Controllers\RouteController($_SERVER['REQUEST_URI']);
$route->routeUser();
