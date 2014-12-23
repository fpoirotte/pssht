<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption\OpensshCom;

/**
 * AES cipher in CBC mode with a 256-bit key
 * (alias for "aes256-cbc").
 */
class Rijndael extends \fpoirotte\Pssht\Encryption\CBC\AES256
{
    public static function getMode()
    {
        return 'cbc';
    }

    public static function getName()
    {
        return 'rijndael-cbc@lysator.liu.se';
    }
}
