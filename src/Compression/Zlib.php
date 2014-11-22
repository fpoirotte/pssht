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

use \HttpInflateStream;
use \HttpDeflateStream;

/**
 * ZLIB compression.
 */
class Zlib implements
    \fpoirotte\Pssht\CompressionInterface,
    \fpoirotte\Pssht\AvailabilityInterface
{
    /// Compression/decompression stream.
    protected $stream;

    /// Compression/decompression mode.
    protected $mode;

    public function __construct($mode)
    {
        if ($mode == self::MODE_COMPRESS) {
            $this->stream = HttpDeflateStream::factory(
                HttpDeflateStream::TYPE_ZLIB |
                HttpDeflateStream::LEVEL_DEF |
                HttpDeflateStream::FLUSH_SYNC
            );
        } else {
            $this->stream = HttpInflateStream::factory();
        }
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public static function isAvailable()
    {
        return  class_exists('HttpDeflateStream') &&
                class_exists('HttpInflateStream');
    }

    public static function getName()
    {
        return 'zlib';
    }

    public function update($data)
    {
        return $this->stream->update($data);
    }
}
