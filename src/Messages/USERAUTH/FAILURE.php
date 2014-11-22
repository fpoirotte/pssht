<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Messages\USERAUTH;

/**
 * SSH_MSG_USERAUTH_FAILURE message (RFC 4252).
 */
class FAILURE implements \fpoirotte\Pssht\MessageInterface
{
    /// List of authentication methods that may continue.
    protected $methods;

    /// Whether a partial success occurred.
    protected $partial;

    /**
     * Construct a new SSH_MSG_USERAUTH_FAILURE message.
     *
     *  \param array $methods
     *      List of authentication methods that may continue.
     *
     *  \param bool $partial
     *      Indicates whether partial success was attained
     *      (\b true) or not (\b false).
     */
    public function __construct(array $methods, $partial)
    {
        if (!is_bool($partial)) {
            throw new \InvalidArgumentException();
        }

        foreach ($methods as $method) {
            if (!is_string($method)) {
                throw new \InvalidArgumentException();
            }
        }

        $this->methods = $methods;
        $this->partial = $partial;
    }

    public static function getMessageId()
    {
        return 51;
    }

    public function serialize(\fpoirotte\Pssht\Wire\Encoder $encoder)
    {
        $encoder->encodeNameList($this->methods);
        $encoder->encodeBoolean($this->partial);
        return $this;
    }

    public static function unserialize(\fpoirotte\Pssht\Wire\Decoder $decoder)
    {
        return new static(
            $decoder->decodeNameList(),
            $decoder->decodeBoolean()
        );
    }
}
