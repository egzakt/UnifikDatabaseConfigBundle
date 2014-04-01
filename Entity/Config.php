<?php

namespace Unifik\DatabaseConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @package Unifik.DatabaseConfigBundle.Entity
 *
 * @author  Guillaume Petit <guillaume.petit@sword-group.com>
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
     * @var \Unifik\DatabaseConfigBundle\Entity\Config
     */
    private $parent;

    /**
     * @var \Unifik\DatabaseConfigBundle\Entity\Extension
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
     * @param string $name the config item name
     *
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
     * @param string $value the config item value
     *
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
     * @param \Unifik\DatabaseConfigBundle\Entity\Config $children the child to add
     *
     * @return Config
     */
    public function addChildren(\Unifik\DatabaseConfigBundle\Entity\Config $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Unifik\DatabaseConfigBundle\Entity\Config $children the child to remove
     *
     * @return void
     */
    public function removeChildren(\Unifik\DatabaseConfigBundle\Entity\Config $children)
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
     * @param \Unifik\DatabaseConfigBundle\Entity\Config $parent the parent to set
     *
     * @return Config
     */
    public function setParent(\Unifik\DatabaseConfigBundle\Entity\Config $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Unifik\DatabaseConfigBundle\Entity\Config
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set extension
     *
     * @param \Unifik\DatabaseConfigBundle\Entity\Extension $extension the extension to set
     *
     * @return Config
     */
    public function setExtension(\Unifik\DatabaseConfigBundle\Entity\Extension $extension = null)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return \Unifik\DatabaseConfigBundle\Entity\Extension
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Return the configuration tree (associative array)
     *
     * @return multitype:array |string
     */
    public function getConfigTree()
    {
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

    /**
     * Get config child by name
     *
     * @param string $configName the config name
     *
     * @return Config|NULL
     */
    public function get($configName)
    {
        foreach ($this->getChildren() as $config) {
            if ($config->getName() == $configName) {
                if ($config->getValue() != '') {
                    return $config->getValue();
                } else {
                    return $config;
                }
            }
        }
        return null;
    }

}
