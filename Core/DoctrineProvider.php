<?php

namespace EPWT\Cache\DoctrineDataProviderBundle\Core;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use EPWT\Cache\DataProviderBundle\Core\BaseProvider;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * Class DoctrineProvider
 * @package EPWT\Cache\DoctrineDataProviderBundle\Core
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
abstract class DoctrineProvider extends BaseProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @return ManagerRegistry
     */
    public function getManagerRegistry()
    {
        if ($this->managerRegistry) {
            return $this->managerRegistry;
        }

        $this->managerRegistry = $this->getContainer()->get('doctrine');

        return $this->managerRegistry;
    }

    /**
     * @param string $name
     *
     * @return EntityManager
     */
    public function getEntityManager($name = null)
    {
        return $this->getManagerRegistry()->getManager($name);
    }

    /**
     * Fetches data from cache or database
     *
     * @param int $id Object id
     *
     * @return bool|mixed
     */
    public function find($id)
    {
        $cacheResult = $this->getCacheValue([$id]);

        if ($cacheResult) {
            return $cacheResult;
        }

        return $this->loadFromDb($id);
    }

    /**
     * Loads data from database and caches it
     *
     * @param int $id Object id
     * @param int $ttl Cache TTL
     * @param bool $buildKey Uses buildKey function to build key for cache
     *
     * @return null|object
     */
    public function loadFromDb($id, $ttl = null, $buildKey = true)
    {
        $result = $this->getRepository()->find($id);

        if ($result) {
            $this->setCacheValue([$result->getId()], $result, $buildKey, $ttl);
        }

        return $result;
    }

    /**
     * Warm ups the cache by caching every row in table
     *
     * @return bool
     */
    public function warmUp()
    {
        $results = $this->getRepository()->findAll();

        foreach ($results as $result) {
            $this->setCacheValue([$result->getId()], $result);
        }

        return true;
    }

    /**
     * @return EntityRepository
     */
    abstract public function getRepository();



    /**
     * @param LifecycleEventArgs $args
     */
    public function eventPreRemove(LifecycleEventArgs $args)
    {
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function eventPostRemove(LifecycleEventArgs $args)
    {
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function eventPrePersist(LifecycleEventArgs $args)
    {
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function eventPostPersist(LifecycleEventArgs $args)
    {
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function eventPreUpdate(LifecycleEventArgs $args)
    {
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function eventPostUpdate(LifecycleEventArgs $args)
    {
    }
}
