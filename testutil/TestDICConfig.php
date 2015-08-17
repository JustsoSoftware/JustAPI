<?php

use justso\justapi\DependencyContainerInterface;

class MockClass
{
    public function __construct(MockClass2 $class2)
    {
    }
}

class MockClass2
{
}

return [
    'TestInterface' => function (DependencyContainerInterface $c) {
        return new MockClass($c->newInstanceOf('MockClass2'));
    }
];
