<?php

namespace Flexy\DatabaseConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RequestMatcher
 *
 * @author akambi <contact@akambi-fagbohoun.com>
 */
class RequestMatcher
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $host;

    /**
     * @var array
     */
    private $ips;

    /**
     * @var array
     */
    private $methods;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $roles;

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
     * Set path
     *
     * @param string $path
     * @return RequestMatcher1
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set host
     *
     * @param string $host
     * @return RequestMatcher1
     */
    public function setHost($host)
    {
        $this->host = $host;
    
        return $this;
    }

    /**
     * Get host
     *
     * @return string 
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set ips
     *
     * @param array $ips
     * @return RequestMatcher1
     */
    public function setIps($ips)
    {
        $this->ips = $ips;
    
        return $this;
    }

    /**
     * Get ips
     *
     * @return array 
     */
    public function getIps()
    {
        return $this->ips;
    }

    /**
     * Set methods
     *
     * @param array $methods
     * @return RequestMatcher1
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    
        return $this;
    }

    /**
     * Get methods
     *
     * @return array 
     */
    public function getMethods()
    {
        return $this->methods;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add roles
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\Role $roles
     * @return RequestMatcher
     */
    public function addRole(\Flexy\DatabaseConfigBundle\Entity\Role $roles)
    {
        $this->roles[] = $roles;
    
        return $this;
    }

    /**
     * Remove roles
     *
     * @param \Flexy\DatabaseConfigBundle\Entity\Role $roles
     */
    public function removeRole(\Flexy\DatabaseConfigBundle\Entity\Role $roles)
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoles()
    {
        return $this->roles;
    }
    
    public static function getAvailableMethods() {
        return array(
            'GET'     => 'GET',
            'POST'    => 'POST',
            'PUT'     => 'PUT',
            'DELETE'  => 'DELETE',
            'HEAD'    => 'HEAD',
            'OPTIONS' => 'OPTIONS',
        );
    }
}