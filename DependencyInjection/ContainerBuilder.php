<?php

namespace Egzakt\DatabaseConfigBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
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
     */
    protected function closeConnection()
    {
        if ($this->databaseConnection->isConnected()) {
            $this->databaseConnection->close();
        }
    }

    /**
     * Check if a given table name exist in the database
     *
     * @param string $table
     *
     * @return bool
     */
    protected function checkTableExist($table)
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();
        $queryBuilder->select('*');
        $queryBuilder->from($table, 't');

        try {
            $this->databaseConnection->query($queryBuilder);
        } catch (DBALException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns the query used to get the configs from the database
     *
     * @return QueryBuilder
     */
    protected function createConfigQuery()
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();

        $queryBuilder
            ->select('e.id AS extension_id, e.name AS extension_name, c.parent_id, p.name AS parent_name, c.id, c.name, c.value')
            ->from('container_config', 'c')
            ->innerJoin('c', 'container_extension', 'e', 'e.id = c.extension_id')
            ->leftJoin('c', 'container_config', 'p', 'p.id = c.parent_id')
            ->orderBy('e.id')
            ->addOrderBy('c.parent_id')
            ->addOrderBy('c.id');

        return $queryBuilder;
    }

    /**
     * Adds configs from the database to the current configs
     */
    protected function addDbConfig()
    {
        if (false === $this->checkTableExist('container_config')) {
            return;
        }

        $query = $this->databaseConnection->query($this->createConfigQuery());

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
     * @return QueryBuilder
     */
    protected function createParametersQuery()
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();

        $queryBuilder
            ->select('p.name, p.value')
            ->from('container_parameter', 'p');

        return $queryBuilder;
    }

    /**
     * Adds the parameters from the database to the container's parameterBag
     */
    protected function addDbParameters()
    {
        if (false === $this->checkTableExist('container_parameter')) {
            return;
        }

        $query = $this->databaseConnection->query($this->createParametersQuery());

        while (false !== $result = $query->fetchObject()) {
            $this->setParameter($result->name, $result->value);
        }
    }

}
