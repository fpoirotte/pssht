<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Encryption;

class   aes128_cbc
extends \Clicky\Pssht\Encryption\Base
{
    static protected function _getMode()
    {
        return 'MCRYPT_MODE_CBC';
    }

    static protected function _getAlgorithm() {
        return 'MCRYPT_RIJNDAEL_128';
    }

    static public function getName()
    {
        return 'aes128-cbc@foo';
    }
}

