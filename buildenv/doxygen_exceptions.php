<?php

$target = dirname(__DIR__) .
    DIRECTORY_SEPARATOR . 'docs' .
    DIRECTORY_SEPARATOR . 'exceptions.php';

$fp = fopen($target, 'wt');
fwrite($fp, '<' . "?php\n/**\n");
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src'
    )
);

$namespace = '';
$class = null;
$method = null;
$braces = 0;
$signature = '';
$exceptions = array();
$extends = '';
$implements = array();
$output = '';

foreach ($it as $path => $entry) {
    $namespace = '';
    $tokens = token_get_all(file_get_contents($path));

    foreach ($tokens as $i => $token) {
        if ($class !== null && $method !== null) {
            if ($token === ';' && $braces === 0) {
                $method = null;
                $signature = '';
                $exceptions = array();
            } elseif ($token === '{') {
                $braces++;
            } elseif ($token === '}') {
                if ($braces === 0) {
                    throw new \RuntimeException();
                } else {
                    $braces--;
                    if ($braces === 0) {
                        $signature = trim(preg_replace('/\\s+/', ' ', $signature));
                        $signature = ltrim($signature, '\\');
                        $signature = str_replace('\\', '::', $signature);
                        $signature = str_replace(' ::', ' ', $signature);
                        $group = str_replace(':', '_', "exc_$namespace::$class::$method");

                        if (count($exceptions)) {
                            fwrite($fp, " * @defgroup $group Exceptions\n * @{\n");
                            $exceptions = array_unique($exceptions, SORT_REGULAR);
                            foreach ($exceptions as $exception) {
                                fwrite($fp, ' *     @throws '."${exception[0]} ${exception[1]}\n");
                            }
                            fwrite($fp, " * @}\n *\n");
                        }

                        if (count($exceptions) || $extends !== null || count($implements)) {
                            fwrite($fp, " * @fn $namespace::$class::$method($signature)\n");
                            fwrite($fp, " * @{\n");

                            if (count($exceptions)) {
                                fwrite($fp, " *     @copydetails $group\n");
                            }

                            if ($extends !== '') {
                                fwrite($fp, " *     @copydetails exc_${extends}__$method\n");
                            }

                            foreach ($implements as $implement) {
                                fwrite($fp, " *     @copydetails exc_${implement}__$method\n");
                            }

                            fwrite($fp, " * @}\n *\n");
                        }

                        $method = null;
                        $signature = '';
                        $exceptions = array();
                    }
                }
            } elseif ($braces === 0) {
                if ($token === '(') {
                    $parens++;
                } elseif ($token === ')') {
                    $parens--;
                } elseif ($parens > 0) {
                    $signature .= is_array($token) ? $token[1] : $token;
                }
            }
        }

        if (!is_array($token)) {
            continue;
        }

        if ($token[0] === T_NAMESPACE) {
            $j = $i + 1;
            $namespace = '';
            while ($tokens[$j] !== ';') {
                if (is_array($tokens[$j])) {
                    if ($tokens[$j][0] === T_NS_SEPARATOR) {
                        $namespace .= '::';
                    } elseif ($tokens[$j][0] === T_STRING) {
                        $namespace .= $tokens[$j][1];
                    }
                }
                $j++;
            }
            $namespace = ltrim($namespace, ':');
        } elseif ($token[0] === T_CLASS) {
            $j = $i + 1;
            while (!is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) {
                if ($tokens[$j] === null) {
                    break;
                }
                $j++;
            }
            $class = $tokens[$j][1];
            $extends = '';
            $implements = array();
        } elseif ($token[0] === T_EXTENDS) {
            $j = $i + 1;
            $extends = '';

            while ($tokens[$j] !== '{' && (!is_array($tokens[$j]) || $tokens[$j][0] !== T_IMPLEMENTS)) {
                if (is_array($tokens[$j]) &&
                    in_array($tokens[$j][0], array(T_STRING, T_NS_SEPARATOR), true)) {
                    $extends .= ($tokens[$j][0] === T_NS_SEPARATOR) ? '__' : $tokens[$j][1];
                }
                $j++;
            }
            $extends = ltrim($extends, '_');
        } elseif ($token[0] === T_IMPLEMENTS) {
            $j = $i + 1;
            $cls = '';
            while ($tokens[$j] !== '{') {
                if ($tokens[$j] === ',') {
                    $implements[] = $cls;
                    $cls = '';
                    $j++;
                    continue;
                }
                if (is_array($tokens[$j]) &&
                    in_array($tokens[$j][0], array(T_STRING, T_NS_SEPARATOR), true)) {
                    $cls .= ($tokens[$j][0] === T_NS_SEPARATOR) ? '__' : $tokens[$j][1];
                }
                $j++;
            }
            $cls = ltrim($cls, '_');
            if ($cls !== '') {
                $implements[] = $cls;
            }
        } elseif ($token[0] === T_FUNCTION) {
            $j = $i + 1;
            while (!is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) {
                if ($tokens[$j] === null) {
                    break;
                }
                $j++;
            }
            $method = $tokens[$j][1];
            $braces = 0;
        } elseif ($token[0] === T_THROW) {
            $j = $i + 1;
            $excClass = '';
            $comment = '';
            $parens = 0;

            while (!is_array($tokens[$j]) || $tokens[$j][0] !== T_NEW) {
                $j++;
            }
            $j++;

            while ($tokens[$j] !== ';') {
                if ($tokens[$j] === '(') {
                    $parens++;
                    $j++;
                    continue;
                } elseif ($tokens[$j] === ')') {
                    if ($parens === 0) {
                        throw new \RuntimeException();
                    } else {
                        $parens--;
                        $j++;
                        continue;
                    }
                }
                if ($parens === 0) {
                    $excClass .= is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
                } else {
                    $comment .= is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
                }
                $j++;
            }

            $excClass = trim($excClass);
            if ($excClass !== '' && $excClass[0] !== '$') {
                $excClass = str_replace('\\', '::', ltrim($excClass, '\\'));
                $exceptions[] = array($excClass, $comment);
            }
        }
    }
}

fwrite($fp, " */\n");
fclose($fp);

