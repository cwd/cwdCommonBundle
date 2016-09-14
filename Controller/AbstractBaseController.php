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
use Doctrine\ORM\EntityNotFoundException;
use Cwd\CommonBundle\Options\ValidatedOptionsInterface;
use Cwd\CommonBundle\Options\ValidatedOptionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
abstract class AbstractBaseController extends Controller implements ValidatedOptionsInterface
{
    use ValidatedOptionsTrait;

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
            'entityService',
            'entityFormType',
            'gridService',
            'icon',
        ));
    }

    /**
     * @param mixed   $crudObject
     * @param Request $request
     *
     * @Method({"GET", "DELETE"})
     * @return RedirectResponse|null
     */
    protected function deleteHandler($crudObject, Request $request)
    {
        $this->checkModelClass($crudObject);
        try {
            $this->getService()->remove($crudObject);
            $this->flashSuccess('Data successfully removed');
        } catch (EntityNotFoundException $e) {
            $this->flashError('Object with this ID not found ('.$request->get('id').')');
        } catch (\Exception $e) {
            $this->flashError('Unexpected Error: '.$e->getMessage());
        }

        $redirectRoute = $this->getOption('redirectRoute');
        if ($redirectRoute !== null) {
            return $this->redirect($this->generateUrl($redirectRoute, $this->getOption('redirectParameter')));
        }
    }

    /**
     * @param mixed   $crudObject
     * @param Request $request
     * @param bool    $persist
     * @param array   $formOptions
     *
     * @return RedirectResponse|Response
     */
    protected function formHandler($crudObject, Request $request, $persist = false, $formOptions = array())
    {
        $this->checkModelClass($crudObject);
        $form = $this->createForm($this->getOption('entityFormType'), $crudObject, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($persist) {
                    $this->getService()->persist($crudObject);
                }

                $this->getService()->flush();

                $this->flashSuccess($this->getOption('successMessage'));

                return $this->redirect(
                    $this->generateUrl($this->getOption('redirectRoute'), $this->getOption('redirectParameter'))
                );
            } catch (\Exception $e) {
                $this->flashError('Error while saving Data: '.$e->getMessage());
            }
        }

        return $this->render($this->getOption('formTemplate'), array(
            'form'  => $form->createView(),
            'title' => $this->getOption('title'),
            'icon'  => $this->getOption('icon'),
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
    protected function getService()
    {
        return $this->get($this->getOption('entityService'));
    }

    /**
     * Get new entity provided by the service.
     *
     * @return mixed
     */
    protected function getNewEntity()
    {
        return $this->getService()->getNew();
    }

    /**
     * @param array $options
     *
     * @return GridBuilderInterface
     *
     * @throws \BadMethodCallException
     */
    protected function getGrid(array $options = [])
    {
        if (!interface_exists(GridBuilderInterface::class)) {
            throw new \BadMethodCallException('Bootgrid Bundle not present');
            return null;
        }

        return $this->get('cwd_bootgrid.grid.factory')->create($this->getOption('gridService'), $options);
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = $this->get('logger');
        }

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
