<?php

namespace ContainerM6xOpEO;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getFosRest_Serializer_JmsHandlerRegistryService extends App_KernelDevDebugContainer
{
    /**
     * Gets the public 'fos_rest.serializer.jms_handler_registry' shared service.
     *
     * @return \FOS\RestBundle\Serializer\JMSHandlerRegistryV2
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/jms/serializer/src/Handler/HandlerRegistryInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/friendsofsymfony/rest-bundle/Serializer/JMSHandlerRegistryV2.php';

        return $container->services['fos_rest.serializer.jms_handler_registry'] = new \FOS\RestBundle\Serializer\JMSHandlerRegistryV2(($container->privates['fos_rest.serializer.jms_handler_registry.inner'] ?? $container->load('getFosRest_Serializer_JmsHandlerRegistry_InnerService')));
    }
}
