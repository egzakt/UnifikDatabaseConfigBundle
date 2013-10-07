<?php

namespace Flexy\DatabaseConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Extension
 */
class Extension
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $configs;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Extension
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add configs
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\Config $config
     * @return Extension
     */
    public function addConfig(\Flexy\DatabaseConfigBundle\Entity\Config $config)
    {
        $config->setExtension($this);

        $this->configs[] = $config;
    
        return $this;
    }

    /**
     * Remove configs
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\Config $configs
     */
    public function removeConfig(\Flexy\DatabaseConfigBundle\Entity\Config $configs)
    {
        $this->configs->removeElement($configs);
    }

    /**
     * Get configs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Get root configs
     *
     * @return ArrayCollection
     */
    public function getRootConfigs()
    {
        $configs = new ArrayCollection();

        foreach ($this->configs as $config) {
            if (false == $config->getParent()) {
                $configs[] = $config;
            }
        }

        return $configs;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $configs
     */
    public function setConfigs($configs)
    {
        $this->configs = $configs;
    }
}