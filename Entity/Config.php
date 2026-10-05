<?php

namespace Plugin\ECCUBE2Downloads44\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Plugin\ECCUBE2Downloads44\Repository\ConfigRepository;

#[ORM\Table(name: 'plg_eccube2downloads44_config')]
#[ORM\Entity(repositoryClass: ConfigRepository::class)]
class Config extends AbstractEntity
{
    #[ORM\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'downloadable_days', type: Types::INTEGER, options: ['default' => 30])]
    private ?int $downloadable_days = 30;

    #[ORM\Column(name: 'downloadable_days_unlimited', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $downloadable_days_unlimited = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDownloadableDays(): ?int
    {
        return $this->downloadable_days;
    }

    public function setDownloadableDays(?int $downloadableDays): static
    {
        $this->downloadable_days = $downloadableDays;

        return $this;
    }

    public function isDownloadableDaysUnlimited(): bool
    {
        return $this->downloadable_days_unlimited;
    }

    public function setDownloadableDaysUnlimited(?bool $downloadableDaysUnlimited): static
    {
        $this->downloadable_days_unlimited = (bool) $downloadableDaysUnlimited;

        return $this;
    }
}
