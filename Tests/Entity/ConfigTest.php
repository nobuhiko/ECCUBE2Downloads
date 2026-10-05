<?php

declare(strict_types=1);

namespace Plugin\ECCUBE2Downloads44\Tests\Entity;

use Eccube\Tests\EccubeTestCase;
use Plugin\ECCUBE2Downloads44\Entity\Config;

final class ConfigTest extends EccubeTestCase
{
    public function testGetSetDownloadableDays(): void
    {
        $Config = new Config();
        $Config->setDownloadableDays(60);

        $this->assertSame(60, $Config->getDownloadableDays());
    }

    public function testGetSetDownloadableDaysUnlimited(): void
    {
        $Config = new Config();
        $Config->setDownloadableDaysUnlimited(true);

        $this->assertTrue($Config->isDownloadableDaysUnlimited());
    }

    public function testDefaultValues(): void
    {
        $Config = new Config();

        $this->assertSame(30, $Config->getDownloadableDays());
        $this->assertFalse($Config->isDownloadableDaysUnlimited());
    }
}
