<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Random;

class Fixed implements \Clicky\Pssht\RandomInterface
{
    protected $data;

    public function __construct($data)
    {
        if (!is_string($data) || strlen($data) === 0) {
            throw new \InvalidArgumentException();
        }

        $this->data = $data;
    }

    public function getBytes($count)
    {
        if (!is_int($count) || $count <= 0) {
            throw new \InvalidArgumentException();
        }

        return substr(
            str_repeat($this->data, (int) ($count / strlen($this->data) + 1)),
            0,
            $count
        );
    }
}
