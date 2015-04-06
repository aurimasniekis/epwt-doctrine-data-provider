<?php

namespace EPWT\Cache\DoctrineDataProviderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DoctrineLifecycleSubscribersPass
 * @package EPWT\Cache\DoctrineDataProviderBundle\DependencyInjection\Compiler
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class DoctrineLifecycleSubscribersPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('epwt_data_provider.doctrine.lifecycle.subscriber')) {
            return;
        }

        $doctrineLifecycleSubscriber = $container->getDefinition('epwt_data_provider.doctrine.lifecycle.subscriber');

        $taggedProviders = $container->findTaggedServiceIds('epwt_data_provider.doctrine');

        foreach ($taggedProviders as $id => $tags) {
            foreach ($tags as $tag) {
                $doctrineLifecycleSubscriber->addMethodCall(
                    'addSubscriber',
                    [
                        $tag['type'],
                        $tag['class'],
                        new Reference($id)
                    ]
                );
            }

        }

    }

}
