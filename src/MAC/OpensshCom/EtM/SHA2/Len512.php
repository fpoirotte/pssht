<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\MAC\OpensshCom\EtM\SHA2;

use \Clicky\Pssht\MAC\OpensshCom\EtM\EtMInterface;

/**
 * MAC generation using an SHA-512 hash in Encrypt-then-MAC mode.
 */
class Len512 extends \Clicky\Pssht\MAC\SHA2\Len512 implements EtMInterface
{
    public static function getName()
    {
        return 'hmac-sha2-512-etm@openssh.com';
    }
}
