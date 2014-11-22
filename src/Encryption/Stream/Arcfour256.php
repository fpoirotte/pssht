<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption\Stream;

/**
 * Arcfour cipher in stream mode with a 256-bit key
 * and 1536 discarded bytes (defined in RFC 4345).
 */
class Arcfour256 extends \fpoirotte\Pssht\Encryption\Base
{
    public function __construct($iv, $key)
    {
        parent::__construct($iv, $key);
        // See section 4 of RFC 4345 for the rationale.
        $this->encrypt(str_repeat(' ', 1536));
    }

    public static function getAlgorithm()
    {
        return 'MCRYPT_ARCFOUR';
    }

    public static function getName()
    {
        return 'arcfour256';
    }

    public static function getKeySize()
    {
        return 256 >> 3;
    }
}
