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

class IDEA extends \Clicky\Pssht\Encryption\Base
{
    public static function getMode()
    {
        return 'MCRYPT_MODE_CBC';
    }

    public static function getAlgorithm()
    {
        return 'MCRYPT_IDEA';
    }

    public static function getName()
    {
        return 'idea-cbc';
    }
}
