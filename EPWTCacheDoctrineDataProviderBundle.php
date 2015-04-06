<?php

namespace EPWT\Cache\DoctrineDataProviderBundle;

use EPWT\Cache\DoctrineDataProviderBundle\DependencyInjection\Compiler\DoctrineLifecycleSubscribersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EPWTCacheDoctrineDataProviderBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineLifecycleSubscribersPass());
    }
}
