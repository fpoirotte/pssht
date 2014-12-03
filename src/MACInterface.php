<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht;

/**
 * Interface for a MAC algorithm.
 */
interface MACInterface extends AlgorithmInterface
{
    /**
     * Construct a new MAC algorithm.
     *
     *  \param string $key
     *      Key for the MAC algorithm.
     */
    public function __construct($key);

    /**
     * Compute the MAC for some data.
     *
     *  \param int $seqno
     *      Sequence number of the message
     *      for which the MAC applies.
     *
     *  \param string $data
     *      Data whose MAC must be computed.
     *
     *  \retval string
     *      MAC for the given data.
     */
    public function compute($seqno, $data);

    /**
     * Get the size of a MAC signature
     * generated with this algorithm.
     *
     *  \retval int
     *      MAC size in bytes.
     */
    public static function getSize();

    /**
     * Get the size of a key compatible
     * with this MAC algorithm.
     *
     *  \retval int
     *      MAC key size in bytes.
     */
    public static function getKeySize();
}
