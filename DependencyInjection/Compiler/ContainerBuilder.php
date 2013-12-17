<?php

namespace Flexy\DatabaseConfigBundle\DependencyInjection\Compiler;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;

use Flexy\DatabaseConfigBundle\Entity\Config;
use Flexy\DatabaseConfigBundle\Entity\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder as BaseContainerBuilder;

/**
 * ContainerBuilder
 *
 * @author Hubert Perron <hubert.perron@gmail.com>
 * @author akambi <contact@akambi-fagbohoun.com>
 */
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
        try {
            $this->initConnection();
            $this->addDbParameters();
            $this->addSecurityParameters();
            $this->addDbConfig();
            $this->closeConnection();
        } catch (\PDOException $e) {
            parent::compile();
            return;
        }

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
     * Returns the query used to get the configs from the database
     *
     * @return QueryBuilder
     */
    protected function createSecurityRoleQuery()
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();

        $queryBuilder
            ->select('c.id, p.role AS name,c.role AS value')
            ->from('container_security_role', 'c')
            ->join('c', 'container_security_role', 'p', 'p.id = c.parent_id')
            ->OrderBy('c.parent_id')
            ->addOrderBy('c.id');

        return $queryBuilder;
    }

    /**
     * Returns the query used to get the configs from the database
     *
     * @return QueryBuilder
     */
    protected function createSecurityAccesControlQuery()
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();

        $queryBuilder
            ->select('r.id, r.path, r.host, r.ips, r.methods, c.role AS roles')
            ->from('container_security_requestmatcher', 'r')
            ->leftJoin('r', 'tj_requestmatcher_role', 'o', 'o.rqm_id = r.id')
            ->leftJoin('o', 'container_security_role', 'c', 'o.rol_id = c.id')
            ->OrderBy('r.id')
            ->addOrderBy('c.id');

        return $queryBuilder;
    }
    
    /**
     * Adds security configs from the database to the current configs
     */
    protected function addSecurityParameters()
    {
        if (false === $this->checkTableExist('container_security_role')) {
            return;
        }
        
        $query = $this->databaseConnection->query($this->createSecurityRoleQuery());
        $configs = array();
        
        while (false !== $result = $query->fetchObject()) {

            // New Config object
            $config = new Config();
            $config->setName($result->name);
            $config->setValue($result->value);

            // Store the new config in the configs array to keep it for further use if it has children
            $configs[$result->id] = $config;
        }
                
        $extension = new Extension();
        $extension->setName('security');
        $parentConfig = new Config();
        $parentConfig->setName('role_hierarchy');
        $parentConfig->setExtension($extension);
        $extension->addConfig($parentConfig);
        
        foreach ($configs as $config) {
            $parentConfig->addChildren($config);
            $config->setParent($parentConfig);
        }
                
        if (false === $this->checkTableExist('container_security_requestmatcher')) {
            return;
        }

        $query = $this->databaseConnection->query($this->createSecurityAccesControlQuery());
        
        $aResult = array();
        $aResultRole = array();
        
        while (false !== $result = $query->fetchObject()) {
            $aResultRole[$result->id][] = $result->roles;
            $aResult[$result->id] = $result;
        }

        $configs = array();        
        $aAccessControlParameters = array('path', 'host', 'ips', 'methods', 'roles');
        
        foreach ($aResult as $id => $result) {
            
            $result->ips = unserialize($result->ips);
            $result->methods = unserialize($result->methods);
            $result->roles = $aResultRole[$id];

            // New Config object
            $parentConfig = new Config();
            $parentConfig->setName(null);
            $parentConfig->setValue($result->id);
            
            foreach ($aAccessControlParameters as $accessControlParameter) {
                // New Config object
                $config = new Config();
                $config->setName($accessControlParameter);
                $config->setValue($result->$accessControlParameter);
                $parentConfig->addChildren($config);
                $config->setParent($parentConfig);
            }
            
            // Store the new parent config in the parentConfigs array for add it further to root config 'access_control'
            $configs[$result->id] = $parentConfig;
        }

        $parentConfig = new Config();
        $parentConfig->setName('access_control');
        $parentConfig->setExtension($extension);
        $extension->addConfig($parentConfig);
        
        foreach ($configs as $config) {
            $parentConfig->addChildren($config);
            $config->setParent($parentConfig);
        }
        
        $values = array();
        // Loop through configs without parent to get their config trees
        foreach ($extension->getConfigs() as $config) {
            $values[$config->getName()] = $config->getConfigTree();
        }
        
        // Adds the new security config loaded from the database to the config of the extension
        $this->loadFromExtension($extension->getName(), $values);        
    }
    
    /**
     * Adds general configs from the database to the current configs
     */
    protected function addDbConfig()
    {
        if (false === $this->checkTableExist('container_config')) {
            return;
        }

        $query = $this->createConfigQuery();
        $this->addConfigsToContainer($query);
    }

    /**
     * Adds configs from the database to the current configs
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     */
    public function addConfigsToContainer(QueryBuilder $query) {
            
        $query = $this->databaseConnection->query($query);
        
        $currentExtension = null;
        $extensions = array();
        $configs = array();
        $aParentToResolve = array();
        
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
                if (isset($configs[$result->parent_id])) {
                    $parentConfig = $configs[$result->parent_id];
                    $parentConfig->addChildren($config);
                    $config->setParent($parentConfig);
                } else {
                    $aParentToResolve[$result->id] = $result->parent_id;
                }
            } else {
                // The current config has no parent so we link it to the extension.
                // (We should always link the config to an extension even if it has a parent but it makes it easier to build the config tree that way)
                $config->setExtension($extensions[$currentExtension]);
                $extensions[$currentExtension]->addConfig($config);
            }

            // Store the new config in the configs array to keep it for further use if it has children
            $configs[$result->id] = $config;
        }
        
        foreach($aParentToResolve as $childId => $parentId) {
            $configs[$childId]->setParent($configs[$parentId]); 
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
