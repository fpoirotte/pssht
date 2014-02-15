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
 * Interface for a (de)compression algorithm.
 */
interface CompressionInterface extends AlgorithmInterface
{
    /// Use the algorithm for compression.
    const MODE_COMPRESS     = 0;

    /// Use the algorithm for decompression.
    const MODE_UNCOMPRESS   = 1;

    /**
     * Construct a (de)compression algorithm.
     *
     *  \param opaque $mode
     *      Mode in which the algorithm is being used.
     *      Either CompressionInterface::MODE_COMPRESS
     *      or CompressionInterface::MODE_UNCOMPRESS.
     */
    public function __construct($mode);

    /**
     * Get the mode in which the algorithm
     * is being used.
     *
     *  \retval opaque
     *      Either CompressionInterface::MODE_COMPRESS
     *      or CompressionInterface::MODE_UNCOMPRESS.
     */
    public function getMode();

    /**
     * Add data to (de)compress.
     *
     *  \param string $data
     *      Additional data to compress or decompress,
     *      depending on the algorithm's mode.
     *
     *  \retval string
     *      (Un)compressed data.
     */
    public function update($data);
}
