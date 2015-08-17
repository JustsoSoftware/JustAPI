<?php

use justso\justapi\DependencyContainerInterface;

return [
    'TestInterface' => function (DependencyContainerInterface $c) {
        return new MockClass($c->newInstanceOf('MockClass2'));
    }
];
