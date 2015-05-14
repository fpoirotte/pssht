<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Key\ECDSA\SHA2;

/**
 * Base interface for encryption/decryption ciphers.
 */
interface BaseInterface
{
    public static function getHash();
    public static function getIdentifier();
}
