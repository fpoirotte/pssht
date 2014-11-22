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
 * MAC generation using the MD5 hash in Encrypt-then-MAC mode.
 */
class MD5 extends \fpoirotte\Pssht\MAC\MD5 implements EtMInterface
{
    public static function getName()
    {
        return 'hmac-md5-etm@openssh.com';
    }
}
