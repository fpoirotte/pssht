<?php

/*
* This file is part of pssht.
*
* (c) François Poirotte <clicky@erebot.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Clicky\Pssht\KEX;

use Clicky\Pssht\KEXInterface;

class       DHGroup1SHA1
implements  KEXInterface
{
    static public function getName()
    {
        return 'diffie-hellman-group1-sha1';
    }

    static public function getGenerator()
    {
        return 2;
    }

    static public function getPrime()
    {
        return str_replace("\r\n ", '', '
            FFFFFFFF FFFFFFFF C90FDAA2 2168C234 C4C6628B 80DC1CD1
            29024E08 8A67CC74 020BBEA6 3B139B22 514A0879 8E3404DD
            EF9519B3 CD3A431B 302B0A6D F25F1437 4FE1356D 6D51C245
            E485B576 625E7EC6 F44C42E9 A637ED6B 0BFF5CB6 F406B7ED
            EE386BFB 5A899FA5 AE9F2411 7C4B1FE6 49286651 ECE65381
            FFFFFFFF FFFFFFFF'
        );
    }

    public function hash($data)
    {
        return sha1($data, TRUE);
    }
}

