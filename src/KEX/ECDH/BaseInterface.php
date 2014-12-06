<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\KEX\ECDH;

/**
 * Interface for Elliptic Curve Diffie-Hellman
 * key exchange as defined in RFC 5656.
 */
interface BaseInterface
{
    public static function getHashName();
}
