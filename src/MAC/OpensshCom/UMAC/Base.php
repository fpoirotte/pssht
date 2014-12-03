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

abstract class Base implements \fpoirotte\Pssht\MACInterface
{
    protected $umac;
    protected $key;

    final public function compute($seqno, $data)
    {
        $encoder    = new \fpoirotte\Pssht\Wire\Encoder();
        $nonce      = $encoder->encodeUint64($seqno)->getBuffer()->get(0);
        $res        = $this->umac->umac($this->key, $data, $nonce);
        return $res;
    }
}
