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

use fpoirotte\Pssht\MAC\MACInterface;
use fpoirotte\Pssht\Algorithms\AvailabilityInterface;
use fpoirotte\Pssht\MAC\BaseInterface;

/**
 * Abstract class representing a MAC algorithm.
 */
abstract class Base implements
    MACInterface,
    AvailabilityInterface,
    BaseInterface
{
    /// Secret key for MAC operations.
    protected $key;

    final public function __construct($key)
    {
        $this->key = $key;
    }

    final public function compute($seqno, $data)
    {
        $cls = get_called_class();
        return hash_hmac($cls::getHash(), pack('N', $seqno) . $data, $this->key, true);
    }

    final public static function getKeySize()
    {
        $cls = get_called_class();
        return strlen(hash($cls::getHash(), '', true));
    }

    final public static function getSize()
    {
        return static::getKeySize();
    }

    final public static function isAvailable()
    {
        if (!function_exists('hash_algos') ||
            !function_exists('hash') ||
            !function_exists('hash_hmac')) {
            return false;
        }
        $cls = get_called_class();
        return in_array($cls::getHash(), hash_algos(), true);
    }
}
