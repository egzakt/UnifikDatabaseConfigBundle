<?php
namespace Unifik\DatabaseConfigBundle\Service;

use Unifik\DatabaseConfigBundle\Entity\ExtensionRepository;
use Unifik\DatabaseConfigBundle\Entity\Config;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\Config\Definition\FloatNode;

/** ConfigurationService
 *
 * @package DatabaseConfigBundle
 * @author  Guillaume Petit <guillaume.petit@sword-group.com>
 *
 */
class ConfigurationService
{
    /**
     * @var ExtensionRepository the repository to the extension doctrine entity
     */
    private $extensionRepository;

    /**
     * @var AppKernel
     */
    private $kernel;

    /**
     * @var array
     */
    private $mergedConfiguration;

    /**
     * Constructor
     *
     * @param \AppKernel          $kernel              the application kernel
     * @param ExtensionRepository $extensionRepository the repository for the extension entity
     *
     * @return void
     */
    public function __construct(\AppKernel $kernel, ExtensionRepository $extensionRepository)
    {
        $this->kernel = $kernel;
        $this->extensionRepository = $extensionRepository;
    }

     /**
     * Get configuration value either from database or bundle config
     *
     * @param string $bundleName the bundle name that defines the default configuration
     * @param string $extension  the extension name
     * @param string $namespace  the namespace linked to the extension
     * @param string $key        the configuration key
     *
     * @throws \InvalidArgumentException when the configuration key is not found
     * @return mixed string|boolean the configuration value or false if it doesn't exists
     */
    public function getConfigurationValue($bundleName, $extension, $namespace, $key)
    {
        $value = '';
        $path = explode('.', $key);
        $node = $this->getDefaultConfigurationNode($bundleName, $path);

        if ($node === null) {
            throw new \InvalidArgumentException('Configuration key not found: ' . $key);
        }

        if (null !== $value = $this->getConfigurationFromDatabase($extension, $namespace, $path)) {
            if ($node instanceof BooleanNode) {
                $value = (boolean) $value;
            } elseif ($node instanceof IntegerNode) {
                $value = (integer) $value;
            } elseif ($node instanceof FloatNode) {
                $value = (float) $value;
            }
        } else {
            $value = $node->getDefaultValue();
        }
        return $value;
    }

    /**
     * Get configuration value from database
     *
     * @param string $extensionName the extension name
     * @param string $namespace     the namespace linked to the extension
     * @param string $path          the configuration path
     *
     * @return string|null
     */
    protected function getConfigurationFromDatabase($extensionName, $namespace, $path)
    {
        $extension = $this->extensionRepository->findOneBy(
            array(
                'name' => $extensionName,
                'namespace' => $namespace,
            )
        );
        if ($extension) {
            $value = $extension;
            foreach ($path as $pathElement) {
                $value = $value->get($pathElement);
                if ($value == null) {
                    break;
                }
            }
        }
        return $value;
    }

    /**
     * Get configuration value from default bundle configuration
     *
     * @param string $bundleName the bundle name that defines the configuration
     * @param string $path       the path of the configuration key
     *
     * @return string|null
     */
    protected function getDefaultConfigurationNode($bundleName, $path)
    {
        $tree = $this->getContainerConfigurationTree($this->kernel->getBundle($bundleName));

        foreach ($path as $pathElement) {
            foreach ($tree->getChildren() as $node) {
                if ($node->getName() == $pathElement) {
                    if ($node instanceof ArrayNode) {
                        $tree = $node;
                    } else {
                        return $node;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Return the configuration tree of a bundle or false if not defined
     *
     * @param BundleInterface $bundle a bundle
     *
     * @return mixed boolean|array
     */
    public function getContainerConfigurationTree(BundleInterface $bundle)
    {
        $extension = $bundle->getContainerExtension();

        if ($extension) {
            $configuration = $extension->getConfiguration(array(), new ContainerBuilder());
            if ($configuration) {
                return $configuration->getConfigTreeBuilder()->buildTree();
            }
        }

        return false;
    }

}
