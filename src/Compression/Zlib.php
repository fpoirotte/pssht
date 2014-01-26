<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Compression;

use Clicky\Pssht\CompressionInterface;
use \HttpInflateStream;
use \HttpDeflateStream;

class       Zlib
implements  CompressionInterface
{
    protected $_stream;

    public function __construct($mode)
    {
        if ($mode == self::MODE_COMPRESS) {
            $this->_stream = HttpDeflateStream::factory(
                HttpDeflateStream::TYPE_ZLIB |
                HttpDeflateStream::LEVEL_DEF |
                HttpDeflateStream::FLUSH_SYNC
            );
        }
        else {
            $this->_stream = HttpInflateStream::factory();
        }
    }

    static public function isAvailable()
    {
        return  class_exists('HttpDeflateStream') &&
                class_exists('HttpInflateStream');
    }

    static public function getName()
    {
        return 'zlib';
    }

    public function update($data)
    {
        return $this->_stream->update($data);
    }
}

