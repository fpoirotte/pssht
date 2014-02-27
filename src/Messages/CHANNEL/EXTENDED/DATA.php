<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\Messages\CHANNEL\EXTENDED;

/**
 * SSH_MSG_CHANNEL_EXTENDED_DATA message (RFC 4254).
 */
class DATA extends \Clicky\Pssht\Messages\CHANNEL\Base
{
    /// Assume the extended data stream is \c stderr.
    const SSH_EXTENDED_DATA_STDERR = 1;

    /// Code designating the extended data stream.
    protected $code;

    /// Payload.
    protected $data;


    /**
     * Construct a new SSH_MSG_CHANNEL_EXTENDED_DATA message.
     *
     *  \copydetails Base::__construct
     *
     *  \param int $code
     *      Code designating the extended data stream
     *      the payload is taken from.
     *
     *  \param string $data
     *      Message's payload.
     */
    public function __construct($channel, $code, $data)
    {
        parent::__construct($channel);
        $this->code     = $code;
        $this->data     = $data;
    }

    public static function getMessageId()
    {
        return 95;
    }

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeUint32($this->code);
        $encoder->encodeString($this->data);
        return $this;
    }

    public static function unserialize(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeUint32(),   // channel
            $decoder->decodeUint32(),
            $decoder->decodeString()
        );
    }

    /**
     * Get the extended stream's identifier.
     *
     *  \retval int
     *      Extended stream's code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the payload associated with this message.
     *
     *  \retval string
     *      Message's payload.
     */
    public function getData()
    {
        return $this->data;
    }
}
