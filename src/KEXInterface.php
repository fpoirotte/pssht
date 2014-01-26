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

interface KEXInterface
{
    static public function getName();

    static public function getGenerator();

    static public function getPrime();

    public function hash($data);
}

