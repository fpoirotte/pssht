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

/**
 * SSH_MSG_USERAUTH_REQUEST message (RFC 4252)
 * for the "password" method.
 */
class Password extends \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base
{
    protected $password;
    protected $newPassword;

    public function __construct($user, $service, $method, $password, $newPassword = null)
    {
        if (!is_string($password)) {
            throw new \InvalidArgumentException();
        }

        if (!is_string($newPassword) && $newPassword !== null) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($user, $service, $method);
        $this->password     = $password;
        $this->newPassword  = $newPassword;
    }

    public function serialize(Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeBoolean($this->newPassword !== null);
        $encoder->encodeString($this->password);
        if ($this->newPassword !== null) {
            $encoder->encodeString($this->newPassword);
        }
        return $this;
    }

    protected static function unserializeSub(Decoder $decoder)
    {
        $passChange = $decoder->decodeBoolean();
        $res        = array($decoder->decodeString());
        if ($passChange === true) {
            $res[] = $decoder->decodeString();
        }
        return $res;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getNewPassword()
    {
        return $this->newPassword;
    }
}
