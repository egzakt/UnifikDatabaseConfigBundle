<?php

namespace Egzakt\DatabaseConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 */
class Config
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
     * @var string
     */
    private $value;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \Egzakt\DatabaseConfigBundle\Entity\Config
     */
    private $parent;

    /**
     * @var \Egzakt\DatabaseConfigBundle\Entity\Extension
     */
    private $extension;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Config
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
     * Set value
     *
     * @param string $value
     * @return Config
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Add children
     *
     * @param \Egzakt\DatabaseConfigBundle\Entity\Config $children
     * @return Config
     */
    public function addChildren(\Egzakt\DatabaseConfigBundle\Entity\Config $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \Egzakt\DatabaseConfigBundle\Entity\Config $children
     */
    public function removeChildren(\Egzakt\DatabaseConfigBundle\Entity\Config $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Egzakt\DatabaseConfigBundle\Entity\Config $parent
     * @return Config
     */
    public function setParent(\Egzakt\DatabaseConfigBundle\Entity\Config $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Egzakt\DatabaseConfigBundle\Entity\Config 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set extension
     *
     * @param \Egzakt\DatabaseConfigBundle\Entity\Extension $extension
     * @return Config
     */
    public function setExtension(\Egzakt\DatabaseConfigBundle\Entity\Extension $extension = null)
    {
        $this->extension = $extension;
    
        return $this;
    }

    /**
     * Get extension
     *
     * @return \Egzakt\DatabaseConfigBundle\Entity\Extension 
     */
    public function getExtension()
    {
        return $this->extension;
    }

    public function getConfigTree() {
        if (count($this->children) > 0) {
            $configArray = array();
            foreach ($this->children as $child) {
                $configArray[$child->getName()] = $child->getConfigTree();
            }

            return $configArray;
        }

        if (is_numeric($this->value)) {
            $this->value = intval($this->value);
        }

        return $this->value;
    }
}