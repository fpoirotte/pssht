<?php

if (posix_setsid() === -1) {
    fprintf(STDERR, "Could not become session leader!\n");
    exit(255);
}

$args = array();
foreach ($_SERVER['argv'] as $index => $value) {
    if ($index === 0) {
        continue;
    }
    $args[] = escapeshellarg($value);
}

$exitCode   = 1;
passthru(implode(' ', $args), $exitCode);
exit($exitCode);
