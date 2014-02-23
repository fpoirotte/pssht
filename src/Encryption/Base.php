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
        if (!defined(static::getAlgorithm()) || !defined(static::getMode())) {
            return false;
        }
        $res = @mcrypt_module_open(
            constant(static::getAlgorithm()),
            '',
            constant(static::getMode()),
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

    public static function getAlgorithm()
    {
        $algo = strtoupper(substr(strrchr(get_called_class(), '\\'), 1));
        return 'MCRYPT_' . $algo;
    }

    public static function getMode()
    {
        $cls    = explode('\\', get_called_class());
        $mode   = strtoupper($cls[count($cls) - 2]);
        return 'MCRYPT_MODE_' . $mode;
    }

    public static function getName()
    {
        $algo = strtolower(substr(strrchr(get_called_class(), '\\'), 1));
        return $algo . '-' . strtolower(substr(static::getMode(), 12));
    }
}
