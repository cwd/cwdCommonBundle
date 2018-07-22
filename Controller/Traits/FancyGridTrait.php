<?php


namespace Cwd\CommonBundle\Controller\Traits;


use Cwd\FancyGridBundle\Grid\GridBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait FancyGridTrait
{

    /**
     * @param Request $request
     *
     * @Route("/list/data")
     * @Method({"GET"})
     *
     * @return JsonResponse
     */
    public function ajaxDataAction(Request $request)
    {
        $options = [
            'filter' => urldecode($request->get('filter', '')),
            'page' => $request->get('page', 1),
            'sortField' => $request->get('sort'),
            'sortDir' => $request->get('dir'),
        ];

        $grid = $this->getGrid($options);
        $data = $grid->getData();

        return new JsonResponse($data);
    }

    /**
     * @Template("MailingOwlAdminBundle:Grid:list.html.twig")
     *
     * @return array
     */
    public function fancyGridlistAction()
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
            throw new \BadMethodCallException('FancyGrid Bundle not present');
            return null;
        }

        return $this->get('cwd_fancygrid.grid.factory')->create($this->getOption('gridService'), $options);
    }
}