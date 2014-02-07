<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\MAC\EtM;

class RIPEMD160 extends \Clicky\Pssht\MAC\RIPEMD160 implements EtMInterface
{
    public static function getName()
    {
        return 'hmac-ripemd160-etm@openssh.com';
    }
}
