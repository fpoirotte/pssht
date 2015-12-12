<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Algorithms;

/**
 * Interface for an algorithm.
 */
interface AlgorithmInterface
{
    /// Return the name of the algorithm.
    public static function getName();
}
