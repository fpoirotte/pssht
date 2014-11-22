<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\CHANNEL\REQUEST;

/**
 * SSH_MSG_CHANNEL_REQUEST message (RFC 4254)
 * for the "exec" request type.
 */
class Exec extends \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\Base
{
    /// Command to execute.
    protected $command;


    /**
     * Construct a new SSH_MSG_CHANNEL_REQUEST message
     * for the "exec" type.
     *
     *  \copydetails Base::__construct
     *
     *  \param string $command
     *      Command to execute.
     */
    public function __construct($channel, $type, $wantReply, $command)
    {
        parent::__construct($channel, $type, $wantReply);
        $this->command = $command;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeString($this->command);
        return $this;
    }

    protected static function unserializeSub(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return array($decoder->decodeString());
    }

    /**
     * Get the command to execute.
     *
     *  \retval string
     *      Command to execute.
     */
    public function getCommand()
    {
        return $this->command;
    }
}
