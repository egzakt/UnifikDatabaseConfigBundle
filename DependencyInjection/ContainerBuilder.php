<?php

namespace Egzakt\DatabaseConfigBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;

use Egzakt\DatabaseConfigBundle\Entity\Config;
use Egzakt\DatabaseConfigBundle\Entity\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder as BaseContainerBuilder;

class ContainerBuilder extends BaseContainerBuilder
{
    /**
     * @var Connection
     */
    protected $databaseConnection;

    /**
     * Constructor
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);

        if (class_exists('ProxyManager\Configuration')) {
            $this->setProxyInstantiator(new RuntimeInstantiator());
        }
    }

    /**
     * Compiles the container.
     *
     * This method adds parameters and configs from the database before calling compiler passes
     *
     */
    public function compile()
    {
        $this->initConnection();
        $this->addDbParameters();
        $this->addDbConfig();
        $this->closeConnection();

        parent::compile();
    }

    /**
     * Initializes the database connection
     */
    protected function initConnection()
    {
        $configs = $this->getExtensionConfig('doctrine');

        $mergedConfig = array();
        foreach ($configs as $config) {
            $mergedConfig = array_merge($mergedConfig, $config);
        }

        $mergedConfig = $this->getParameterBag()->resolveValue($mergedConfig);

        $params = $mergedConfig['dbal'];

        if (array_key_exists('connections', $params)) {
            $defaultEntityManager = $mergedConfig['orm']['default_entity_manager'];
            $defaultConnection = $mergedConfig['entity_managers'][$defaultEntityManager]['connection'];
            $params = $params['connections'][$defaultConnection];
        }

        $connection_factory = new ConnectionFactory(array());
        $this->databaseConnection = $connection_factory->createConnection($params);
        $this->databaseConnection->connect();
    }

    /**
     * Closes the database connection
     *
     */
    protected function closeConnection()
    {
        if ($this->databaseConnection->isConnected()) {
            $this->databaseConnection->close();
        }
    }

    /**
     * Returns the query used to get configs from the database
     *
     * @return string
     */
    protected function getConfigQuery()
    {
        return 'SELECT
                    e.id as extension_id,
                    e.name as extension_name,
                    c.parent_id,
                    p.name as parent_name,
                    c.id,
                    c.name,
                    c.value
                FROM db_config_config c
                INNER JOIN db_config_extension e ON e.id = c.extension_id
                LEFT JOIN db_config_config p ON p.id = c.parent_id
                ORDER BY e.id, c.parent_id, c.id
            ';
    }

    /**
     * Adds configs from the database to the current configs
     *
     */
    protected function addDbConfig()
    {
        $query = $this->databaseConnection->query($this->getConfigQuery());

        $currentExtension = null;
        $extensions = array();
        $configs = array();

        while (false !== $result = $query->fetchObject()) {

            if ($currentExtension != $result->extension_id) {
                // The current extension has changed. We have to create a new Extension
                $currentExtension = $result->extension_id;
                $extension = new Extension();
                $extension->setName($result->extension_name);
                $extensions[$currentExtension] = $extension;
            }

            // New Config object
            $config = new Config();
            $config->setName($result->name);
            $config->setValue($result->value);


            if (null !== $result->parent_id) {
                // The current config has a parent. We set the parent and the child
                $parentConfig = $configs[$result->parent_id];
                $parentConfig->addChildren($config);
                $config->setParent($parentConfig);
            } else {
                // The current config has no parent so we link it to the extension.
                // (We should always link the config to an extension even if it has a parent but it makes it easier to build the config tree that way)
                $config->setExtension($extensions[$currentExtension]);
                $extensions[$currentExtension]->addConfig($config);
            }

            // Store the new config in the configs array to keep it for further use if it has children
            $configs[$result->id] = $config;
        }

        foreach ($extensions as $extension) {
            $values = array();

            // Loop through configs without parent to get their config trees
            foreach ($extension->getConfigs() as $config) {
                $values[$config->getName()] = $config->getConfigTree();
            }

            // Adds the new config loaded from the database to the config of the extension
            $this->loadFromExtension($extension->getName(), $values);
        }
    }

    /**
     * Returns the query used to get parameters from the database
     *
     * @return string
     */
    protected function getParametersQuery()
    {
        return 'SELECT name, value
                FROM db_config_parameter';
    }

    /**
     * Adds the parameters from the database to the container's parameterBag
     */
    protected function addDbParameters()
    {
        $query = $this->databaseConnection->query($this->getParametersQuery());

        while (false !== $result = $query->fetchObject()) {
            $this->setParameter($result->name, $result->value);
        }
    }

}
