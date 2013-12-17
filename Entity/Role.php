<?php

namespace Flexy\DatabaseConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 *
 * @author akambi <contact@akambi-fagbohoun.com>
 */
class Role
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
    private $role;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \Flexy\DatabaseConfigBundle\Entity\Config
     */
    private $parent;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $requestMatchers;   

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
     * @return Role1
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
     * Set role
     *
     * @param string $role
     * @return Role1
     */
    public function setRole($role)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requestMatchers = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add requestMatchers
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\RequestMatcher $requestMatchers
     * @return Role
     */
    public function addRequestMatcher(\Flexy\DatabaseConfigBundle\Entity\RequestMatcher $requestMatchers)
    {
        $this->requestMatchers[] = $requestMatchers;
    
        return $this;
    }

    /**
     * Remove requestMatchers
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\RequestMatcher $requestMatchers
     */
    public function removeRequestMatcher(\Flexy\DatabaseConfigBundle\Entity\RequestMatcher $requestMatchers)
    {
        $this->requestMatchers->removeElement($requestMatchers);
    }

    /**
     * Get requestMatchers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRequestMatchers()
    {
        return $this->requestMatchers;
    }

    /**
     * Add children
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\Role $children
     * @return Role
     */
    public function addChildren(\Flexy\DatabaseConfigBundle\Entity\Role $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\Role $children
     */
    public function removeChildren(\Flexy\DatabaseConfigBundle\Entity\Role $children)
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
     * @param \Flexy\DatabaseConfigBundle\Entity\Role $parent
     * @return Role
     */
    public function setParent(\Flexy\DatabaseConfigBundle\Entity\Role $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Flexy\DatabaseConfigBundle\Entity\Role 
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    public function __toString()
    {
        return $this->getName();
    }
}