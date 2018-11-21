<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $loader = require __DIR__ . '/vendor/autoload.php';

    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}
