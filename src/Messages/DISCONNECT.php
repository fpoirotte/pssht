<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages;

use fpoirotte\Pssht\MessageInterface;

/**
 * SSH_MSG_DISCONNECT message (RFC 4253).
 */
class DISCONNECT extends \Exception implements MessageInterface
{
    /// Disconnected because the remote host is not allowed to connect.
    const SSH_DISCONNECT_HOST_NOT_ALLOWED_TO_CONNECT        =    1;

    /// Disconnected due to a protocol error.
    const SSH_DISCONNECT_PROTOCOL_ERROR                     =    2;

    /// Disconnected due to key exchange failure.
    const SSH_DISCONNECT_KEY_EXCHANGE_FAILED                =    3;

    /// Disconnected due to a reserved error.
    const SSH_DISCONNECT_RESERVED                           =    4;

    /// Disconnected due to a Message Authentication Code error.
    const SSH_DISCONNECT_MAC_ERROR                          =    5;

    /// Disconnected due to a compression error.
    const SSH_DISCONNECT_COMPRESSION_ERROR                  =    6;

    /// Disconnected because the requested service is not available.
    const SSH_DISCONNECT_SERVICE_NOT_AVAILABLE              =    7;

    /// Disconnected due to an unsupported protocol version being requested.
    const SSH_DISCONNECT_PROTOCOL_VERSION_NOT_SUPPORTED     =    8;

    /// Disconnected due to an unverifiable host key.
    const SSH_DISCONNECT_HOST_KEY_NOT_VERIFIABLE            =    9;

    /// Disconnected due to connection loss.
    const SSH_DISCONNECT_CONNECTION_LOST                    =   10;

    /// Disconnected by the application layer.
    const SSH_DISCONNECT_BY_APPLICATION                     =   11;

    /// Disconnected because too many connections are currently opened.
    const SSH_DISCONNECT_TOO_MANY_CONNECTIONS               =   12;

    /// Disconnected due to authentication cancelation.
    const SSH_DISCONNECT_AUTH_CANCELLED_BY_USER             =   13;

    /// Disconnected because no more authentication methods are available.
    const SSH_DISCONNECT_NO_MORE_AUTH_METHODS_AVAILABLE     =   14;

    /// Disconnected due to an illegal user name.
    const SSH_DISCONNECT_ILLEGAL_USER_NAME                  =   15;


    /// Disconnection reason.
    protected $code;

    /// Disconnection message (human-readable description in UTF-8 encoding).
    protected $message;

    /// Language the disconnection message is written into (from RFC 3066).
    protected $language;


    /**
     * Construct a new disconnection message.
     *
     *  \param int $reasonCode
     *      Disconnection code.
     *
     *  \param string $reasonMessage
     *      Disconnection message, as a human-readable description
     *      in ISO-10646 UTF-8 encoding.
     *
     *  \param string $language
     *      Language tag the disconnection message is written into,
     *      in RFC 3066 format.
     */
    public function __construct(
        $reasonCode = 0,
        $reasonMessage = '',
        $language = ''
    ) {
        if (!is_int($reasonCode)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($reasonMessage)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($language)) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($reasonMessage, $reasonCode);
        $this->language    = $language;
    }

    public static function getMessageId()
    {
        return 1;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeUint32($this->code);
        $encoder->encodeString($this->message);
        $encoder->encodeString($this->language);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeUint32(),
            $decoder->decodeString(),
            $decoder->decodeString()
        );
    }
}
