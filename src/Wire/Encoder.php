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

class Encoder
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

    protected function write($value)
    {
        $this->buffer->push($value);
    }

    public function encodeBytes($value)
    {
        $this->write($value);
    }

    public function encodeBoolean($value)
    {
        return $this->encodeBytes(((bool) $value) ? "\x01" : "\x00");
    }

    public function encodeUint32($value)
    {
        return $this->encodeBytes(pack('N', $value));
    }

    public function encodeUint64($value)
    {
        $s = gmp_strval($value, 16);
        $s = pack('H*', str_pad($s, ((strlen($s) + 1) >> 1) << 1, '0', STR_PAD_LEFT));
        return $this->encodeBytes(pack('H*', $s));
    }

    public function encodeString($value)
    {
        return $this->encodeBytes(
            $this->encodeUint32(strlen($value)) .
            $this->encodeBytes($value)
        );
    }

    public function encodeMpint($value)
    {
        if (gmp_cmp($value, "0") == 0) {
            return $this->encodeString('');
        }
        $s = gmp_strval($value, 16);
        $s = pack('H*', str_pad($s, ((strlen($s) + 1) >> 1) << 1, '0', STR_PAD_LEFT));
        // Positive numbers where the most significant bit
        // in the first byte is set must be preceded with \x00
        // to distinguish them from negative numbers.
        if ((ord($s[0]) & 0x80) && gmp_sign($value) > 0) {
            $s = "\x00" . $s;
        }
        return $this->encodeString($s);
    }

    public function encodeNameList(array $values)
    {
        $s = implode(',', $values);
        if ($s === '') {
            return $this->encodeUint32(0);
        }

        if (addcslashes($s, "\x80..\xFF") !== $s) {
            throw new \InvalidArgumentException();
        }

        // The names in the list MUST NOT be empty.
        if ($s[0] === ',' || substr($s, -1) === ',' ||
            strpos($s, ',,') !== false) {
            throw new \InvalidArgumentException();
        }

        return $this->encodeString($s);
    }
}
