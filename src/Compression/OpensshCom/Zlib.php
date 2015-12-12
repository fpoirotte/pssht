<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Compression\OpensshCom;

/**
 * Delayed ZLIB compression.
 */
class Zlib extends \fpoirotte\Pssht\Compression\Zlib implements
    \fpoirotte\Pssht\Compression\DelayedCompressionInterface
{
    /// Flag indicating whether user authentication succeeded or not.
    protected $authenticated;

    public function __construct($mode)
    {
        parent::__construct($mode);
        $this->authenticated = false;
    }

    public static function getName()
    {
        return 'zlib@openssh.com';
    }

    public function setAuthenticated()
    {
        $this->authenticated = true;
        return $this;
    }

    public function update($data)
    {
        if ($this->authenticated === false) {
            return $data;
        }
        return parent::update($data);
    }
}
