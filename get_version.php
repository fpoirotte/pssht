#!/usr/bin/env php
<?php
/**
 * This program displays various information
 * about the current component.
 */

function usage($prog)
{
    echo "Usage: $prog [options] [path/to/git/repository/]" . PHP_EOL;
    echo "Options:" . PHP_EOL;
    echo "-C, --composer" . PHP_EOL;
    echo "    Displays the component's name in Composer" . PHP_EOL;
    echo "-N, --normalize" . PHP_EOL;
    echo "    Displays the normalized version instead of the raw one." . PHP_EOL;
}

function get_composer($dir)
{
    $composer = @file_get_contents(
        $dir .
        DIRECTORY_SEPARATOR . 'composer.json'
    );
    if ($composer === FALSE) {
        fprintf(STDERR, "Could not find 'composer.json'.%s", PHP_EOL);
        exit(1);
    }

    $composer = @json_decode(
        $composer,
        TRUE
    );
    if ($composer === NULL) {
        fprintf(STDERR, "Could not parse 'composer.json'.%s", PHP_EOL);
        exit(1);
    }
    return $composer;
}

function version($dir, $normalize)
{
    $NUL        = strncasecmp(PHP_OS, 'Win', 3) ? '/dev/null' : 'NUL';
    $fallback   = FALSE;

    exec('git describe --tags --exact-match --dirty 2>' . $NUL, $output, $exitcode);
    if ($exitcode != 0) {
        $fallback = TRUE;
    }
    else {
        // A tag matched the HEAD exactly...
        $version = trim($output[0]);

        // ...unless the current tree is actually dirty.
        if (substr($version, -6) === '-dirty') {
            $fallback = TRUE;
        }
    }

    // No matching tag or dirty working tree.
    // Fallback to using the current branch's name.
    if ($fallback) {
        unset($output);
        exec('git symbolic-ref --short HEAD 2>' . $NUL, $output, $exitcode);
        $version = 'dev-' . trim($output[0]);
        unset($output);

        $composer = get_composer($dir);
        if (isset($composer['extra']['branch-alias'][$version])) {
            $version = $composer['extra']['branch-alias'][$version];
            unset($composer);
            if ($normalize) {
                $version = str_replace('x', '9999999', $version);
            }
        }
        // For unaliased branches, use a dummy version.
        else {
            $version = '0.0.0-dev';
        }
    }

    // We do this small normalization step
    // regardless of the $normalize flag.
    if (!strncmp($version, 'v', 1)) {
        $version = (string) substr($version, 1);
    }

    // Actual normalization.
    if ($normalize) {
        if (!preg_match(
            '/^(?J)(\\d+)\\.(\\d+)\\.(\\d+)(?:-(?:(?<mod>(?:alpha|beta|RC|patch))(\\d+)?|(?<mod>dev)))?$/',
            $version,
            $parts
        )) {
            fprintf(STDERR, "Invalid version: %s%s", $version, PHP_EOL);
            exit(1);
        }

        $stability = 4;
        $stabilityMap = array_flip(array(
            'dev',
            'alpha',
            'beta',
            'RC',
            'stable',
            'patch'
        ));

        if (isset($parts['mod'])) {
            $stability = $stabilityMap[$parts['mod']];
        }
        $stability *= 1000;
        if (isset($parts[5])) {
            // We cap the number after the stability suffix
            // to avoid overflowing into another stability
            // for very larger numbers (eg. 1.0.0-RC1000).
            // Also, values >65535 cause an overflow on Windows.
            $stability += min(999, (int) $parts[5]);
        }

        $version = "${parts[1]}.${parts[2]}.${parts[3]}.$stability";
    }

    echo $version . PHP_EOL;
    exit($exitcode);
}

function composer($dir)
{
    $composer = get_composer($dir);
    echo $composer['name'] . PHP_EOL;
    exit(0);
}

function main()
{
    $args       = $_SERVER['argv'];
    $script     = array_shift($args);
    $normalize  = FALSE;
    $composer   = FALSE;
    $rem_args   = array();

    for ($i = 0, $m = count($args); $i < $m; $i++) {
        $arg = $args[$i];

        if ($arg == '-N' || $arg == '--normalize')
            $normalize = TRUE;
        else if ($arg == '-C' || $arg == '--composer')
            $composer = TRUE;
        else if (!strncmp($arg, '-', 1)) {
            usage($script);
            exit(1);
        }
        else {
            $rem_args[] = $arg;
        }
    }

    $dir = getcwd();
    if (count($rem_args)) {
        $dir = $rem_args[0];
        putenv('GIT_DIR=' . $dir . DIRECTORY_SEPARATOR . '.git');
    }

    // Retrieve the path to the repository's toplevel directory.
    $NUL = strncasecmp(PHP_OS, 'Win', 3) ? '/dev/null' : 'NUL';
    exec('git rev-parse --show-toplevel 2>' . $NUL, $output, $exitcode);
    if ($exitcode != 0) {
        fprintf(STDERR, "Could not determine path to .git folder.%s", PHP_EOL);
        exit(1);
    }
    $dir = trim($output[0]);

    if ($composer)
        composer($dir);
    version($dir, $normalize);
}

main();

