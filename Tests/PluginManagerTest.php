<?php

namespace Plugin\ECCUBE2Downloads\Tests;

use Eccube\Entity\Delivery;
use Eccube\Entity\DeliveryFee;
use Eccube\Entity\Master\SaleType;
use Eccube\Entity\PaymentOption;
use Eccube\Tests\EccubeTestCase;
use Plugin\ECCUBE2Downloads\Entity\Config;
use Plugin\ECCUBE2Downloads\PluginManager;

class PluginManagerTest extends EccubeTestCase
{
    public function testSaleTypeExists()
    {
        $SaleType = $this->entityManager->find(SaleType::class, PluginManager::SALE_TYPE_ID);
        self::assertNotNull($SaleType);
        self::assertEquals('ダウンロード', $SaleType->getName());
    }

    public function testDeliveryExists()
    {
        $SaleType = $this->entityManager->find(SaleType::class, PluginManager::SALE_TYPE_ID);
        $Delivery = $this->entityManager->getRepository(Delivery::class)->findOneBy(['SaleType' => $SaleType]);

        self::assertNotNull($Delivery);
        self::assertTrue($Delivery->isVisible());
        self::assertEquals('ダウンロード商品送料', $Delivery->getName());
    }

    public function testDeliveryFeeAllZero()
    {
        $SaleType = $this->entityManager->find(SaleType::class, PluginManager::SALE_TYPE_ID);
        $Delivery = $this->entityManager->getRepository(Delivery::class)->findOneBy(['SaleType' => $SaleType]);

        $fees = $this->entityManager->getRepository(DeliveryFee::class)->findBy(['Delivery' => $Delivery]);
        self::assertGreaterThanOrEqual(47, count($fees));

        foreach ($fees as $fee) {
            self::assertEquals(0, $fee->getFee());
        }
    }

    public function testPaymentOptionsExist()
    {
        $SaleType = $this->entityManager->find(SaleType::class, PluginManager::SALE_TYPE_ID);
        $Delivery = $this->entityManager->getRepository(Delivery::class)->findOneBy(['SaleType' => $SaleType]);

        $options = $this->entityManager->getRepository(PaymentOption::class)->findBy(['Delivery' => $Delivery]);
        self::assertGreaterThan(0, count($options));
    }

    public function testConfigExists()
    {
        $Config = $this->entityManager->getRepository(Config::class)->findOneBy([]);
        self::assertNotNull($Config);
        self::assertEquals(30, $Config->getDownloadableDays());
        self::assertFalse($Config->isDownloadableDaysUnlimited());
    }
}
