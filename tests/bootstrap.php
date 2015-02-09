<?php

require(
    dirname(__DIR__) .
    DIRECTORY_SEPARATOR . 'vendor' .
    DIRECTORY_SEPARATOR . 'autoload.php'
);

$logging = \Plop\Plop::getInstance();
$handlers = $logging->getLogger()->getHandlers();
$handlers[0] = new \Plop\Handler\Stream(fopen('/dev/null', 'w'));
