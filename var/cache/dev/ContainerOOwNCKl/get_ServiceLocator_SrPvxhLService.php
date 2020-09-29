<?php

namespace ContainerOOwNCKl;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class get_ServiceLocator_SrPvxhLService extends App_KernelDevDebugContainer
{
    /**
     * Gets the private '.service_locator.srPvxhL' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\ServiceLocator
     */
    public static function do($container, $lazyLoad = true)
    {
        return $container->privates['.service_locator.srPvxhL'] = new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($container->getService, [
            'viewhandler' => ['services', 'fos_rest.view_handler', 'getFosRest_ViewHandlerService', true],
        ], [
            'viewhandler' => '?',
        ]);
    }
}