<?php

namespace fpoirotte\Pssht\Tests\Helpers;

class OutputException extends \PHPUnit_Framework_SyntheticError
{
    public function toString()
    {
        return $this->getMessage();
    }
}

