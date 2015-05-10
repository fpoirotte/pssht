<?php

namespace fpoirotte\Pssht\Tests\Helpers;

/**
 *  \c PATH lookup for executables.
 *
 *  \param string $binary
 *      Name of the executable to look for.
 *      Full path (eg. "/bin/bash" or "foo/bar") are also accepted,
 *      but no \c PATH lookup will be attempted for those.
 *
 *  \param bool $real
 *      (optional) Whether to return the real (absolute) path
 *      on success (\c true) or the path as it was detected (\c false).
 *      Real paths are free from extra directory separators, symlinks
 *      and references to relative directories ("." and "..").
 *
 *  \param array $paths
 *      (optional) Use these paths instead of the ones
 *      listed in the \c PATH environment variable.
 *
 *  \param array $pathexts
 *      (optional, Windows only) Use these extensions
 *      to identify executables instead of the ones
 *      listed in the \c PATHEXT environment variable.
 *
 *  \retval string
 *      On success, the path to the executable is returned
 *      (either its real path or the one used to find it,
 *      depending on the value of \a $real).
 *      On failure, \c null is returned.
 *
 *  \warning
 *      When \a $real is set to \c true (the default),
 *      this function is subjected to PHP's \c open_basedir
 *      restrictions. Set \a $real to \c false if this is
 *      an issue for you.
 *
 *  \note
 *      The SPL extension is required to get proper
 *      exceptions when using invalid arguments.
 *      It is not needed when the API is respected.
 *
 *  \exception ::InvalidArgumentException
 *      Thrown when invalid arguments are passed
 *      to this function.
 */
function findBinary(
    $binary,
    $real = true,
    array $paths = array(),
    array $pathexts = array()
) {
    // Parameter validation.
    if (!is_string($binary)) {
        throw new \InvalidArgumentException('$binary must be a string');
    }
    if (!is_bool($real)) {
        throw new \InvalidArgumentException('$real must be a boolean');
    }
    foreach ($paths as $path) {
        if (!is_string($path)) {
            throw new \InvalidArgumentException(
                '$paths may only contain strings');
        }
    }
    foreach ($pathexts as $pathext) {
        if (!is_string($pathext)) {
            throw new \InvalidArgumentException(
                '$pathexts may only contain strings');
        }
    }

    $dirseps = array(DIRECTORY_SEPARATOR);
    $windows = !strncasecmp(PHP_OS, 'Win', 3);

    // Use the PATH environment variable,
    // unless caller specifically overrides it.
    if (!count($paths)) {
        $env = getenv('PATH');
        if ($env !== false) {
            $paths = explode(PATH_SEPARATOR, $env);
        }
    }

    // Remove empty PATHs.
    while (($key = array_search('', $paths, true)) !== false) {
        unset($paths[$key]);
    }

    // Cygwin behaves like Linux and is thus omitted from this check.
    if ($windows) {
        // Windows accepts both "\" & "/" as DIRECTORY_SEPARATORs.
        $dirseps[]  = '/';
        // Hack to handle strange PATHs like "C:foo/" or "C:".
        $dirseps[]  = ':';

        // It also makes use of the PATHEXT environment variable
        // when looking for executables in the PATH.
        $env        = count($pathexts)
            ? implode(PATH_SEPARATOR, $pathexts)
            : getenv('PATHEXT');
        $pathexts   =
            (!in_array($env, array(false, ''))) // Empty or no PATHEXT
            ? explode(PATH_SEPARATOR, $env)
            : array('.COM', '.EXE', '.BAT', '.CMD');

        // Remove empty PATHEXTs.
        while (($key = array_search('', $pathexts, true)) !== false) {
            unset($pathexts[$key]);
        }

        // Finally, it always looks for files in the current directory first,
        // unless the command contains a path (this is handled below).
        array_unshift($paths, '.');
    } else {
        $pathexts = array('');
    }

    // Handle commands that contain a path.
    $lastSeparator = -1;
    foreach ($dirseps as $dirsep) {
        if (($pos = strrpos($binary, $dirsep)) !== false) {
            $lastSeparator = max($lastSeparator, $pos + strlen($dirsep));
        }
    }
    if ($lastSeparator !== -1) {
        $paths  = array(substr($binary, 0, $lastSeparator));
        $binary = substr($binary, $lastSeparator);

        // Eg. "/path/to/nothing/".
        if ($binary === false) {
            return null;
        }
    }

    // No paths left to search
    // (eg. "PATH=", "PATH=:" / "PATH=;", etc.).
    if (!count($paths)) {
        return null;
    }

    // Handle "X:relative/path" on Windows.
    if ($windows && ($pos = strpos($paths[0], ':')) === 1) {
        $oldcwd = getcwd();
        chdir(substr($paths[0], 0, 2));
        $paths  = array(getcwd() . (string) substr($paths[0], 2));
        chdir($oldcwd);
    }

    // We're on Windows and the command has an extension.
    // Look for a binary with that exact name and extension.
    if ($windows && strpos($binary, '.') !== false) {
        foreach ($paths as $path) {
            $file = $path . DIRECTORY_SEPARATOR . $binary;

            // Both is_file & is_executable follow symlinks
            // and return false for broken symlinks.
            if (is_file($file) && is_executable($file)) {
                return $real ? realpath($file) : $file;
            }
        }
        return null;
    }

    // Otherwise, loop through all possible paths and extensions.
    // The only valid extension on non-Windows is "" (i.e. no extension).
    foreach ($paths as $path) {
        foreach ($pathexts as $pathext) {
            $file = $path . DIRECTORY_SEPARATOR . $binary;
            // Both is_file & is_executable follow symlinks
            // and return false for broken symlinks.
            if (is_file($file) && is_executable($file)) {
                return $real ? realpath($file) : $file;
            }
        }
    }
    return null;
}

