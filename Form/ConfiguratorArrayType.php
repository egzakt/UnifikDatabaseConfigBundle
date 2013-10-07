<?php

namespace Flexy\DatabaseConfigBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class ConfiguratorArrayType extends ConfiguratorType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->processChildren($options['tree'], $builder);
    }
}
