<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\MAC;

use Clicky\Pssht\MACInterface;

class       MD5
implements  MACInterface
{
    protected $_key;

    public function __construct($key)
    {
        $this->_key = $key;
    }

    static public function getName()
    {
        return 'hmac-md5';
    }

    public function compute($data)
    {
        return hash_hmac('md5', $data, $this->_key, TRUE);
    }

    static public function getSize()
    {
        return 128 >> 3;
    }
}

