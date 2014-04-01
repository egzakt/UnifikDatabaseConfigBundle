<?php

namespace Unifik\DatabaseConfigBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/** ConfigRepository
 *
 * @package Unifik.DatabaseConfigBundle.Entity
 * @author  Guillaume Petit <guillaume.petit@sword-group.com>
 *
 */
class ConfigRepository extends EntityRepository
{
    /**
     * Delete configuration by extension
     *
     * @param integer $extensionId the extension id
     *
     * @return void
     */
    public function deleteByExtension($extensionId)
    {
        $builder = $this->createQueryBuilder('e')
            ->delete()
            ->where('e.extension = :extensionId')
            ->setParameter('extensionId', $extensionId);

        $builder->getQuery()->execute();
    }
}