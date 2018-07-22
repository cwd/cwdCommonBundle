<?php


namespace Cwd\CommonBundle\Controller\Traits;


use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait HandlerTrait
{
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

        if (is_string($state)) {
            $state = ($state == 'true') ? true : false;
        }

        $crudObject->$field($state);
        $this->getManager()->flush();

        return new JsonResponse(array('error' => false, 'message' => sprintf('State saved', $field)));
    }


    /**
     * @param mixed   $crudObject
     * @param Request $request
     *
     * @return RedirectResponse|null
     */
    protected function deleteHandler($crudObject, Request $request)
    {
        $this->checkModelClass($crudObject);
        try {
            $this->getManager()->remove($crudObject);
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
                    $this->getManager()->persist($crudObject);
                }

                $this->getManager()->flush();

                $this->flashSuccess($this->getOption('successMessage'));

                return $this->redirect(
                    $this->generateUrl($this->getOption('redirectRoute'), $this->getOption('redirectParameter'))
                );
            } catch (\Exception $e) {
                $this->flashError('Error while saving Data: '.$e->getMessage());
                $this->getLogger()->addError($e->getMessage());
            }
        }

        return $this->render($this->getOption('formTemplate'), array(
            'form'  => $form->createView(),
            'title' => $this->getOption('title'),
            'icon'  => $this->getOption('icon'),
            'redirectRoute' => $this->getOption('redirectRoute'),
            'redirectParameter' => $this->getOption('redirectParameter'),
            'create' => $persist,
        ));
    }

    abstract public function getManager();
    abstract public function getLogger();
    abstract public function getOption($name);
    abstract public function checkModelClass($crudObject);
}