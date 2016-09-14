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
     * @Route("/create")
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
     * @Route("/list")
     * @Route("/")
     * @Method({"GET"})
     * @Template("MailingOwlAdminBundle:Grid:list.html.twig")
     *
     *
     * @return array
     */
    public function listAction()
    {
        $this->getGrid()->get();

        return array(
            'icon'        => $this->getOption('icon'),
            'title'       => $this->getOption('title'),
            'gridRoute'   => $this->getOption('gridRoute'),
            'createRoute' => $this->getOption('createRoute'),
            'gridOptions' => $this->getOption('gridOptions'),

        );
    }

    /**
     * @return array
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Grid action
     * @Method({"GET"})
     *
     * @Route("/grid")
     * @return Response
     */
    public function gridAction()
    {
        $this->getGrid()->get();

        return $this->getGrid()->execute();
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
