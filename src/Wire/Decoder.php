<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Wire;

class Decoder
{
    protected $buffer;

    public function __construct(\Clicky\Pssht\Buffer $buffer = null)
    {
        if ($buffer === null) {
            $buffer = new \Clicky\Pssht\Buffer();
        }

        $this->buffer = $buffer;
    }

    public function getBuffer()
    {
        return $this->buffer;
    }

    public function decodeBytes($count = 1)
    {
        if (!is_int($count) || $count < 0) {
            throw new \InvalidArgumentException();
        }

        if (!$count) {
            return '';
        }

        return $this->buffer->get($count);
    }

    public function decodeBoolean()
    {
        $value = $this->decodeBytes();
        if ($value === null) {
            return null;
        }
        return ($value !== "\0");
    }

    public function decodeUint32()
    {
        $value = $this->decodeBytes(4);
        if ($value === null) {
            return null;
        }
        $res = unpack('N', $value);
        return array_pop($res);
    }

    public function decodeUint64()
    {
        $value = $this->decodeBytes(8);
        if ($value === null) {
            return null;
        }
        return gmp_init(bin2hex($value), 16);
    }

    public function decodeString()
    {
        $len = $this->decodeUint32();
        if ($len === null) {
            return null;
        }
        $value = $this->decodeBytes($len);
        if ($value === null) {
            $this->buffer->unget(pack('N', $len));
            return null;
        }
        return $value;
    }

    public function decodeMpint()
    {
        $s = $this->decodeString();
        if ($s === null) {
            return null;
        }

        if ($s === '') {
            return gmp_init(0);
        }

        $n = gmp_init(bin2hex($s), 16);

        // Negative numbers: decode using two-complement.
        if (ord($s[0]) & 0x80) {
            // gmp_com() uses the number's size to compute
            // the complement, which is right in our case.
            $n = gmp_neg(gmp_add(gmp_com($n), "1"));
        }
        return $n;
    }

    public function decodeNameList($validationCallback = null)
    {
        $s = $this->decodeString();
        if ($s === null) {
            return null;
        }

        // Empty list.
        if ($s === '') {
            $l = array();
        } else {
            // All the names in the list MUST be in US-ASCII.
            if (addcslashes($s, "\x80..\xFF") !== $s) {
                throw new \InvalidArgumentException();
            }

            // The names in the list MUST NOT be empty.
            if ($s[0] === ',' || substr($s, -1) === ',' ||
                strpos($s, ',,') !== false) {
                throw new \InvalidArgumentException();
            }

            $l = explode(',', $s);
        }

        if ($validationCallback !== null) {
            call_user_func($validationCallback, $l);
        }
        return $l;
    }
}
