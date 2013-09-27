<?php

namespace Egzakt\DatabaseConfigBundle\Form\DataTransformer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;

use Egzakt\DatabaseConfigBundle\Entity\Config;
use Egzakt\DatabaseConfigBundle\Entity\Extension;

class ArrayEntityTransformer implements DataTransformerInterface
{
    /**
     * @var Extension
     */
    private $extension;

    /**
     * @param Extension $extension
     *
     * @return array
     */
    public function transform($extension)
    {
        $this->extension = $extension;

        $tree = $this->transformChildren($extension->getRootConfigs());

        return $tree;
    }

    /**
     * Takes a Config entities collection and convert it to an array based tree structure.
     * Recursively called on each level.
     *
     * @param Collection $configs A collection Config entities
     *
     * @return array
     */
    public function transformChildren(Collection $configs)
    {
        $tree = array();

        foreach ($configs as $config) {
            $children = $config->getChildren();
            if ($children->count()) {
                $tree[$config->getName()] = $this->transformChildren($children);
            } else {
                $tree[$config->getName()] = $config->getValue();
            }
        }

        return $tree;
    }

    /**
     * @param mixed $children
     *
     * @return Extension|mixed
     */
    public function reverseTransform($children)
    {
        return $this->reverseTransformChildren($children);
    }

    /**
     * Takes an array based tree structure and convert it to a Config entities tree structure
     * Recursively called on each level.
     *
     * @param array $children
     * @param Config $parent
     *
     * @return Extension
     */
    public function reverseTransformChildren($children, Config $parent = null)
    {
        foreach ($children as $key => $value) {

            if (is_array($value)) {
                $value = array_filter($value);
            }

            if (empty($value)) {
                continue;
            }

            $config = new Config();
            $config->setName($key);
            $config->setParent($parent);

            if (is_array($value)) {
                $config->setValue(''); // array node are represented using an empty value
            } else {
                $config->setValue($value);
            }

            $this->extension->addConfig($config);

            if (is_array($value)) {
                $this->reverseTransformChildren($value, $config);
            }
        }

        return $this->extension;
    }
}