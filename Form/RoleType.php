<?php

namespace Flexy\DatabaseConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Description of RoleType
 *
 * @author akambi <contact@akambi-fagbohoun.com>
 */
class RoleType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $role = $builder->getData();
        
        $builder
            ->add('name')
            ->add('role', 'text', array(
                'label' => 'role.field.role',
                'translation_domain' => 'FlexyDatabaseConfigBundle'
            ))
            ->add('parent', 'entity', array(
                'label' => 'role.field.parent.label',
                'empty_value' => 'role.field.parent.empty_label',
                'class' => 'FlexyDatabaseConfigBundle:Role',
                'query_builder' => function(EntityRepository $er) use ($role) {
                    $qb = $er->createQueryBuilder('r');
                    if ($role->getId()) {
                        $qb->where('r != :role')
                        ->setParameter('role', $role);
                    }
                    return $qb->orderBy('r.name', 'ASC');
                },
                'translation_domain' => 'FlexyDatabaseConfigBundle'
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Flexy\DatabaseConfigBundle\Entity\Role'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'flexy_databaseconfigbundle_role';
    }
}
