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

    final public function __construct($key)
    {
        $cls = __NAMESPACE__ . '\\' . static::HASH;
        $this->_subhash = new $cls($key);
    }

    final static public function getName()
    {
        $cls = __NAMESPACE__ . '\\' . static::HASH;
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

    final public function getSize()
    {
        return 96;
    }
}

