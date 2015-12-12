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
abstract class DHGroupSHA1Base implements
    \fpoirotte\Pssht\KEX\KEXInterface,
    \fpoirotte\Pssht\KEX\DHGroupSHA1Interface
{
    public function hash($data)
    {
        return sha1($data, true);
    }

    public static function addHandlers(\fpoirotte\Pssht\Transport $transport)
    {
        $transport->setHandler(
            \fpoirotte\Pssht\Messages\KEXDH\INIT::getMessageId(),
            new \fpoirotte\Pssht\Handlers\KEXDH\INIT()
        );
    }
}
