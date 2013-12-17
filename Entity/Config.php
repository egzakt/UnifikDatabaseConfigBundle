<?php

namespace Flexy\DatabaseConfigBundle\Entity;

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
     * @var \Flexy\DatabaseConfigBundle\Entity\Config
     */
    private $parent;

    /**
     * @var \Flexy\DatabaseConfigBundle\Entity\Extension
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
     * @param \Flexy\DatabaseConfigBundle\Entity\Config $children
     * @return Config
     */
    public function addChildren(\Flexy\DatabaseConfigBundle\Entity\Config $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\Config $children
     */
    public function removeChildren(\Flexy\DatabaseConfigBundle\Entity\Config $children)
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
     * @param \Flexy\DatabaseConfigBundle\Entity\Config $parent
     * @return Config
     */
    public function setParent(\Flexy\DatabaseConfigBundle\Entity\Config $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Flexy\DatabaseConfigBundle\Entity\Config
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set extension
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\Extension $extension
     * @return Config
     */
    public function setExtension(\Flexy\DatabaseConfigBundle\Entity\Extension $extension = null)
    {
        $this->extension = $extension;
    
        return $this;
    }

    /**
     * Get extension
     *
     * @return \Flexy\DatabaseConfigBundle\Entity\Extension
     */
    public function getExtension()
    {
        return $this->extension;
    }

    public function getConfigTree() {
        if (count($this->children) > 0) {
            $configArray = array();
            foreach ($this->children as $child) {
                if (isset($configArray[$child->getName()])) {
                    if (is_string($configArray[$child->getName()])) {
                        $configArray[$child->getName()] = array($configArray[$child->getName()]);
                    }
                    $configArray[$child->getName()][] = $child->getConfigTree();
                } else {
                    if ($child->getName() == null) {
                        $configArray[] = $child->getConfigTree();
                    } else {
                        $configArray[$child->getName()] = $child->getConfigTree();
                    }
                }
            }

            return $configArray;
        }

        if (is_numeric($this->value)) {
            $this->value = intval($this->value);
        }

        return $this->value;
    }
}