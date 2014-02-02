<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\MAC;

use Clicky\Pssht\MACInterface;

class None implements MACInterface
{
    public function __construct($key)
    {
    }

    public static function getName()
    {
        return 'none';
    }

    public function compute($data)
    {
        return '';
    }

    public static function getSize()
    {
        return 0;
    }
}
