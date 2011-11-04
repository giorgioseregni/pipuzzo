<?php

error_reporting(E_ALL);
//

require(__DIR__ . '/lib/SplClassLoader.php');

$classLoader = new SplClassLoader('WebSocket', __DIR__ . '/lib');
$classLoader->register();

$server = new \WebSocket\Server('server.editinn.com', 1222331);
//$server->registerApplication('ftp', \WebSocket\Application\FtpApplication::getInstance());
$server->registerApplication('echo', \WebSocket\Application\EchoApplication::getInstance());


$server->run();

?>