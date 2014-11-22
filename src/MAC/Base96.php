<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\MAC;

use fpoirotte\Pssht\MACInterface;
use fpoirotte\Pssht\AvailabilityInterface;
use fpoirotte\Pssht\MAC\Base96Interface;

/**
 * Abstract base class for a MAC truncated
 * after 96 bits.
 */
abstract class Base96 implements
    MACInterface,
    AvailabilityInterface,
    Base96Interface
{
    /// Subhash performing the real MAC operation.
    protected $subhash;

    final public function __construct($key)
    {
        $cls = static::getBaseClass();
        $this->subhash = new $cls($key);
    }

    public static function getName()
    {
        $cls = static::getBaseClass();
        return $cls::getName() . '-96';
    }

    final public function compute($data)
    {
        return substr(
            $this->subhash->compute($data),
            0,
            $this->getSize() >> 3
        );
    }

    final public static function getSize()
    {
        return 96 >> 3;
    }

    final public static function isAvailable()
    {
        $cls = static::getBaseClass();
        if ($cls instanceof AvailabilityInterface) {
            return $cls::isAvailable();
        }
        return true;
    }
}
