<?php

namespace Plugin\ECCUBE2Downloads44\Repository;

use Doctrine\Persistence\ManagerRegistry as RegistryInterface;
use Eccube\Repository\AbstractRepository;
use Plugin\ECCUBE2Downloads44\Entity\Config;

/**
 * @extends AbstractRepository<Config>
 */
class ConfigRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Config::class);
    }

    public function get(): ?Config
    {
        return $this->findOneBy([]);
    }
}
