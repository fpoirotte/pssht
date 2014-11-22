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
 * for the "exit-status" request type.
 */
class ExitStatus extends \fpoirotte\Pssht\Messages\CHANNEL\REQUEST\Base
{
    /// Exit status of the process.
    protected $status;


    /**
     * Construct a new SSH_MSG_CHANNEL_REQUEST message
     * for the "exit-signal" type.
     *
     *  \copydetails Base::__construct
     *
     *  \param int $status
     *      Exit status of the process.
     */
    public function __construct($channel, $type, $wantReply, $status)
    {
        parent::__construct($channel, $type, $wantReply);
        $this->status = $status;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeUint32($this->status);
        return $this;
    }

    protected static function unserializeSub(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return array($decoder->decodeUint32());
    }

    /**
     * Get the exit status of the process.
     *
     *  \retval int
     *      Exit status of the process.
     */
    public function getStatus()
    {
        return $this->status;
    }
}
