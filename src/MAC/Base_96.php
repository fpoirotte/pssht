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

abstract class  Base_96
implements      MACInterface
{
    protected $_subhash;

    abstract static protected function _getHash();

    final public function __construct($key)
    {
        $cls = static::_getHash();
        $this->_subhash = new $cls($key);
    }

    final static public function getName()
    {
        $cls = static::_getHash();
        return $cls::getName() . '-96';
    }

    final public function compute($data)
    {
        return substr(
            $this->_subhash->compute($data),
            0,
            $this->getSize() >> 3
        );
    }

    final static public function getSize()
    {
        return 96 >> 3;
    }
}

