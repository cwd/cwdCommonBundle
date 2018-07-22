<?php


namespace Cwd\CommonBundle\Controller\Traits;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait LegacyTrait
 * @package Cwd\CommonBundle\Controller\Traits
 * @deprecated
 */
trait LegacyTrait
{
    use HandlerTrait;

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
     * @return array
     */
    public function indexAction(Request $request)
    {
        return array();
    }

}