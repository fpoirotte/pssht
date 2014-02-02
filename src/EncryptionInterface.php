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

    public static function getName();

    public static function getKeySize();

    public static function getIVSize();

    public static function getBlockSize();

    public function encrypt($data);

    public function decrypt($data);
}
