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
 * Elliptic Curve Diffie-Hellman key exchange as defined
 * in RFC 5656, using the "nistp256" curve.
 */
class NISTp256 extends \fpoirotte\Pssht\KEX\ECDH\Base
{
    public static function getHashName()
    {
        return 'sha256';
    }

    public static function getName()
    {
        return 'ecdh-sha2-nistp256';
    }
}
