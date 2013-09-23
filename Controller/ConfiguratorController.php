<?php

namespace Egzakt\DatabaseConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Egzakt\DatabaseConfigBundle\Form\ConfiguratorType;

/**
 * Locale Controller
 */
class ConfiguratorController extends Controller
{
    /**
     * List all config of a given extension
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $extensionRepository = $this->getDoctrine()->getRepository('EgzaktDatabaseConfigBundle:Extension');
        $configRepository = $this->getDoctrine()->getRepository('EgzaktDatabaseConfigBundle:Config');

        $tree = $this->getConfigTreeForBundle('EgzaktLdapBundle');
        $extension = $extensionRepository->findOneByName($tree->getName());

        $form = $this->createForm(new ConfiguratorType(), $extension, array('tree' => $tree, 'request' => $request));

        if ('POST' == $request->getMethod()) {

            $form->bind($request);

            if ($form->isValid()) {

                // removing the previous config entries from the database
                $configRepository->deleteByExtension($extension->getId());

                $manager->persist($extension);
                $manager->flush($extension);

                // TODO: clear the container cache
            }
        }

        return $this->render('EgzaktDatabaseConfigBundle::list.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @param $bundleName
     *
     * @return NodeInterface
     */
    private function getConfigTreeForBundle($bundleName)
    {
        $bundles = $this->get('kernel')->getBundles();
        $composerManagerBundle = $bundles[$bundleName];
        $composerManagerExtension = $composerManagerBundle->getContainerExtension();
        $composerManagerConfiguration = $composerManagerExtension->getConfiguration(array(), new ContainerBuilder());
        $tree = $composerManagerConfiguration->getConfigTreeBuilder()->buildTree();

        return $tree;
    }
}
