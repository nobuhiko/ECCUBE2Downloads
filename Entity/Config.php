<?php

namespace Plugin\ECCUBE2Downloads\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * @ORM\Table(name="plg_eccube2downloads_config")
 *
 * @ORM\Entity(repositoryClass="Plugin\ECCUBE2Downloads\Repository\ConfigRepository")
 */
class Config extends AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="downloadable_days", type="integer", options={"default":30})
     */
    private $downloadable_days = 30;

    /**
     * @var bool
     *
     * @ORM\Column(name="downloadable_days_unlimited", type="boolean", options={"default":false})
     */
    private $downloadable_days_unlimited = false;

    public function getId()
    {
        return $this->id;
    }

    public function getDownloadableDays()
    {
        return $this->downloadable_days;
    }

    public function setDownloadableDays($downloadableDays)
    {
        $this->downloadable_days = $downloadableDays;

        return $this;
    }

    public function isDownloadableDaysUnlimited()
    {
        return $this->downloadable_days_unlimited;
    }

    public function setDownloadableDaysUnlimited($downloadableDaysUnlimited)
    {
        $this->downloadable_days_unlimited = $downloadableDaysUnlimited;

        return $this;
    }
}
