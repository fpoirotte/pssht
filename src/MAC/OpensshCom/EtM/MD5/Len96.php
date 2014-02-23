<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\MAC\OpensshCom\EtM\MD5;

use \Clicky\Pssht\MAC\OpensshCom\EtM\EtMInterface;

/**
 * MAC generation using a truncated MD5 hash in Encrypt-then-MAC mode.
 */
class Len96 extends \Clicky\Pssht\MAC\MD5\Len96 implements EtMInterface
{
    public static function getName()
    {
        return 'hmac-md5-96-etm@openssh.com';
    }
}
