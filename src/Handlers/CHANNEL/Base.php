<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Handlers\CHANNEL;

abstract class Base implements \Clicky\Pssht\HandlerInterface
{
    protected $connection;

    public function __construct(\Clicky\Pssht\Connection $connection)
    {
        $this->connection = $connection;
    }
}
