<?php

namespace Plugin\ECCUBE2Downloads\Repository;

use Doctrine\Persistence\ManagerRegistry as RegistryInterface;
use Eccube\Repository\AbstractRepository;
use Plugin\ECCUBE2Downloads\Entity\Config;

class ConfigRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Config::class);
    }

    /**
     * @return Config|null
     */
    public function get()
    {
        return $this->findOneBy([]);
    }
}
