<?php

posix_setsid();

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
