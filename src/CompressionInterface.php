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

interface CompressionInterface
{
    const MODE_COMPRESS     = 0;
    const MODE_UNCOMPRESS   = 1;

    public function __construct($mode);

    public static function getName();

    public function update($data);
}
