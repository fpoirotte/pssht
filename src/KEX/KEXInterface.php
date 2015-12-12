<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\KEX;

/**
 * Interface for a Key Exchange algorithm.
 */
interface KEXInterface extends \fpoirotte\Pssht\Algorithms\AlgorithmInterface
{
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

    public static function addHandlers(\fpoirotte\Pssht\Transport $transport);
}
