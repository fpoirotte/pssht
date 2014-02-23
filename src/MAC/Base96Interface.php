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

/**
 * Interface for MAC algorithms where the output
 * is truncated after 96 bits.
 */
interface Base96Interface
{
    /**
     * Return the name of the class wrapped by this method
     * and implementing the real MAC algorithm to use.
     *
     *  \retval string
     *      Name of a base class to use to generate MACs.
     *
     *  \note
     *      The base class returned by this method must implement
     *      the \\Clicky\\Pssht\\MACInterface interface.
     */
    public static function getBaseClass();
}
