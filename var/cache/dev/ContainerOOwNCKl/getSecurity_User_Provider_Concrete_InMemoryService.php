<?php

namespace ContainerOOwNCKl;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getSecurity_User_Provider_Concrete_InMemoryService extends App_KernelDevDebugContainer
{
    /**
     * Gets the private 'security.user.provider.concrete.in_memory' shared service.
     *
     * @return \Symfony\Component\Security\Core\User\InMemoryUserProvider
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/symfony/security-core/User/UserProviderInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/symfony/security-core/User/InMemoryUserProvider.php';

        return $container->privates['security.user.provider.concrete.in_memory'] = new \Symfony\Component\Security\Core\User\InMemoryUserProvider(['admin' => ['password' => 'mypassword,', 'roles' => [0 => 'ROLE_ADMIN']], 'simoneguglielmi' => ['password' => 'aUDfbM@}wnG?ZD\\5eN)>{f/"JFGeF01,', 'roles' => [0 => 'ROLE_ADMIN']]]);
    }
}
