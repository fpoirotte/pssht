<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\MAC;

/**
 * Alias for the "hmac-ripemd160" MAC algorithm.
 *
 * \note
 *      This alias only exists for compatibility
 *      with other SSH implementations where this
 *      name is used.
 */
class RawRIPEMD160 extends \fpoirotte\Pssht\MAC\RIPEMD160
{
    public static function getName()
    {
        return 'ripemd160';
    }

    public static function getHash()
    {
        return 'ripemd160';
    }
}
