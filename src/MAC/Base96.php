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
use Clicky\Pssht\MAC\Base96Interface;

abstract class  Base96
implements      MACInterface,
                AvailabilityInterface,
                Base96Interface
{
    protected $_subhash;

    final public function __construct($key)
    {
        $cls = static::getBaseClass();
        $this->_subhash = new $cls($key);
    }

    final static public function getName()
    {
        $cls = static::getBaseClass();
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

    final static public function isAvailable()
    {
        $cls = static::getBaseClass();
        if ($cls instanceof AvailabilityInterface)
            return $cls::isAvailable();
        return TRUE;
    }
}

