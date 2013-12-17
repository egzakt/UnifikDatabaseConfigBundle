<?php

namespace Flexy\DatabaseConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Flexy\DatabaseConfigBundle\Entity\RequestMatcher;
use Flexy\DatabaseConfigBundle\Form\RequestMatcherType;

/**
 * RequestMatcher controller.
 *
 * @author akambi <contact@akambi-fagbohoun.com>
 */
class RequestMatcherController extends Controller
{

    /**
     * Lists all RequestMatcher entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('FlexyDatabaseConfigBundle:RequestMatcher')->findAllWithRoles();

        return $this->render('FlexyDatabaseConfigBundle:RequestMatcher:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new RequestMatcher entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new RequestMatcher();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'requestmatcher.flash.created');
            
            return $this->redirect($this->generateUrl('flexy_requestmatcher'));
        }

        return $this->render('FlexyDatabaseConfigBundle:RequestMatcher:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a RequestMatcher entity.
    *
    * @param RequestMatcher $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(RequestMatcher $entity)
    {
        $form = $this->createForm(new RequestMatcherType(), $entity, array(
            'action' => $this->generateUrl('flexy_requestmatcher_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new RequestMatcher entity.
     *
     */
    public function newAction()
    {
        $entity = new RequestMatcher();
        $form   = $this->createCreateForm($entity);

        return $this->render('FlexyDatabaseConfigBundle:RequestMatcher:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a RequestMatcher entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FlexyDatabaseConfigBundle:RequestMatcher')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RequestMatcher entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FlexyDatabaseConfigBundle:RequestMatcher:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing RequestMatcher entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FlexyDatabaseConfigBundle:RequestMatcher')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RequestMatcher entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FlexyDatabaseConfigBundle:RequestMatcher:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a RequestMatcher entity.
    *
    * @param RequestMatcher $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(RequestMatcher $entity)
    {
        $form = $this->createForm(new RequestMatcherType(), $entity, array(
            'action' => $this->generateUrl('flexy_requestmatcher_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing RequestMatcher entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FlexyDatabaseConfigBundle:RequestMatcher')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find RequestMatcher entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'request.flash.updated');
            
            return $this->redirect($this->generateUrl('flexy_requestmatcher'));
        }

        return $this->render('FlexyDatabaseConfigBundle:RequestMatcher:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a RequestMatcher entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FlexyDatabaseConfigBundle:RequestMatcher')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find RequestMatcher entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('flexy_requestmatcher'));
    }

    /**
     * Creates a form to delete a RequestMatcher entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('flexy_requestmatcher_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
