<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption\OpensshCom;

/**
 * AES-GCM with a 256-bit key.
 */
class AES256GCM extends \fpoirotte\Pssht\Encryption\OpensshCom\AES128GCM
{
    public static function getName()
    {
        return 'aes256-gcm@openssh.com';
    }

    public static function getKeySize()
    {
        return 256 >> 3;
    }
}
