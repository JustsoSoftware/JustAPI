<?php

use justso\justapi\testutil\MockClass;
use justso\justapi\testutil\MockClass2;

return [
    'TestInterface' => function (\justso\justapi\SystemEnvironmentInterface $env) {
        /** @var MockClass2 $object */
        $object = $env->newInstanceOf('justso\justapi\testutil\MockClass2');
        return new MockClass($object);
    }
];
