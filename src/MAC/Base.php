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
use Clicky\Pssht\AvailabilityInterface;
use Clicky\Pssht\MAC\BaseInterface;

abstract class  Base
implements      MACInterface,
                AvailabilityInterface,
                BaseInterface
{
    protected $_key;

    final public function __construct($key)
    {
        $this->_key = $key;
    }

    final public function compute($data)
    {
        $cls = get_called_class();
        return hash_hmac($cls::getHash(), $data, $this->_key, TRUE);
    }

    final static public function getSize()
    {
        $cls = get_called_class();
        return strlen(hash($cls::getHash(), '', TRUE));
    }

    final static public function isAvailable()
    {
        if (!function_exists('hash_algos') ||
            !function_exists('hash') ||
            !function_exists('hash_hmac'))
            return FALSE;
        $cls = get_called_class();
        return in_array($cls::getHash(), hash_algos(), TRUE);
    }
}

