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

/**
 * ZLIB compression.
 */
class Zlib implements
    \fpoirotte\Pssht\CompressionInterface,
    \fpoirotte\Pssht\AvailabilityInterface
{
    static protected $deflateFactory;
    static protected $inflateFactory;

    /// Compression/decompression stream.
    protected $stream;

    /// Compression/decompression mode.
    protected $mode;

    public function __construct($mode)
    {
        if (self::$deflateFactory === null || self::$inflateFactory === null) {
            throw new \RuntimeException('(De)Compression is not available');
        }

        if ($mode == self::MODE_COMPRESS) {
            list($cls, $method) = self::$deflateFactory;
            $flags =
                $cls::TYPE_ZLIB |
                $cls::LEVEL_DEF |
                $cls::FLUSH_SYNC;
        } else {
            list($cls, $method) = self::$inflateFactory;
            $flags = 0;
        }

        $reflector = new \ReflectionMethod($cls, $method);
        if ($reflector->isConstructor()) {
            $this->stream = new $cls($flags);
        } else {
            $this->stream = $reflector->invoke(null, $flags);
        }
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public static function isAvailable()
    {
        // PECL_HTTP v2.x uses classes in a dedicated namespace
        // and a regular constructor.
        if (class_exists('http\\Encoding\\Stream\\Deflate') &&
            class_exists('\\http\\Encoding\\Stream\\Inflate')) {
            self::$deflateFactory = array('\\http\\Encoding\\Stream\\Deflate', '__construct');
            self::$inflateFactory = array('\\http\\Encoding\\Stream\\Inflate', '__construct');
            return true;
        }
        // PECL_HTTP v1.x uses classes in the global scope
        // and a static factory method.
        if (class_exists('\\HttpDeflateStream') &&
            class_exists('\\HttpInflateStream')) {
            self::$deflateFactory = array('\\HttpDeflateStream', 'factory');
            self::$inflateFactory = array('\\HttpInflateStream', 'factory');
            return true;
        }
        return false;
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
