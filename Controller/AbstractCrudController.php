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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CrudController
 *
 * @package Cwd\CommonBundle\Controller
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
abstract class AbstractCrudController extends AbstractBaseController
{
    /**
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $object = $this->getNewEntity();

        return $this->formHandler($object, $request, true);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxDataAction(Request $request)
    {
        $grid = $this->getGrid($request->request->all());
        $data = $grid->getData();

        return new JsonResponse($data);
    }

    /**
     * @Method({"GET"})
     * @Template("MailingOwlAdminBundle:Grid:list.html.twig")
     *
     * @return array
     */
    public function listAction()
    {
        return array(
            'grid'        => $this->getGrid(),
            'icon'        => $this->getOption('icon'),
            'title'       => $this->getOption('title'),
            'gridRoute'   => $this->getOption('gridRoute'),
            'createRoute' => $this->getOption('createRoute'),
        );
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
     * @return array
     */
    public function indexAction()
    {
        return array();
    }


    /**
     * @param string $field
     * @param mixed  $crudObject
     * @param bool   $state
     *
     * @return JsonResponse
     */
    protected function toggleHandler($field, $crudObject, $state)
    {
        $field = sprintf('set%s', ucfirst($field));
        if (!method_exists($crudObject, $field)) {
            return new JsonResponse(array('error' => true, 'message' => sprintf('Field %s not found', $field)));
        }

        $state = ($state == 'true') ? true : false;

        $crudObject->$field($state);
        $this->getService()->flush();

        return new JsonResponse(array('error' => false, 'message' => sprintf('State saved', $field)));
    }
}
