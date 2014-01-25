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

class       SHA1
implements  MACInterface
{
    protected $_key;

    public function __construct($key)
    {
        $this->_key = $key;
    }

    static public function getName()
    {
        return 'hmac-sha1';
    }

    public function compute($data)
    {
        return hash_hmac('sha1', $data, $this->_key, TRUE);
    }

    static public function getSize()
    {
        return 160 >> 3;
    }
}

