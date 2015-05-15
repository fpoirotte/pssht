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
$output     = array();
$res        = exec(implode(' ', $args), $output, $exitCode);

echo implode('', $output);
exit($exitCode);
