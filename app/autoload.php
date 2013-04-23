<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../vendor/autoload.php';

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));


AnnotationRegistry::registerAutoloadNamespaces(array(
    'Gedmo\Mapping\Annotation'  => realpath(__DIR__.'/../') . '/vendor/gedmo/doctrine-extensions/lib',
    'Doctrine\\ODM\\MongoDB'    => realpath(__DIR__.'/../') . '/vendor/doctrine-mongodb-odm/lib',
    'Doctrine\\MongoDB'         => realpath(__DIR__.'/../') . '/vendor/doctrine-mongodb/lib',
    'Doctrine'                  => realpath(__DIR__.'/../') . '/vendor/doctrine/lib',
    'Buzz'						=> realpath(__DIR__.'/../') . '/vendor/buzz/lib',
    'Sensio'					=> realpath(__DIR__.'/../') . '/vendor/bundles'
     )
);

return $loader;
