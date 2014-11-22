<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Handlers\CHANNEL;

/**
 * Abstract handler for SSH_MSG_CHANNEL_* messages.
 */
abstract class Base implements \fpoirotte\Pssht\HandlerInterface
{
    /// SSH connection layer.
    protected $connection;

    /**
     * Construct the handler.
     *
     *  \param fpoirotte::Pssht::Connection $connection
     *      SSH connection layer.
     */
    public function __construct(\fpoirotte\Pssht\Connection $connection)
    {
        $this->connection = $connection;
    }
}
