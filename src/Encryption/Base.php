<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Encryption;

use fpoirotte\Pssht\EncryptionInterface;
use fpoirotte\Pssht\AvailabilityInterface;
use fpoirotte\Pssht\Encryption\BaseInterface;

/**
 * Abstract base for encryption ciphers.
 */
abstract class Base implements
    EncryptionInterface,
    AvailabilityInterface,
    BaseInterface
{
    /// mcrypt handle for the cipher.
    protected $mcrypt;

    public function __construct($iv, $key)
    {
        $this->mcrypt = mcrypt_module_open(
            constant(static::getAlgorithm()),
            '',
            static::getMode(),
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
        if (!extension_loaded('mcrypt')) {
            return false;
        }

        if (!defined(static::getAlgorithm())) {
            return false;
        }
        $res = @mcrypt_module_open(
            constant(static::getAlgorithm()),
            '',
            static::getMode(),
            ''
        );
        if ($res !== false) {
            mcrypt_module_close($res);
        }
        return (bool) $res;
    }

    final public static function getIVSize()
    {
        return mcrypt_get_iv_size(
            constant(static::getAlgorithm()),
            static::getMode()
        );
    }

    final public static function getBlockSize()
    {
        return mcrypt_get_block_size(
            constant(static::getAlgorithm()),
            static::getMode()
        );
    }

    final public function encrypt($seqno, $data)
    {
        return mcrypt_generic($this->mcrypt, $data);
    }

    final public function decrypt($seqno, $data)
    {
        return mdecrypt_generic($this->mcrypt, $data);
    }

    public static function getAlgorithm()
    {
        $algo = strtoupper(substr(strrchr(get_called_class(), '\\'), 1));
        return 'MCRYPT_' . $algo;
    }

    public static function getMode()
    {
        $cls    = explode('\\', get_called_class());
        $mode   = strtolower($cls[count($cls) - 2]);
        return $mode;
    }

    public static function getName()
    {
        $algo = strtolower(substr(strrchr(get_called_class(), '\\'), 1));
        return $algo . '-' . static::getMode();
    }
}
