<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\MAC\OpensshCom\EtM;

class SHA1 extends \Clicky\Pssht\MAC\SHA1 implements EtMInterface
{
    public static function getName()
    {
        return 'hmac-sha1-etm@openssh.com';
    }
}
