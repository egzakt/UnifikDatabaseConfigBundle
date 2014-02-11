<?php

namespace Flexy\DatabaseConfigBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Flexy\DatabaseConfigBundle\Entity\Role;

class FixturesRole extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $em)       
    {
        //  On crée les rôles par défaut de symfony 2
        $role = new Role();
        $role->setName('Anonyme');
        $role->setRole('IS_AUTHENTICATED_ANONYMOUSLY');
        //  On la persiste
        $em->persist($role);

        $role = new Role();
        $role->setName('Authentifié par cookie remembered');
        $role->setRole('IS_AUTHENTICATED_REMEMBERED');
        //  On la persiste
        $em->persist($role);

        $role = new Role();
        $role->setName('Authetifié complètement');
        $role->setRole('IS_AUTHENTICATED_FULLY');
        //  On la persiste
        $em->persist($role);

        $roleUser = new Role();
        $roleUser->setName('Utilisateur');
        $roleUser->setRole('ROLE_USER');
        //  On la persiste
        $em->persist($roleUser);

        $roleAdmin = new Role();
        $roleAdmin->setName('Administrateur');
        $roleAdmin->setRole('ROLE_ADMIN');
        $roleAdmin->setParent($roleUser);
        //  On la persiste
        $em->persist($roleAdmin);

        $roleSuperAdmin = new Role();
        $roleSuperAdmin->setName('Super Administrateur');
        $roleSuperAdmin->setRole('ROLE_SUPER_ADMIN');
        $roleSuperAdmin->setParent($roleAdmin);
        //  On la persiste
        $em->persist($roleSuperAdmin);
                
        //  On déclenche l'enregistrement
        $em->flush();
    }
    
     /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
