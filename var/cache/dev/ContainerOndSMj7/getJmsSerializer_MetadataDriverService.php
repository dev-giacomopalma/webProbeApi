<?php

namespace ContainerOndSMj7;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getJmsSerializer_MetadataDriverService extends App_KernelDevDebugContainer
{
    /**
     * Gets the public 'jms_serializer.metadata_driver' shared service.
     *
     * @return \JMS\Serializer\Metadata\Driver\DoctrineTypeDriver
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/jms/metadata/src/Driver/DriverInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Metadata/Driver/AbstractDoctrineTypeDriver.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Metadata/Driver/DoctrineTypeDriver.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/metadata/src/Driver/AdvancedDriverInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/metadata/src/Driver/DriverChain.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/metadata/src/Driver/AbstractFileDriver.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Metadata/Driver/ExpressionMetadataTrait.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Metadata/Driver/YamlDriver.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/metadata/src/Driver/FileLocatorInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/metadata/src/Driver/AdvancedFileLocatorInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/metadata/src/Driver/FileLocator.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Naming/PropertyNamingStrategyInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Naming/SerializedNameAnnotationStrategy.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Naming/CamelCaseNamingStrategy.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Metadata/Driver/XmlDriver.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Metadata/Driver/AnnotationDriver.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Type/ParserInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Type/Parser.php';

        $a = new \Metadata\Driver\FileLocator([]);
        $b = new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(new \JMS\Serializer\Naming\CamelCaseNamingStrategy('_', true));
        $c = ($container->privates['jms_serializer.type_parser'] ?? ($container->privates['jms_serializer.type_parser'] = new \JMS\Serializer\Type\Parser()));

        return $container->services['jms_serializer.metadata_driver'] = new \JMS\Serializer\Metadata\Driver\DoctrineTypeDriver(new \Metadata\Driver\DriverChain([0 => new \JMS\Serializer\Metadata\Driver\YamlDriver($a, $b, $c), 1 => new \JMS\Serializer\Metadata\Driver\XmlDriver($a, $b, $c), 2 => new \JMS\Serializer\Metadata\Driver\AnnotationDriver(($container->privates['annotations.cached_reader'] ?? $container->getAnnotations_CachedReaderService()), $b, $c)]), ($container->services['doctrine'] ?? $container->getDoctrineService()), $c);
    }
}
