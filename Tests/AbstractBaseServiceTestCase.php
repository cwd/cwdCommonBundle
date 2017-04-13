<?php
/*
 * This file is part of Zitate
 *
 * (c)2014 Ludwig Ruderstaller <lr@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\CommonBundle\Tests;

use Doctrine\ORM\QueryBuilder;
use Cwd\CommonBundle\Tests\Repository\DoctrineTestCase;
use FOS\UserBundle\Model\UserInterface;

/**
 * Class Cwd\Common\AbstractBaseService
 *
 * @package MailingOwl\Tests\Service
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
abstract class AbstractBaseServiceTestCase extends DoctrineTestCase
{
    /**
     * @var string|int
     */
    protected $primaryId;

    /**
     * @return string|int
     */
    protected function getFirstId()
    {
        /** @var QueryBuilder $x */
        $x = $this->service->getRepository()->createQueryBuilder('x');
        $x->setMaxResults(1);

        $object = $x->getQuery()->getSingleResult();

        return $object->getId();
    }

    public function testEntityManager()
    {
        $this->assertInstanceOf('Doctrine\ORM\EntityManager', $this->service->getEntityManager());
    }

    public function testGetNewEntity()
    {
        $this->assertNull($this->service->getNew()->getId());
    }

    public function testFind()
    {
        $object = $this->service->find($this->getFirstId());
        $this->assertEquals($this->primaryId, $object->getId());

        return $object;
    }

    /**
     * @
     */
    public function testFindNotFound() {
        $this->expectException($this->service->getOption('notFoundExceptionClass'));
        $this->service->find('foo');
    }

    public function testDelete()
    {
        $object = $this->service->find($this->primaryId);
        $this->assertEquals($this->primaryId, $object->getId());

        $this->service->remove($object);
        $this->service->getEntityManager()->clear();

        $this->expectException($this->service->getOption('notFoundExceptionClass'));
        $object = $this->service->find($this->primaryId);
    }


    public function testRepository()
    {
        $this->assertInstanceOf('Doctrine\ORM\EntityRepository', $this->service->getRepository());
    }

    /**
     * @param int $pid
     *
     * @return UserInterface
     */
    abstract protected function getUser($pid = 1);

}
