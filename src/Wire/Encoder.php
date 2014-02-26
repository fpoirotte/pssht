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

/**
 * SSH-encode values (RFC 4251).
 */
class Encoder
{
    /// Buffer where the encoded values will be appended.
    protected $buffer;

    /**
     * Construct a new encoder.
     *
     *  \param Buffer $buffer
     *      (optional) Buffer to write to.
     *      If omitted, a new empty buffer is used.
     */
    public function __construct(\Clicky\Pssht\Buffer $buffer = null)
    {
        if ($buffer === null) {
            $buffer = new \Clicky\Pssht\Buffer();
        }

        $this->buffer = $buffer;
    }

    /**
     * Get the buffer associated with this encoder.
     *
     *  \retval Buffer $buffer
     *      The buffer associated with this encoder.
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Encode raw bytes ("byte" type).
     *
     *  \param string $value
     *      Raw array of bytes.
     *
     *  \retval Encoder
     *      Returns this encoder.
     */
    public function encodeBytes($value)
    {
        $this->buffer->push($value);
        return $this;
    }

    /**
     * Encode a boolean ("boolean" type).
     *
     *  \param string $value
     *      Boolean value to encode.
     *
     *  \retval Encoder
     *      Returns this encoder.
     */
    public function encodeBoolean($value)
    {
        return $this->encodeBytes(((bool) $value) ? "\x01" : "\x00");
    }

    /**
     * Encode a 32 bits unsigned value ("uint32" type).
     *
     *  \param string $value
     *      32 bits unsigned value to encode.
     *
     *  \retval Encoder
     *      Returns this encoder.
     */
    public function encodeUint32($value)
    {
        return $this->encodeBytes(pack('N', $value));
    }

    /**
     * Encode a 64 bits unsigned value ("uint64" type).
     *
     *  \param resource $value
     *      GMP resource representing the 64 bits unsigned value
     *      to encode.
     *
     *  \retval Encoder
     *      Returns this encoder.
     */
    public function encodeUint64($value)
    {
        $s = gmp_strval($value, 16);
        $s = pack('H*', str_pad($s, ((strlen($s) + 1) >> 1) << 1, '0', STR_PAD_LEFT));
        return $this->encodeBytes(pack('H*', $s));
    }

    /**
     * Encode a string ("string" type).
     *
     *  \param string $value
     *      Character string to encode.
     *
     *  \retval Encoder
     *      Returns this encoder.
     */
    public function encodeString($value)
    {
        $this->encodeUint32(strlen($value));
        return $this->encodeBytes($value);
    }

    /**
     * Encode an arbitrary precision number ("mpint" type).
     *
     *  \param resource $value
     *      GMP resource representing the arbitrary precision
     *      number to encode.
     *
     *  \retval Encoder
     *      Returns this encoder.
     */
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

    /**
     * Encode a list of names ("name-list" type).
     *
     *  \param array $values
     *      A list of algorithm names.
     *
     *  \retval Encoder
     *      Returns this encoder.
     */
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
