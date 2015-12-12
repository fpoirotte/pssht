<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\MAC\OpensshCom\UMAC;

abstract class Base implements
    \fpoirotte\Pssht\MAC\MACInterface,
    \fpoirotte\Pssht\Algorithms\AvailabilityInterface
{
    protected $umac;
    protected $key;

    final public function compute($seqno, $data)
    {
        $encoder    = new \fpoirotte\Pssht\Wire\Encoder();
        $nonce      = $encoder->encodeUint64($seqno)->getBuffer()->get(0);
        $res        = $this->umac->umac($this->key, $data, $nonce);
        return $res;
    }

    final public static function isAvailable()
    {
        if (!extension_loaded('mcrypt')) {
            return false;
        }

        if (!defined('MCRYPT_RIJNDAEL_128')) {
            return false;
        }
        $res = @mcrypt_module_open(
            MCRYPT_RIJNDAEL_128,
            '',
            'ecb',
            ''
        );
        if ($res !== false) {
            mcrypt_module_close($res);
        }
        return (bool) $res;
    }
}
