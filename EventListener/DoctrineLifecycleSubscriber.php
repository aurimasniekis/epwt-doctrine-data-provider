<?php

namespace EPWT\Cache\DoctrineDataProviderBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use EPWT\Cache\DataProviderBundle\Core\ProviderInterface;
use EPWT\Cache\DoctrineDataProviderBundle\Core\DoctrineProviderContainer;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class DoctrineLifecycleSubscriber
 * @package EPWT\Cache\DoctrineDataProviderBundle\EventListener
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class DoctrineLifecycleSubscriber extends ContainerAware implements EventSubscriber
{
    /**
     * @var array
     */
    protected $subscribers;

    /**
     * @var DoctrineProviderContainer
     */
    protected $doctrineProviderContainer;

    public function __construct()
    {
        $this->subscribers = [];
        $this->subscribers['preRemove'] = [];
        $this->subscribers['postRemove'] = [];
        $this->subscribers['prePersist'] = [];
        $this->subscribers['postPersist'] = [];
        $this->subscribers['preUpdate'] = [];
        $this->subscribers['postUpdate'] = [];
    }

    /**
     * @param string $eventType
     * @param string $entityClass
     * @param ProviderInterface $provider
     */
    public function addSubscriber($eventType, $entityClass, $provider)
    {
        if (!isset($this->subscribers[$eventType][$entityClass])) {
            $this->subscribers[$eventType][$entityClass] = [];
        }

        $this->subscribers[$eventType][$entityClass][] = $provider;
    }

    /**
     * @param string $eventType
     *
     * @return bool
     */
    public function hasSubscriberType($eventType)
    {
        return array_key_exists($eventType, $this->subscribers);
    }

    /**
     * @param string $eventType
     * @param string $entityClass
     *
     * @return bool
     */
    public function hasSubscriberTypeEntity($eventType, $entityClass)
    {
        return array_key_exists($eventType, $this->subscribers) &&
               array_key_exists($entityClass, $this->subscribers[$eventType]);
    }

    /**
     * @param string $eventType
     * @param null $entityClass
     *
     * @return array
     */
    public function getSubscribers($eventType, $entityClass = null)
    {
        if (!$this->hasSubscriberType($eventType)) {
            return [];
        }

        if ($entityClass && $this->hasSubscriberTypeEntity($eventType, $entityClass)) {
            return $this->subscribers[$eventType][$entityClass];
        }

        return $this->subscribers[$eventType];
    }


    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'preRemove',
            'postRemove',
            'prePersist',
            'postPersist',
            'preUpdate',
            'postUpdate'
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $this->baseEvent('preRemove', $args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->baseEvent('postRemove', $args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->baseEvent('prePersist', $args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->baseEvent('postPersist', $args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->baseEvent('preUpdate', $args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->baseEvent('postUpdate', $args);
    }

    /**
     * @param string $eventType
     * @param LifecycleEventArgs $args
     */
    protected function baseEvent($eventType, LifecycleEventArgs $args)
    {
        if ($this->container->has('debug.stopwatch')) {
            $stopwatch = $this->container->get('debug.stopwatch');
            $stopwatch->start($eventType, 'epwt_data_provider');
        }

        $this->callProviderEvent($eventType, get_class($args->getObject()), $args);

        if ($stopwatch) {
            $stopwatch->stop($eventType);
        }
    }

    /**
     * @param string $eventType
     * @param string $entityClass
     * @param LifecycleEventArgs $args
     */
    protected function callProviderEvent($eventType, $entityClass, $args)
    {
        if (!$this->hasSubscriberTypeEntity($eventType, $entityClass)) {
            return;
        }


        foreach ($this->getSubscribers($eventType, $entityClass) as $provider) {
            $eventName = 'event' . ucfirst($eventType);

            call_user_func([$provider, $eventName], $args);
        }
    }
}
