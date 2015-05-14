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

$logging = \Plop\Plop::getInstance();
$handlers = $logging->getLogger()->getHandlers();
define('PSSHT_TESTS_LOG', tempnam(sys_get_temp_dir(), 'pssht-'));
#$null = strncasecmp(PHP_OS, 'Win', 3) ? '/dev/null' : 'NUL';
$handlers[0] = new \Plop\Handler\Stream(fopen(PSSHT_TESTS_LOG, 'w'));
