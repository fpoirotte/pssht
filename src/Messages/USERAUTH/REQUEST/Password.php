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

/**
 * SSH_MSG_USERAUTH_REQUEST message (RFC 4252)
 * for the "password" method.
 */
class Password extends \Clicky\Pssht\Messages\USERAUTH\REQUEST\Base
{
    /// Password for the given login.
    protected $password;

    /// New password.
    protected $newPassword;


    /**
     *  \copydetails Base::__construct
     *
     *  \param string $password
     *      Password for the given user.
     *
     *  \param string $newPassword
     *      (optional) Set the new password for the user
     *      to this value after authentication.
     *
     *  \note
     *      Pssht does not support password changing.
     *      Therefore, the last argument to this method
     *      is ignored when given.
     */
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

    public function serialize(\Clicky\Pssht\Wire\Encoder $encoder)
    {
        parent::serialize($encoder);
        $encoder->encodeBoolean($this->newPassword !== null);
        $encoder->encodeString($this->password);
        if ($this->newPassword !== null) {
            $encoder->encodeString($this->newPassword);
        }
        return $this;
    }

    protected static function unserializeSub(\Clicky\Pssht\Wire\Decoder $decoder)
    {
        $passChange = $decoder->decodeBoolean();
        $res        = array($decoder->decodeString());
        if ($passChange === true) {
            $res[] = $decoder->decodeString();
        }
        return $res;
    }

    /**
     * Get the password given for authentication purposes.
     *
     *  \retval string
     *      Password associated with the given user.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the user's password to this new password
     * after authentication success.
     *
     *  \retval string
     *      New password for the user.
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }
}
