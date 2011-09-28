<?php

require_once __DIR__.'/../../../symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
require_once __DIR__.'/../../../symfony/src/Symfony/Component/ClassLoader/DebugUniversalClassLoader.php';

use Symfony\Component\ClassLoader\DebugUniversalClassLoader as UniversalClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Doctrine\\Common'                 => __DIR__.'/../../../doctrine-common/lib',
    'Knp\\Component'                   => array(__DIR__.'/../../../knp-materialized-path/src', __DIR__ )
));
$loader->register();

