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
     * @param string $bundleName
     *
     * @return Response
     */
    public function listAction(Request $request, $bundleName)
    {
        $manager = $this->getDoctrine()->getManager();
        $extensionRepository = $this->getDoctrine()->getRepository('EgzaktDatabaseConfigBundle:Extension');
        $configRepository = $this->getDoctrine()->getRepository('EgzaktDatabaseConfigBundle:Config');

        $tree = $this->getConfigNode($bundleName);
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
     * @param string $bundleName
     *
     * @return NodeInterface
     */
    private function getConfigNode($bundleName)
    {
        $bundles = $this->get('kernel')->getBundles();
        $bundle = $bundles[$bundleName];
        $extension = $bundle->getContainerExtension();
        $configuration = $extension->getConfiguration(array(), new ContainerBuilder());
        $tree = $configuration->getConfigTreeBuilder()->buildTree();

        return $tree;
    }
}
