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
 * Interface for a (Pseudo-)Random Number Generator.
 */
interface RandomInterface
{
    /**
     * Get (pseudo-)random bytes.
     *
     *  \param int $count
     *      Number of (pseudo-)random bytes to retrieve.
     *
     *  \retval string
     *      A character string composed of $count
     *      (pseudo-)random bytes.
     */
    public function getBytes($count);
}
