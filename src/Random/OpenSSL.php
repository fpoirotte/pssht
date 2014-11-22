<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Random;

/**
 * OpenSSL-based pseudo-random number generator.
 */
class OpenSSL implements \fpoirotte\Pssht\RandomInterface
{
    /// Construct a new PRNG.
    public function __construct()
    {
    }

    public function getBytes($count)
    {
        if (!is_int($count) || $count <= 0) {
            throw new \InvalidArgumentException();
        }

        $value = openssl_random_pseudo_bytes($count, $strong);

        /// @FIXME: warn user or tweak the value for crypto-weak values
        if (!$strong) {
            ;
        }
        return $value;
    }
}
