<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Encryption\CBC;

class AES128 extends \Clicky\Pssht\Encryption\Base
{
    public static function getMode()
    {
        return 'MCRYPT_MODE_CBC';
    }

    public static function getAlgorithm()
    {
        return 'MCRYPT_RIJNDAEL_128';
    }

    public static function getName()
    {
        return 'aes128-cbc@foo';
    }
}
