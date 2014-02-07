<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\MAC\EtM\SHA2;

use \Clicky\Pssht\MAC\EtM\EtMInterface;

class Len256 extends \Clicky\Pssht\MAC\SHA2\Len256 implements EtMInterface
{
    public static function getName()
    {
        return 'hmac-sha2-256-etm@openssh.com';
    }
}
