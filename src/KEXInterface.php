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
 * Interface for a Key Exchange algorithm.
 */
interface KEXInterface extends AlgorithmInterface
{
    /**
     * Get the generator to use for key exchange.
     *
     *  \retval int
     *      Key exchange generator.
     */
    public static function getGenerator();

    /**
     * Get the prime number to use for key exchange.
     *
     *  \retval resource
     *      GMP resource with the primer number
     *      to use for key exchange.
     */
    public static function getPrime();

    /**
     * Hash the given data.
     *
     *  \param string $data
     *      Data to hash.
     *
     *  \retval string
     *      Hash for the given data.
     */
    public function hash($data);
}
