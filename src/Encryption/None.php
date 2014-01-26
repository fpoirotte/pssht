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

use Clicky\Pssht\EncryptionInterface;

class       None
implements  EncryptionInterface
{
    public function __construct($iv, $key)
    {
    }

    static public function getName()
    {
        return 'none';
    }

    static public function getKeySize()
    {
        return 0;
    }

    static public function getIVSize()
    {
        return 0;
    }

    static public function getBlockSize()
    {
        return 0;
    }

    public function encrypt($data)
    {
        return $data;
    }

    public function decrypt($data)
    {
        return $data;
    }
}

