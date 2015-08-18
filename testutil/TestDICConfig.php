<?php

use justso\justapi\DependencyContainerInterface;

return [
    'TestInterface' => function (\justso\justapi\SystemEnvironmentInterface $env) {
        /** @var MockClass2 $object */
        $object = $env->newInstanceOf('MockClass2');
        return new MockClass($object);
    }
];
