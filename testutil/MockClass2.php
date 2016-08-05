<?php

namespace justso\justapi\testutil;

class MockClass2
{
    public $myParams;

    public function __construct($param1 = null, $param2 = null)
    {
        $this->myParams = [$param1, $param2];
    }
}
