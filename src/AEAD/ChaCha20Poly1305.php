<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\AEAD;

/**
 * ChaCha20 & Poly1305 combined to achieve AEAD.
 */
class ChaCha20Poly1305
{
    protected $header;
    protected $main;

    public function __construct($key)
    {
        $this->main     = new \fpoirotte\Pssht\ChaCha20(substr($key, 0, 32));
        $this->header   = new \fpoirotte\Pssht\ChaCha20(substr($key, 32));
    }

    public function ae($IV, $P, $A)
    {
        $polyKey    = $this->main->encrypt(str_repeat("\x00", 32), $IV, 0);
        $poly       = new \fpoirotte\Pssht\Poly1305($polyKey);
        $aad        = $this->header->encrypt($A, $IV, 0);
        $res        = $this->main->encrypt($P, $IV, 1);
        return $aad . $res . $poly->mac($aad . $res);
    }

    public function ad($IV, $C, $A, $T)
    {
        $len = $this->header->decrypt($A, $IV, 0);
        if ($C === null && $T === null) {
            return $len;
        }

        $polyKey    = $this->main->encrypt(str_repeat("\x00", 32), $IV, 0);
        $poly       = new \fpoirotte\Pssht\Poly1305($polyKey);
        if ($poly->mac($A . $C) !== $T) {
            return null;
        }
        return $this->main->decrypt($C, $IV, 1);
    }
}
