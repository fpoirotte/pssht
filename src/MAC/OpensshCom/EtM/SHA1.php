<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\MAC\OpensshCom\EtM;

/**
 * MAC generation using the SHA1 hash in Encrypt-then-MAC mode.
 */
class SHA1 extends \fpoirotte\Pssht\MAC\SHA1 implements EtMInterface
{
    public static function getName()
    {
        return 'hmac-sha1-etm@openssh.com';
    }
}
