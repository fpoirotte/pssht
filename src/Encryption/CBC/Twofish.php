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

class   Twofish
extends \Clicky\Pssht\Encryption\Base
{
    static public function getMode()
    {
        return 'MCRYPT_MODE_CBC';
    }

    static public function getAlgorithm() {
        return 'MCRYPT_TWOFISH';
    }

    static public function getName()
    {
        return 'twofish-cbc';
    }
}

