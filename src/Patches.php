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
 * Runtime "patches" for PHP.
 */
class Patches
{
    /**
     * Apply runtime patches to PHP.
     */
    public static function apply()
    {
        if (!defined('MCRYPT_MODE_CTR')) {
            define('MCRYPT_MODE_CTR', 'ctr');
        }
    }
}
