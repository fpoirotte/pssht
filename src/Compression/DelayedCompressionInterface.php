<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Compression;

/**
 * Interface for delayed compression.
 *
 * When used, compression is delayed until
 * a successful user authentication occurs.
 */
interface DelayedCompressionInterface extends CompressionInterface
{
    /// Sets a flag indicating user authentication success.
    public function setAuthenticated();
}
