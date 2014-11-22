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
 * MAC generation using the RIPEMD160 hash in Encrypt-then-MAC mode.
 */
class RIPEMD160 extends \fpoirotte\Pssht\MAC\RIPEMD160 implements EtMInterface
{
    public static function getName()
    {
        return 'hmac-ripemd160-etm@openssh.com';
    }
}
