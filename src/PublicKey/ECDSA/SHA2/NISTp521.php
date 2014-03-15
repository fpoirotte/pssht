<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\PublicKey\ECDSA\SHA2;

/**
 * Public key using the Elliptic Curve Digital Signature Algorithm (ECDSA)
 * with the NIST P-521 elliptic curve.
 */
class NISTp521 extends \Clicky\Pssht\PublicKey\ECDSA\SHA2\Base
{
    public static function getHash()
    {
        return 'sha521';
    }

    public static function getIdentifier()
    {
        return 'nistp521';
    }
}
