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

/**
 * Interface for the hashing algorithm
 * to use for MAC purposes.
 */
interface BaseInterface
{
    /**
     * Return the name of the hashing algorithm
     * to use for MAC purposes.
     *
     *  \retval string
     *      Hashing algorithm to use.
     *
     *  \note
     *      The value returned by this method is the name
     *      of one of the algorithms listed by hash_algos().
     */
    public static function getHash();
}
