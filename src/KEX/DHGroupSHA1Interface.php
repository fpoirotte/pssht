<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\KEX;

/**
 * Abstract class for Diffie-Hellman key exchange
 * with SHA-1 as HASH; intended for groups defined
 * in RFC 4253.
 */
interface DHGroupSHA1Interface
{
    /**
     * Get the generator to use for key exchange.
     *
     *  \retval int
     *      Key exchange generator.
     */
    public static function getGenerator();

    /**
     * Get the prime number to use for key exchange.
     *
     *  \retval resource
     *      GMP resource with the prime number
     *      to use for key exchange.
     */
    public static function getPrime();
}
