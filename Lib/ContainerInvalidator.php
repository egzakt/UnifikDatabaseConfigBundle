<?php

namespace Egzakt\DatabaseConfigBundle\Lib;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Finder\Finder;

class ContainerInvalidator
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Invalidate the routing cache
     */
    public function invalidate()
    {
        $regex = '/app' . $this->getKernel()->getEnvironment() . '(.*)ProjectContainer(.*)/i';

        foreach ($this->getFiles($regex)->in($this->getKernel()->getCacheDir()) as $file) {
            unlink($file);
        }
    }

    /**
     * Return the kernel.
     * @return Kernel
     */
    protected function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Return the cache directory.
     * @return string
     */
    protected function getKernelCacheDir()
    {
        return $this->kernel->getCacheDir();
    }

    /**
     * @param $regex
     * @return Finder
     */
    protected function getFiles($regex)
    {
        return $this->getFinder()->files()->name($regex);
    }

    /**
     * @return Finder
     */
    protected function getFinder()
    {
        return new Finder();
    }

}
