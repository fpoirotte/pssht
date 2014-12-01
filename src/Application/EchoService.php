<?php
/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\Application;

class EchoService
{
    public function __construct(
        \fpoirotte\Pssht\Transport $transport,
        \fpoirotte\Pssht\Connection $connection,
        \fpoirotte\Pssht\MessageInterface $message
    ) {
        
    }
}
