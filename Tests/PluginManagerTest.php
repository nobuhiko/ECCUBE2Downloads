<?php

declare(strict_types=1);

namespace Plugin\ECCUBE2Downloads44\Tests;

use Eccube\Entity\Delivery;
use Eccube\Entity\DeliveryFee;
use Eccube\Entity\Master\SaleType;
use Eccube\Entity\PaymentOption;
use Eccube\Tests\EccubeTestCase;
use Plugin\ECCUBE2Downloads44\Entity\Config;
use Plugin\ECCUBE2Downloads44\PluginManager;

final class PluginManagerTest extends EccubeTestCase
{
    public function testSaleTypeExists(): void
    {
        $SaleType = $this->entityManager->find(SaleType::class, PluginManager::SALE_TYPE_ID);
        $this->assertInstanceOf(SaleType::class, $SaleType);
        $this->assertEquals('ダウンロード', $SaleType->getName());
    }

    public function testDeliveryExists(): void
    {
        $SaleType = $this->entityManager->find(SaleType::class, PluginManager::SALE_TYPE_ID);
        $Delivery = $this->entityManager->getRepository(Delivery::class)->findOneBy(['SaleType' => $SaleType]);

        $this->assertInstanceOf(Delivery::class, $Delivery);
        $this->assertTrue($Delivery->isVisible());
        $this->assertEquals('ダウンロード商品送料', $Delivery->getName());
    }

    public function testDeliveryFeeAllZero(): void
    {
        $SaleType = $this->entityManager->find(SaleType::class, PluginManager::SALE_TYPE_ID);
        $Delivery = $this->entityManager->getRepository(Delivery::class)->findOneBy(['SaleType' => $SaleType]);

        $fees = $this->entityManager->getRepository(DeliveryFee::class)->findBy(['Delivery' => $Delivery]);
        $this->assertGreaterThanOrEqual(47, count($fees));

        foreach ($fees as $fee) {
            $this->assertEquals(0, $fee->getFee());
        }
    }

    public function testPaymentOptionsExist(): void
    {
        $SaleType = $this->entityManager->find(SaleType::class, PluginManager::SALE_TYPE_ID);
        $Delivery = $this->entityManager->getRepository(Delivery::class)->findOneBy(['SaleType' => $SaleType]);

        $options = $this->entityManager->getRepository(PaymentOption::class)->findBy(['Delivery' => $Delivery]);
        $this->assertGreaterThan(0, count($options));
    }

    public function testConfigExists(): void
    {
        $Config = $this->entityManager->getRepository(Config::class)->findOneBy([]);
        $this->assertInstanceOf(Config::class, $Config);
        $this->assertEquals(30, $Config->getDownloadableDays());
        $this->assertFalse($Config->isDownloadableDaysUnlimited());
    }
}
