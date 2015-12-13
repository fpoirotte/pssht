<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace fpoirotte\Pssht\MAC\OpensshCom\EtM\UMAC;

use fpoirotte\Pssht\MAC\OpensshCom\EtM\EtMInterface;

/**
 * The UMAC-128 algorithm, generating 128-bits tags,
 * in Encrypt-then-MAC (EtM) mode.
 */
class Len128 extends \fpoirotte\Pssht\MAC\OpensshCom\UMAC\Len128 implements EtMInterface
{
    public static function getName()
    {
        return 'umac-128-etm@openssh.com';
    }
}
