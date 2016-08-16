<?php

chdir(__DIR__);
exec("find data/encrypted/ data/plaintext/ -type f -exec chmod 0600 '{}' '+'");

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

$logging    = \Plop\Plop::getInstance();
$handlers   = new \Plop\HandlersCollection();
$handlers[] = new \Plop\Handler\Stream(STDOUT);
$logging->addLogger(new \Plop\Logger('fpoirotte\\Pssht\\Tests\\Helpers', null, null, $handlers));
