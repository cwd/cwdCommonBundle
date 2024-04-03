<?php
/*
 * This file is part of CwdCommonBundle
 *
 * (c)2016 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\CommonBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Cwd\CommonBundle\Exception\BaseException;
use Cwd\CommonBundle\Options\ValidatedOptionsTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Persisters\PersisterException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base Manager class to ease creation of model-specific manager classes.
 * If this code proves useful, we should consider moving it into the Generic service.
 *
 * @package Cwd\CommonBundle\Manager
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
abstract class AbstractManager
{
    use ValidatedOptionsTrait;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger)
    {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
    }

    /**
     * @param int    $id
     * @param string $model
     *
     * @return object
     *
     * @deprecated Use findById instead!
     */
    public function findByModel($id, $model)
    {
        return $this->findById($model, $id);
    }

    /**
     * Find All by Model.
     *
     * @param string $model
     * @param array  $filter
     * @param array  $order
     * @param int    $amount
     * @param int    $offset
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findAllByModel($model, $filter = array(), $order = array(), $amount = 10, $offset = 0)
    {
        return $this->getEntityManager()->getRepository($model)->findBy($filter, $order, $amount, $offset);
    }

    /**
     * @param string $model
     * @param array  $where
     *
     * @return int
     */
    public function getCountByModel($model, $where = array())
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select($qb->expr()->count('x'))
            ->from($model, 'x');

        if (count($where) > 0) {
            $qb->andWhere($where);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $model
     * @param int    $id
     *
     * @return object
     */
    public function findById($model, $id)
    {
        return $this->getEntityManager()->getRepository($model)->find($id);
    }

    /**
     * Find Entities by fields in given Model.
     *
     * @param string   $model
     * @param array    $filter
     * @param array    $sort
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function findByFilter($model, $filter = array(), $sort = array(), $limit = null, $offset = null)
    {
        return $this->getEntityManager()->getRepository($model)->findby($filter, $sort, $limit, $offset);
    }

    /**
     * Find one Entities by fields in given Model.
     *
     * @param string $model
     * @param array  $filter
     *
     * @return object|null
     */
    public function findOneByFilter($model, $filter = array())
    {
        return $this->getEntityManager()->getRepository($model)->findOneby($filter);
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param mixed $object
     *
     * @throws PersistanceException
     *
     * @return bool
     */
    public function persist($object)
    {
        try {
            $this->getEntityManager()->persist($object);
        } catch (\Exception $e) {
            $this->getLogger()->warn('Object could not be saved', (array) $e);
            throw new PersisterException('Object could not be stored - '.$e->getMessage(), null, $e);
        }

        return true;
    }

    /**
     * Flush EntityManager.
     */
    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param misc $id
     *
     * @throws NotFoundException
     *
     * @return boolean
     */
    public function remove($id)
    {
        if (is_int($id)) {
            $object = $this->find($id);
        } else {
            $object = $id;
        }
        $this->getEntityManager()->remove($object);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     *
     * @return true
     */
    public function restore($id)
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');
        $object = $this->find($id);
        $object->setDeletedAt(null);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        $entityManager = $this->managerRegistry->getManagerForClass($this->getOption('modelName'));
        if (!$entityManager->isOpen()) {
            $this->managerRegistry->resetManager();
        }

        return $this->managerRegistry->getManagerForClass($this->getOption('modelName'));
    }

    /**
     * @return EntityManager
     * @deprecated
     */
    public function getEm()
    {
        trigger_error('getEm is deprecated - use getEntityManager() instead', E_USER_DEPRECATED);

        return $this->getEntityManager();
    }

    /**
     * Find Object by ID, throwing an optional exception if it is not found.
     *
     * @param int         $pid            Entity ID
     * @param string      $modelName      Model definition (class name or alias) to use
     * @param string|null $exceptionClass Optional Exception class to throw when nothing is found
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function findOneByIdForModel($pid, $modelName, $exceptionClass = null)
    {
        try {
            $obj = $this->findById($modelName, $pid);

            if ($obj === null) {
                $this->getLogger()->info('Row with ID {id} not found', array('id' => $pid));
                if (null !== $exceptionClass) {
                    throw new $exceptionClass('Row with ID '.$pid.' not found');
                }
            }

            return $obj;
        } catch (\Exception $e) {
            if (null !== $exceptionClass) {
                throw $this->createNotFoundException(sprintf('Entity with ID %s not found', $pid), 0, $e);
            } else {
                throw new \Exception(sprintf('Entity with ID %s not found', $pid), 0, $e);
            }
        }
    }

    /**
     * Return the Model to use inside this service - either by FQCN or by Doctrine alias.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->getOption('modelName');
    }

    /**
     * Return the NotFoundException class to use inside this service.
     *
     * @return string
     */
    public function getNotFoundExceptionClass()
    {
        return $this->getOption('notFoundExceptionClass');
    }

    /**
     * Create an exception instance using the class defined in $this->getNotFoundExceptionClass().
     *
     * @param string|null     $message
     * @param int|null        $code
     * @param \Exception|null $previous
     *
     * @return \Exception
     */
    public function createNotFoundException($message = null, $code = null, $previous = null)
    {
        $class = $this->getNotFoundExceptionClass();

        return new $class($message, $code, $previous);
    }

    /**
     * Shortcut to get entity repository for this service's model.
     *
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->getModelName());
    }

    /**
     * Find Object by ID
     *
     * @param int $pid
     *
     * @return object|null
     * @throws \Exception
     */
    public function find($pid)
    {
        return $this->findOneByIdForModel($pid, $this->getModelName(), $this->getNotFoundExceptionClass());
    }

    /**
     * @return object
     */
    public function getNew()
    {
        $class = $this->getEntityManager()->getClassMetadata($this->getModelName())->getName();

        return new $class();
    }

    /**
     * Set defaults required for the service definition.
     * @see BaseService::configureOptions()
     *
     * @return array
     */
    abstract protected function setServiceOptions();

    /**
     * Set default options, set required options - whatever is needed.
     * This will be called during first access to any of the object-related methods.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array());

        $resolver->setRequired(array(
            'modelName',
            'notFoundExceptionClass',
        ));
    }

    /**
     * Set raw option values right before validation. This can be used to chain
     * options in inheritance setups.
     *
     * @return array
     */
    protected function setOptions()
    {
        return $this->setServiceOptions();
    }
}
