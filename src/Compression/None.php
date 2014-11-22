<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Compression;

use fpoirotte\Pssht\CompressionInterface;

/**
 * Null compression (= no compression).
 */
class None implements CompressionInterface
{
    /// Compression/decompression mode.
    protected $mode;

    public function __construct($mode)
    {
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public static function getName()
    {
        return 'none';
    }

    public function update($data)
    {
        return $data;
    }
}
