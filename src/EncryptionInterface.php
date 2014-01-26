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

interface EncryptionInterface
{
    public function __construct($iv, $key);

    static public function getName();

    static public function getKeySize();

    static public function getIVSize();

    static public function getBlockSize();

    public function encrypt($data);

    public function decrypt($data);
}

