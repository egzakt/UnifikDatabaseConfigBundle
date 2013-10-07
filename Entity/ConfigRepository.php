<?php

namespace Flexy\DatabaseConfigBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class ConfigRepository extends EntityRepository
{
    public function deleteByExtension($extensionId)
    {
        $builder = $this->createQueryBuilder('e')
            ->delete()
            ->where('e.extension = :extensionId')
            ->setParameter('extensionId', $extensionId);

        $builder->getQuery()->execute();
    }
}