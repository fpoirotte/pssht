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

use Clicky\Pssht\Wire\Exception;
use Clicky\Pssht\Buffer;

class Encoder
{
    protected $_buffer;

    public function __construct(Buffer $buffer = NULL)
    {
        if ($buffer === NULL)
            $buffer = new Buffer();
        $this->_buffer = $buffer;
    }

    public function getBuffer()
    {
        return $this->_buffer;
    }

    protected function _write($value)
    {
        $this->_buffer->push($value);
    }

    public function encode_bytes($value)
    {
        $this->_write($value);
    }

    public function encode_boolean($value)
    {
        return $this->encode_bytes(((bool) $value) ? "\x01" : "\x00");
    }

    public function encode_uint32($value)
    {
        return $this->encode_bytes(pack('N', $value));
    }

    public function encode_uint64($value)
    {
        $res = gmp_div_qr($value, "0x100000000");
        return $this->encode_bytes(
            $this->encode_uint32(gmp_intval($res[0])) .
            $this->encode_uint32(gmp_intval($res[1]))
        );
    }

    public function encode_string($value)
    {
        return $this->encode_bytes(
            $this->encode_uint32(strlen($value)) .
            $this->encode_bytes($value)
        );
    }

    public function encode_mpint($value)
    {
        if (gmp_cmp($value, "0") == 0)
            return $this->encode_string('');
        $s = gmp_strval($value, 16);
        $s = pack('H*', str_pad($s, ((strlen($s) + 1) >> 1) << 1, '0', STR_PAD_LEFT));
        // Positive numbers where the most significant bit
        // in the first byte is set must be preceded with \x00
        // to distinguish them from negative numbers.
        if ((ord($s[0]) & 0x80) && gmp_sign($value) > 0)
            $s = "\x00" . $s;
        return $this->encode_string($s);
    }

    public function encode_name_list(array $values)
    {
        $s = implode(',', $values);
        if ($s === '')
            return $this->encode_uint32(0);

        if (addcslashes($s, "\x80..\xFF") !== $s)
            throw new Exception();

        // The names in the list MUST NOT be empty.
        if ($s[0] === ',' || substr($s, -1) === ',' ||
            strpos($s, ',,') !== FALSE)
            throw new Exception();

        return $this->encode_string($s);
    }
}

