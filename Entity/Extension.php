<?php

namespace Egzakt\DatabaseConfigBundle\Entity;

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
     * @param \Egzakt\DatabaseConfigBundle\Entity\Config $configs
     * @return Extension
     */
    public function addConfig(\Egzakt\DatabaseConfigBundle\Entity\Config $configs)
    {
        $this->configs[] = $configs;
    
        return $this;
    }

    /**
     * Remove configs
     *
     * @param \Egzakt\DatabaseConfigBundle\Entity\Config $configs
     */
    public function removeConfig(\Egzakt\DatabaseConfigBundle\Entity\Config $configs)
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
}