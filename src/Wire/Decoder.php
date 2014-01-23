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

class Decoder
{
    protected $_buffer;

    public function __construct(Buffer $buffer)
    {
        $this->_buffer = $buffer;
    }

    public function getBuffer()
    {
        return $this->_buffer;
    }

    public function decode_bytes($count = 1)
    {
        if (!is_int($count) || $count < 0)
            throw new Exception();
        if (!$count)
            return '';

        return $this->_buffer->get($count);
    }

    public function decode_boolean()
    {
        $value = $this->decode_bytes();
        if ($value === NULL)
            return NULL;
        return ($value !== "\0");
    }

    public function decode_uint32()
    {
        $value = $this->decode_bytes(4);
        if ($value === NULL)
            return NULL;
        $res = unpack('N', $value);
        return array_pop($res);
    }

    public function decode_uint64()
    {
        $value = $this->decode_bytes(8);
        if ($value === NULL)
            return NULL;
        return gmp_init($value);
    }

    public function decode_string()
    {
        $len = $this->decode_uint32();
        if ($len === NULL)
            return NULL;
        $value = $this->decode_bytes($len);
        if ($value === NULL) {
            $this->_buffer->unget(pack('N', $len));
            return NULL;
        }
        return $value;
    }

    public function decode_mpint()
    {
        $s = $this->decode_string();
        if ($s === NULL)
            return NULL;

        if ($s === '')
            return gmp_init(0);

        $n = gmp_init(bin2hex($s), 16);

        // Negative numbers: decode using two-complement.
        if (ord($s[0]) & 0x80) {
            // gmp_com() uses the number's size to compute
            // the complement, which is right in our case.
            $n = gmp_neg(gmp_add(gmp_com($n), "1"));
        }
        return $n;
    }

    public function decode_name_list($validationCallback = NULL)
    {
        $s = $this->decode_string();
        if ($s === NULL)
            return NULL;

        // Empty list.
        if ($s === '')
            $l = array();

        else {
            // All the names in the list MUST be in US-ASCII.
            if (addcslashes($s, "\x80..\xFF") !== $s)
                throw new Exception();

            // The names in the list MUST NOT be empty.
            if ($s[0] === ',' || substr($s, -1) === ',' ||
                strpos($s, ',,') !== FALSE)
                throw new Exception();

            $l = explode(',', $s);
        }

        if ($validationCallback !== NULL)
            call_user_func($validationCallback, $l);
        return $l;
    }
}

