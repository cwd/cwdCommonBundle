<?php
/*
 * This file is part of CwdCommonBundle
 *
 * (c)2016 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\CommonBundle\Controller;

use Cwd\BootgridBundle\Grid\GridBuilderInterface;
use Cwd\CommonBundle\Controller\Traits\HandlerTrait;
use Doctrine\ORM\EntityNotFoundException;
use Cwd\CommonBundle\Options\ValidatedOptionsInterface;
use Cwd\CommonBundle\Options\ValidatedOptionsTrait;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractBaseController
 *
 * @package Cwd\CommonBundle\Controller
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
abstract class AbstractBaseController extends AbstractController implements ValidatedOptionsInterface
{
    use ValidatedOptionsTrait;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @var LoggerInterface
     */
    protected $logger = null;


    /**
     * Set default options, set required options - whatever is needed.
     * This will be called during first access to any of the object-related methods.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'checkModelClass'   => null,
            'redirectRoute'     => null,
            'gridRoute'         => null,
            'createRoute'       => null,
            'redirectParameter' => array(),
            'successMessage'    => 'Data successfully saved',
            'formTemplate'      => null,
            'title'             => 'Admin',
        ));

        $resolver->setRequired(array(
            'entityManager',
            'entityFormType',
            'gridService',
            'icon',
        ));
    }

    /**
     * Check if the given CRUD object matches the optionally configured "checkModelClass" option.
     *
     * @throws \InvalidArgumentException
     *
     * @param string $crudObject
     */
    protected function checkModelClass($crudObject)
    {
        $modelClass = $this->getOptionOrDefault('checkModelClass');
        if (null !== $modelClass && !$crudObject instanceof $modelClass) {
            throw new \InvalidArgumentException(
                'Expected CRUD model class '.$modelClass.' but got '.get_class($crudObject)
            );
        }
    }

    /**
     * @return BaseService
     */
    protected function getManager()
    {
        return $this->get($this->getOption('entityManager'));
    }

    /**
     * Get new entity provided by the service.
     *
     * @return mixed
     */
    protected function getNewEntity()
    {
        return $this->getManager()->getNew();
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * Session Flashmessenger.
     *
     * @param string      $type
     * @param string|null $message
     */
    protected function flash($type = 'info', $message = null)
    {
        $this->get('session')->getFlashBag()->add(
            $type,
            $message
        );
    }

    /**
     * @param string $message
     */
    protected function flashInfo($message)
    {
        $this->flash('info', $message);
    }

    /**
     * @param string $message
     */
    protected function flashSuccess($message)
    {
        $this->flash('success', $message);
    }

    /**
     * @param string $message
     */
    protected function flashError($message)
    {
        $this->flash('error', $message);
    }
}
