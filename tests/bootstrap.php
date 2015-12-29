<?php

require(
    dirname(__DIR__) .
    DIRECTORY_SEPARATOR . 'vendor' .
    DIRECTORY_SEPARATOR . 'autoload.php'
);

require(__DIR__ . DIRECTORY_SEPARATOR . 'Helpers.php');

require(
    __DIR__ .
    DIRECTORY_SEPARATOR . 'Helpers' .
    DIRECTORY_SEPARATOR . 'AbstractSshClient.php'
);

require(
    __DIR__ .
    DIRECTORY_SEPARATOR . 'Helpers' .
    DIRECTORY_SEPARATOR . 'SshClient' .
    DIRECTORY_SEPARATOR . 'Openssh.php'
);

require(
    __DIR__ .
    DIRECTORY_SEPARATOR . 'Helpers' .
    DIRECTORY_SEPARATOR . 'SshClient' .
    DIRECTORY_SEPARATOR . 'Putty.php'
);

require(
    __DIR__ .
    DIRECTORY_SEPARATOR . 'Helpers' .
    DIRECTORY_SEPARATOR . 'AbstractConnectionTest.php'
);

require(
    __DIR__ .
    DIRECTORY_SEPARATOR . 'Helpers' .
    DIRECTORY_SEPARATOR . 'OutputException.php'
);

$logging    = \Plop\Plop::getInstance();
$handlers   = new \Plop\HandlersCollection();
$handlers[] = new \Plop\Handler\Stream(fopen('php://output', 'w'));
$logging->addLogger(new \Plop\Logger('fpoirotte\\Pssht\\Tests\\Helpers', null, null, $handlers));
