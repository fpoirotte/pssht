<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\MAC\OpensshCom\UMAC;

class Len64 extends Base
{
    final public function __construct($key)
    {
        $this->key  = $key;
        $this->umac = new \fpoirotte\Pssht\Algorithms\UMAC\UMAC64();
    }

    final public static function getKeySize()
    {
        return 16;
    }

    final public static function getSize()
    {
        return 8;
    }

    public static function getName()
    {
        return 'umac-64@openssh.com';
    }
}
