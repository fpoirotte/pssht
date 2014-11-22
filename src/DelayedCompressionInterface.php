<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht;

/**
 * Interface for delayed compression.
 */
interface DelayedCompressionInterface extends CompressionInterface
{
    /// Sets a flag indicating that user authentication success.
    public function setAuthenticated();
}
