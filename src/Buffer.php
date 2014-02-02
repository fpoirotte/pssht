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

class Buffer implements \Countable
{
    protected $data;

    public function __construct($data = '')
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        $this->data = $data;
    }

    public function count()
    {
        return strlen($this->data);
    }

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

    public function unget($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        $this->data = $data . $this->data;
        return $this;
    }

    public function push($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException();
        }

        $this->data .= $data;
        return $this;
    }
}
