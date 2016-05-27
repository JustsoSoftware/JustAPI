<?php

use justso\justapi\testutil\MockClass;
use justso\justapi\testutil\MockClass2;

// @codeCoverageIgnoreStart
return [
    'TestClassName' => 'justso\justapi\testutil\MockClass2',

    'TestFactory' => function (\justso\justapi\SystemEnvironmentInterface $env) {
        /** @var MockClass2 $object */
        $object = $env->newInstanceOf('justso\justapi\testutil\MockClass2');
        return new MockClass($object);
    },

    'TestObject' => new justso\justapi\testutil\MockClass2(),
];
// @codeCoverageIgnoreEnd
