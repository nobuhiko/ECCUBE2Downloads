<?php

namespace Plugin\ECCUBE2Downloads\Tests\Entity;

use Eccube\Tests\EccubeTestCase;
use Plugin\ECCUBE2Downloads\Entity\Config;

class ConfigTest extends EccubeTestCase
{
    public function testGetSetDownloadableDays()
    {
        $Config = new Config();
        $Config->setDownloadableDays(60);

        self::assertEquals(60, $Config->getDownloadableDays());
    }

    public function testGetSetDownloadableDaysUnlimited()
    {
        $Config = new Config();
        $Config->setDownloadableDaysUnlimited(true);

        self::assertTrue($Config->isDownloadableDaysUnlimited());
    }

    public function testDefaultValues()
    {
        $Config = new Config();

        self::assertEquals(30, $Config->getDownloadableDays());
        self::assertFalse($Config->isDownloadableDaysUnlimited());
    }
}
