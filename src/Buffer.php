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

class       Buffer
implements  \Countable
{
    private $_buffer;

    public function __construct($data = '')
    {
        if (!is_string($data))
            throw new \InvalidArgumentException();
        $this->_buffer = $data;
    }

    public function count()
    {
        return strlen($this->_buffer);
    }

    private function _get_length($limit)
    {
        $size = strlen($this->_buffer);
        if ($limit <= 0)
            $limit += $size;
        if ($limit > $size)
            return NULL;
        $res = (string) substr($this->_buffer, 0, $limit);
        $this->_buffer = (string) substr($this->_buffer, $limit);
        return $res;
    }

    private function _get_delim($limit)
    {
        if ($limit === '')
            throw new \InvalidArgumentException();
        $pos = strpos($this->_buffer, $limit);
        if ($pos === FALSE)
            return NULL;
        $pos += strlen($limit);
        $res = substr($this->_buffer, 0, $pos);
        $this->_buffer = (string) substr($this->_buffer, $pos);
        return $res;
    }

    public function get($limit)
    {
        if (is_int($limit))
            return $this->_get_length($limit);
        if (is_string($limit))
            return $this->_get_delim($limit);
        throw new \InvalidArgumentException();
    }

    public function unget($data)
    {
        if (!is_string($data))
            throw new \InvalidArgumentException();
        $this->_buffer = $data . $this->_buffer;
        return $this;
    }

    public function push($data)
    {
        if (!is_string($data))
            throw new \InvalidArgumentException();
        $this->_buffer .= $data;
        return $this;
    }
}

