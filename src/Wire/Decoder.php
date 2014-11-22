<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Wire;

/**
 * Decode SSH-encoded values (RFC 4251).
 */
class Decoder
{
    /// Buffer the encoded values are read from.
    protected $buffer;

    /**
     * Construct a new decoder.
     *
     *  \param fpoirotte::Pssht::Buffer $buffer
     *      (optional) Buffer to read from.
     *      If omitted, a new empty buffer is used.
     */
    public function __construct(\fpoirotte\Pssht\Buffer $buffer = null)
    {
        if ($buffer === null) {
            $buffer = new \fpoirotte\Pssht\Buffer();
        }

        $this->buffer = $buffer;
    }

    /**
     * Get the buffer associated with this decoder.
     *
     *  \retval fpoirotte::Pssht::Buffer
     *      The buffer associated with this decoder.
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Decode a series of bytes ("byte" type).
     *
     *  \param int $count
     *      (optional) Number of bytes to decode.
     *      Defaults to 1.
     *
     *  \retval string
     *      A string of exactly $count bytes.
     *
     *  \retval null
     *      The buffer did not contain enough data.
     */
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

    /**
     * Decode a boolean value ("boolean" type).
     *
     *  \retval bool
     *      Decoded value.
     *
     *  \retval null
     *      The buffer did not contain enough data.
     */
    public function decodeBoolean()
    {
        $value = $this->decodeBytes();
        if ($value === null) {
            return null;
        }
        return ($value !== "\0");
    }

    /**
     * Decode a 32 bits unsigned value ("uint32" type).
     *
     *  \retval int
     *      Decoded value.
     *
     *  \retval null
     *      The buffer did not contain enough data.
     */
    public function decodeUint32()
    {
        $value = $this->decodeBytes(4);
        if ($value === null) {
            return null;
        }
        $res = unpack('N', $value);
        return array_pop($res);
    }

    /**
     * Decode a 64 bits unsigned value ("uint64" type).
     *
     *  \retval resource
     *      GMP resource representing the decoded
     *      64 bits unsigned value.
     *
     *  \retval null
     *      The buffer did not contain enough data.
     */
    public function decodeUint64()
    {
        $value = $this->decodeBytes(8);
        if ($value === null) {
            return null;
        }
        return gmp_init(bin2hex($value), 16);
    }

    /**
     * Decode a character string ("string" type).
     *
     *  \retval string
     *      Decoded value.
     *
     *  \retval null
     *      The buffer did not contain enough data.
     */
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

    /**
     * Decode an arbitrary precision number ("mpint" type).
     *
     *  \retval resource
     *      GMP resource representing the decoded
     *      arbitrary precision number.
     *
     *  \retval null
     *      The buffer did not contain enough data.
     */
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

    /**
     * Decode a list of names ("name-list" type).
     *
     *  \param callable $validationCallback
     *      (optional) Callback to call with the list as its sole
     *      argument for validation before it is returned.
     *      The callback should throw \\InvalidArgumentException
     *      if any of the values is invalid.
     *      By default, this method already validates the values
     *      using the rules for "name-list" type given in RFC 4251.
     *
     *  \retval array
     *      List of decoded algorithm names.
     *
     *  \retval null
     *      The buffer did not contain enough data.
     */
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
