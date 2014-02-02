<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Encryption;

use Clicky\Pssht\EncryptionInterface;
use Clicky\Pssht\AvailabilityInterface;
use Clicky\Pssht\Encryption\BaseInterface;

abstract class Base implements
    EncryptionInterface,
    AvailabilityInterface,
    BaseInterface
{
    protected $mcrypt;

    final public function __construct($iv, $key)
    {
        $this->mcrypt = mcrypt_module_open(
            constant(static::getAlgorithm()),
            '',
            constant(static::getMode()),
            ''
        );
        mcrypt_generic_init($this->mcrypt, $key, $iv);
    }

    final public function __destruct()
    {
        mcrypt_generic_deinit($this->mcrypt);
        mcrypt_module_close($this->mcrypt);
    }

    final public static function isAvailable()
    {
        return defined(static::getAlgorithm()) && defined(static::getMode());
    }

    final public static function getKeySize()
    {
        return mcrypt_get_key_size(
            constant(static::getAlgorithm()),
            constant(static::getMode())
        );
    }

    final public static function getIVSize()
    {
        return mcrypt_get_iv_size(
            constant(static::getAlgorithm()),
            constant(static::getMode())
        );
    }

    final public static function getBlockSize()
    {
        return mcrypt_get_block_size(
            constant(static::getAlgorithm()),
            constant(static::getMode())
        );
    }

    final public function encrypt($data)
    {
        return mcrypt_generic($this->mcrypt, $data);
    }

    final public function decrypt($data)
    {
        return mdecrypt_generic($this->mcrypt, $data);
    }
}
