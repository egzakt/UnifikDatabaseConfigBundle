<?php

namespace Egzakt\DatabaseConfigBundle\Form\DataTransformer;

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

        $tree = array();

        if ($extension) {
            foreach ($extension->getConfigs() as $config) {
                if (false == $config->getParent()) {
                    $tree[$config->getName()] = $config->getValue();
                }
            }
        }

        return $tree;
    }

    /**
     * @param array $values
     *
     * @return Extension
     */
    public function reverseTransform($values)
    {
        foreach ($values as $key => $value) {

            if (strpos($key, '_activated')) {
                continue;
            }

            if (is_array($value)) {
                continue;
            }

            if (is_null($value)) {
                continue;
            }

            $config = new Config();
            $config->setName($key);
            $config->setValue($value);

            $this->extension->addConfig($config);
        }

        return $this->extension;
    }
}