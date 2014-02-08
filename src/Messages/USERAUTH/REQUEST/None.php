<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\USERAUTH\REQUEST;

use Clicky\Pssht\Wire\Encoder;
use Clicky\Pssht\Wire\Decoder;

class None extends \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base
{
    protected static function unserializeSub(Decoder $decoder)
    {
        return array();
    }
}
