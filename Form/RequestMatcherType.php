<?php

namespace Flexy\DatabaseConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Flexy\DatabaseConfigBundle\Entity\RequestMatcher;
use Doctrine\ORM\EntityRepository;

/**
 * Description of RequestMatcherType
 *
 * @author akambi <contact@akambi-fagbohoun.com>
 */
class RequestMatcherType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('path')
            ->add('host', 'text', array(
                'required' => false
            ))
            ->add('ips', 'collection', array(
                'type' => 'text',
                'label' => 'requestmatcher.field.ips.label',
                'translation_domain' => 'FlexyDatabaseConfigBundle',
                'allow_add'    => true,
                'allow_delete' => true,
                'required' => false,
                'options' => array(
                    'required'  => false,
                    'attr' => array('class' => 'text-box'),
                    'label' => false
                )
            ))
            ->add('methods', 'choice', array(
                'choices'  => RequestMatcher::getAvailableMethods(),
                'label' => 'requestmatcher.field.methods',
                'translation_domain' => 'FlexyDatabaseConfigBundle',
                'multiple' => true,
                'expanded' => true,
                'required' => false
            ))
            ->add('roles', 'entity', array(
                    'class' => 'FlexyDatabaseConfigBundle:Role',
                    'property' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'multiple' => true,
                    'expanded' => true,
            
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Flexy\DatabaseConfigBundle\Entity\RequestMatcher'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'flexy_databaseconfigbundle_requestmatcher';
    }
}
