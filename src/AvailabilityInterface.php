<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht;

/**
 * Interface for an algorithm whose availability
 * is constrained by external factors.
 */
interface AvailabilityInterface
{
    public static function isAvailable();
}
