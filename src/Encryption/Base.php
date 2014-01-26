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

abstract class  Base
implements      EncryptionInterface,
                AvailabilityInterface
{
    protected $_mcrypt;

    abstract static protected function _getMode();

    abstract static protected function _getAlgorithm();

    final public function __construct($iv, $key)
    {
        $this->_mcrypt = mcrypt_module_open(
            constant(static::_getAlgorithm()),
            '',
            constant(static::_getMode()),
            ''
        );
        mcrypt_generic_init($this->_mcrypt, $key, $iv);
    }

    final public function __destruct()
    {
        mcrypt_generic_deinit($this->_mcrypt);
        mcrypt_module_close($this->_mcrypt);
    }

    final static public function isAvailable()
    {
        return defined(static::_getAlgorithm()) && defined(static::_getMode());
    }

    final static public function getKeySize()
    {
        return mcrypt_get_key_size(
            constant(static::_getAlgorithm()),
            constant(static::_getMode())
        );
    }

    final static public function getIVSize()
    {
        return mcrypt_get_iv_size(
            constant(static::_getAlgorithm()),
            constant(static::_getMode())
        );
    }

    final static public function getBlockSize()
    {
        return mcrypt_get_block_size(
            constant(static::_getAlgorithm()),
            constant(static::_getMode())
        );
    }

    final public function encrypt($data)
    {
        return mcrypt_generic($this->_mcrypt, $data);
    }

    final public function decrypt($data)
    {
        return mdecrypt_generic($this->_mcrypt, $data);
    }
}

