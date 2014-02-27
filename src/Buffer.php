<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht;

/**
 * Generic buffer.
 */
class Buffer implements \Countable
{
    /// The buffer's current data.
    protected $data;

    /**
     * Construct a new buffer.
     *
     *  \param string $data
     *      (optional) Initial data for the buffer.
     *      If omitted, the buffer is initialized empty.
     */
    public function __construct($data = '')
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        $this->data = $data;
    }

    /**
     * Return the size (in bytes) of the data
     * currently stored in the buffer.
     *
     *  \retval int
     *      The size of the buffer's current data.
     */
    public function count()
    {
        return strlen($this->data);
    }

    /**
     * Return a limited amount of data from the buffer.
     *
     *  \param int $limit
     *      Number of bytes to retrieve from the buffer.
     *
     *  \retval string
     *      Exactly $limit bytes of data from the buffer.
     *
     *  \retval null
     *      The buffer contains less than $limit bytes
     *      of data.
     */
    protected function getLength($limit)
    {
        $size = strlen($this->data);
        if ($limit <= 0) {
            $limit += $size;
        }

        if ($limit > $size) {
            return null;
        }

        $res = (string) substr($this->data, 0, $limit);
        $this->data = (string) substr($this->data, $limit);
        return $res;
    }

    /**
     * Return a delimited string from the buffer.
     *
     *  \param string $limit
     *      Delimiter.
     *
     *  \retval string
     *      All the data at the beginning of the buffer
     *      up to (and including) the delimiter.
     *
     *  \retval null
     *      The given delimiter does not appear
     *      in the buffer.
     */
    protected function getDelimiter($limit)
    {
        if ($limit === '') {
            throw new \InvalidArgumentException();
        }

        $pos = strpos($this->data, $limit);
        if ($pos === false) {
            return null;
        }

        $pos += strlen($limit);
        $res = substr($this->data, 0, $pos);
        $this->data = (string) substr($this->data, $pos);
        return $res;
    }

    /**
     * Get limited data from the beginning of the buffer.
     *
     *  \param int|string $limit
     *      Either the number of bytes to retrieve
     *      from the buffer, or a string delimiter
     *      to look for.
     *
     *  \retval string
     *      The data at the beginning of the buffer, until the given
     *      limit is reached. For string delimiters, the delimiter
     *      is part of the result.
     */
    public function get($limit)
    {
        if (is_int($limit)) {
            return $this->getLength($limit);
        }

        if (is_string($limit)) {
            return $this->getDelimiter($limit);
        }

        throw new \InvalidArgumentException();
    }

    /**
     * Prepend data to the beginning of the buffer.
     *
     *  \param string $data
     *      Data to prepend.
     *
     *  \retval Buffer
     *      Returns this buffer.
     */
    public function unget($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        $this->data = $data . $this->data;
        return $this;
    }

    /**
     * Append data to the end of the buffer.
     *
     *  \param string $data
     *      Data to append.
     *
     *  \retval Buffer
     *      Returns this buffer.
     */
    public function push($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        $this->data .= $data;
        return $this;
    }
}
